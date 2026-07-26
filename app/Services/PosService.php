<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class PosService
{
    protected LedgerService $ledgerService;
    protected DarajaService $darajaService;

    public function __construct(LedgerService $ledgerService, DarajaService $darajaService)
    {
        $this->ledgerService = $ledgerService;
        $this->darajaService = $darajaService;
    }

    /**
     * Process POS Order Checkout with 3 Payment Paths
     */
    public function processCheckout(array $orderData, array $cartItems, ?string $customerPhone = null): array
    {
        $paymentMethod = strtolower($orderData['payment_method'] ?? 'cash');
        $customerId = $orderData['customer_id'];
        $customer = Customer::findOrFail($customerId);

        $totalAmount = floatval($orderData['total']);
        $vatRate = 0.16;
        $taxMode = $orderData['tax_mode'] ?? 'inclusive';
        $vatAmount = $taxMode === 'exclusive'
            ? round($totalAmount * $vatRate, 2)
            : round($totalAmount - ($totalAmount / (1 + $vatRate)), 2);
        $netTotal = $taxMode === 'exclusive' ? $totalAmount : round($totalAmount - $vatAmount, 2);
        $grossTotal = $taxMode === 'exclusive' ? round($totalAmount + $vatAmount, 2) : $totalAmount;
        $tendered = (float) ($orderData['paid'] ?? $grossTotal);

        if ($paymentMethod === 'cash' && $tendered < $grossTotal) {
            throw new Exception('Cash tendered must cover the total sale amount.');
        }

        // Validation for Debtor Sales
        if ($paymentMethod === 'debtor') {
            if (!$customer || $customer->name === 'Walk-in Customer' || $customer->name === 'Walk In') {
                throw new Exception("Credit sales require a valid registered customer account.");
            }

            // Calculate current outstanding debtor balance
            $currentBalance = DB::table('debtor_transactions')->where('customer_id', $customer->id)->sum('amount');

            $creditLimit = floatval($customer->credit_limit);
            if ($creditLimit > 0 && ($currentBalance + $grossTotal) > $creditLimit) {
                throw new Exception("Credit Limit Exceeded: Customer outstanding balance (KES " . number_format($currentBalance, 2) . ") + Sale (KES " . number_format($totalAmount, 2) . ") exceeds credit limit (KES " . number_format($creditLimit, 2) . ").");
            }
        }

        return DB::transaction(function () use ($orderData, $cartItems, $customer, $paymentMethod, $totalAmount, $vatAmount, $netTotal, $grossTotal, $taxMode, $customerPhone, $tendered) {
            $customer = Customer::lockForUpdate()->findOrFail($customer->id);
            if ($paymentMethod === 'debtor' && $customer->credit_limit > 0) {
                $outstanding = DB::table('debtor_transactions')->where('customer_id', $customer->id)->sum('amount');
                if (($outstanding + $grossTotal) > $customer->credit_limit) {
                    throw new Exception('Credit Limit Exceeded for this customer.');
                }
            }
            $orderStatus = in_array($paymentMethod, ['stk_push', 'debtor']) ? 0 : 1;
            $paymentStatus = in_array($paymentMethod, ['stk_push', 'debtor']) ? 'pending' : 'paid';

            $order = Order::create([
                'user_id' => auth()->id() ?? 1,
                'customer_id' => $customer->id,
                'discount' => $orderData['discount'] ?? 0,
                'sub_total' => $orderData['sub_total'],
                'total' => $grossTotal,
                'vat_amount' => $vatAmount,
                'net_total' => $netTotal,
                'tax_mode' => $taxMode,
                'paid' => ($paymentMethod === 'cash' ? $grossTotal : (($paymentMethod === 'stk_push') ? 0 : 0)),
                'due' => ($paymentMethod === 'stk_push' || $paymentMethod === 'debtor') ? $grossTotal : ($orderData['due'] ?? 0),
                'change_amount' => $paymentMethod === 'cash' ? round($tendered - $grossTotal, 2) : 0,
                'note' => $orderData['note'] ?? null,
                'status' => $orderStatus,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'mpesa_code' => $orderData['mpesa_code'] ?? null,
            ]);

            // Save Cart Line Items and update inventory
            foreach ($cartItems as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['id']);
                if ($product->quantity < $item['qty']) {
                    throw new Exception("Insufficient stock for {$product->name}.");
                }

                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'purchase_price' => $product->purchase_price,
                    'sub_total' => $item['price'] * $item['qty'],
                    'total' => $item['price'] * $item['qty'],
                ]);

                $product->decrement('quantity', $item['qty']);
            }

            // Execute Payment Path Posting
            if ($paymentMethod === 'cash') {
                // Cash Posting: Dr Cash on Hand (1010) / Cr Sales Revenue (4000) / Cr Output VAT (2200)
                $this->ledgerService->postJournalEntry(
                    "POS Cash Sale #{$order->id} - Customer: {$customer->name}",
                    [
                        ['account_code' => '1010', 'debit' => $grossTotal, 'credit' => 0, 'memo' => 'Cash received'],
                        ['account_code' => '4000', 'debit' => 0, 'credit' => $netTotal, 'memo' => 'Net POS Sales Revenue'],
                        ['account_code' => '2200', 'debit' => 0, 'credit' => $vatAmount, 'memo' => 'Output VAT Payable (16%)'],
                    ],
                    'Order',
                    $order->id
                );

                $this->postCostOfGoodsSold($order);
                return ['order' => $order, 'status' => 'completed', 'message' => 'Cash checkout completed successfully.'];

            } elseif ($paymentMethod === 'debtor') {
                // Debtor Posting: Dr Accounts Receivable (1100) / Cr Sales Revenue (4000) / Cr Output VAT (2200)
                $this->ledgerService->postJournalEntry(
                    "POS Credit Sale #{$order->id} - Customer: {$customer->name}",
                    [
                        ['account_code' => '1100', 'debit' => $grossTotal, 'credit' => 0, 'memo' => 'Accounts Receivable charge'],
                        ['account_code' => '4000', 'debit' => 0, 'credit' => $netTotal, 'memo' => 'Net POS Sales Revenue'],
                        ['account_code' => '2200', 'debit' => 0, 'credit' => $vatAmount, 'memo' => 'Output VAT Payable (16%)'],
                    ],
                    'Order',
                    $order->id
                );

                // Record transaction in Debtor Ledger
                DB::table('debtor_transactions')->insert([
                    'customer_id' => $customer->id,
                    'order_id' => $order->id,
                    'type' => 'invoice',
                    'reference' => 'INV-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'amount' => $grossTotal,
                    'transaction_date' => date('Y-m-d'),
                    'notes' => 'POS Credit Sale',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->postCostOfGoodsSold($order);
                return ['order' => $order, 'status' => 'completed', 'message' => 'Credit sale recorded against customer AR ledger.'];

            } elseif ($paymentMethod === 'stk_push') {
                // STK Push Path: Initiate Daraja request
                $phone = $customerPhone ?? $customer->phone ?? '0700000000';
                $stkResult = $this->darajaService->initiateStkPush($phone, $grossTotal, "POS-{$order->id}", "Order #{$order->id}");

                if ($stkResult['success']) {
                    $order->update([
                        'daraja_checkout_request_id' => $stkResult['CheckoutRequestID'],
                    ]);

                    // If demo mode, auto-confirm payment for seamless test workflow
                    if ($stkResult['is_demo'] ?? false) {
                        $this->confirmStkPushPayment($stkResult['CheckoutRequestID'], true);
                        return ['order' => $order->fresh(), 'status' => 'completed', 'message' => 'M-Pesa payment confirmed successfully.'];
                    }

                    return [
                        'order' => $order,
                        'status' => 'pending',
                        'checkout_request_id' => $stkResult['CheckoutRequestID'],
                        'message' => $stkResult['CustomerMessage'] ?? 'STK Push initiated. Waiting for M-Pesa PIN entry on customer phone.',
                    ];
                } else {
                    $order->update(['payment_status' => 'payment_failed', 'status' => 0]);
                    throw new Exception($stkResult['message'] ?? 'M-Pesa STK Push request failed.');
                }
            }

            throw new Exception("Invalid payment method.");
        });
    }

    /**
     * Confirm STK Push Payment Callback
     */
    public function confirmStkPushPayment(string $checkoutRequestId, bool $isSuccess, ?string $mpesaReceipt = null): bool
    {
        $order = Order::where('daraja_checkout_request_id', $checkoutRequestId)->first();
        if (!$order) {
            return false;
        }

        if ($order->payment_status === 'paid') {
            return true; // Already processed
        }

        if ($isSuccess) {
            return DB::transaction(function () use ($order, $mpesaReceipt) {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 1,
                    'paid' => $order->total,
                    'due' => 0,
                    'note' => ($order->note ? $order->note . ' | ' : '') . "M-Pesa Receipt: " . ($mpesaReceipt ?? 'MPESA-' . time()),
                ]);

                // M-Pesa Posting: Dr M-Pesa Clearing (1020) / Cr Sales Revenue (4000) / Cr Output VAT (2200)
                $this->ledgerService->postJournalEntry(
                    "POS M-Pesa Sale #{$order->id} - Receipt: {$mpesaReceipt}",
                    [
                        ['account_code' => '1020', 'debit' => $order->total, 'credit' => 0, 'memo' => 'M-Pesa clearing settlement'],
                        ['account_code' => '4000', 'debit' => 0, 'credit' => $order->net_total, 'memo' => 'Net POS Sales Revenue'],
                        ['account_code' => '2200', 'debit' => 0, 'credit' => $order->vat_amount, 'memo' => 'Output VAT Payable (16%)'],
                    ],
                    'Order',
                    $order->id
                );
                $this->postCostOfGoodsSold($order);

                return true;
            });
        } else {
            $order->update([
                'payment_status' => 'payment_failed',
                'status' => 0,
            ]);
            return false;
        }
    }

    private function postCostOfGoodsSold(Order $order): void
    {
        $cost = round($order->products()->sum(DB::raw('quantity * purchase_price')), 2);
        if ($cost <= 0) {
            return;
        }

        $this->ledgerService->postJournalEntry(
            "COGS for POS Sale #{$order->id}",
            [
                ['account_code' => '5000', 'debit' => $cost, 'credit' => 0, 'memo' => 'Cost of goods sold'],
                ['account_code' => '1200', 'debit' => 0, 'credit' => $cost, 'memo' => 'Inventory issued'],
            ],
            'Order',
            $order->id
        );
    }
}
