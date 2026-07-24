<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Exception;

class DebtorService
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Record a customer collection payment receipt
     */
    public function recordReceipt(int $customerId, float $amount, string $paymentMode = 'cash', ?string $notes = null): int
    {
        $customer = Customer::findOrFail($customerId);

        if ($amount <= 0) {
            throw new Exception("Receipt amount must be greater than zero.");
        }

        $accountCode = ($paymentMode === 'bank') ? '1030' : (($paymentMode === 'mpesa') ? '1020' : '1010');

        return DB::transaction(function () use ($customer, $amount, $accountCode, $paymentMode, $notes) {
            $outstanding = (float) DB::table('debtor_transactions')->where('customer_id', $customer->id)->sum('amount');
            if ($amount > $outstanding + 0.01) {
                throw new Exception("Receipt cannot exceed the customer's outstanding balance.");
            }
            // Post to General Ledger: Dr Cash/Bank/M-Pesa / Cr Accounts Receivable (1100)
            $entry = $this->ledgerService->postJournalEntry(
                "Customer Payment Receipt - {$customer->name}",
                [
                    ['account_code' => $accountCode, 'debit' => $amount, 'credit' => 0, 'memo' => "Receipt via {$paymentMode}"],
                    ['account_code' => '1100', 'debit' => 0, 'credit' => $amount, 'memo' => 'Accounts Receivable collection'],
                ],
                'Customer',
                $customer->id
            );

            $receiptRef = 'REC-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Record in Debtor Ledger (negative amount reduces AR balance)
            $receiptId = DB::table('debtor_transactions')->insertGetId([
                'customer_id' => $customer->id,
                'order_id' => null,
                'type' => 'receipt',
                'reference' => $receiptRef,
                'amount' => -$amount,
                'transaction_date' => date('Y-m-d'),
                'notes' => $notes ?? "Payment receipt via {$paymentMode}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // FIFO allocation: settle the oldest outstanding invoices first.
            $remaining = $amount;
            $invoices = DB::table('debtor_transactions as dt')
                ->leftJoin('debtor_receipt_allocations as dra', 'dt.id', '=', 'dra.invoice_transaction_id')
                ->where('dt.customer_id', $customer->id)
                ->where('dt.type', 'invoice')
                ->groupBy('dt.id', 'dt.order_id', 'dt.amount', 'dt.transaction_date')
                ->select('dt.id', 'dt.order_id', 'dt.amount', DB::raw('COALESCE(SUM(dra.amount), 0) as allocated'))
                ->orderBy('dt.transaction_date')->orderBy('dt.id')->lockForUpdate()->get();

            foreach ($invoices as $invoice) {
                if ($remaining <= 0) break;
                $open = round((float) $invoice->amount - (float) $invoice->allocated, 2);
                if ($open <= 0) continue;
                $applied = min($remaining, $open);
                DB::table('debtor_receipt_allocations')->insert([
                    'receipt_transaction_id' => $receiptId,
                    'invoice_transaction_id' => $invoice->id,
                    'amount' => $applied,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                if ($invoice->order_id && ($order = Order::lockForUpdate()->find($invoice->order_id))) {
                    $order->paid = round((float) $order->paid + $applied, 2);
                    $order->due = max(0, round((float) $order->due - $applied, 2));
                    $order->payment_status = $order->due <= 0 ? 'paid' : 'pending';
                    $order->status = $order->due <= 0;
                    $order->save();
                }
                $remaining = round($remaining - $applied, 2);
            }

            return $receiptId;
        });
    }

    /**
     * Generate Customer Statement
     */
    public function getCustomerStatement(int $customerId, ?string $fromDate = null, ?string $toDate = null): array
    {
        $customer = Customer::findOrFail($customerId);
        $query = DB::table('debtor_transactions')
            ->where('customer_id', $customer->id)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        if ($fromDate) {
            $query->where('transaction_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('transaction_date', '<=', $toDate);
        }

        $transactions = $query->get();
        $runningBalance = 0.00;
        $formatted = [];

        foreach ($transactions as $tx) {
            $runningBalance += floatval($tx->amount);
            $formatted[] = [
                'id' => $tx->id,
                'date' => $tx->transaction_date,
                'reference' => $tx->reference,
                'type' => ucfirst($tx->type),
                'notes' => $tx->notes,
                'debit' => $tx->amount > 0 ? floatval($tx->amount) : 0,
                'credit' => $tx->amount < 0 ? abs(floatval($tx->amount)) : 0,
                'balance' => $runningBalance,
            ];
        }

        return [
            'customer' => $customer,
            'current_balance' => $runningBalance,
            'transactions' => $formatted,
        ];
    }

    /**
     * Generate AR Aging Report
     */
    public function getAgingReport(): array
    {
        $customers = Customer::all();
        $report = [];
        $now = time();

        foreach ($customers as $cust) {
            $transactions = DB::table('debtor_transactions')
                ->where('customer_id', $cust->id)
                ->get();

            $current = 0.00;
            $days30 = 0.00;
            $days60 = 0.00;
            $days90Plus = 0.00;
            $total = 0.00;

            foreach ($transactions as $tx) {
                $amount = floatval($tx->amount);
                $total += $amount;

                $ageDays = floor(($now - strtotime($tx->transaction_date)) / 86400);

                if ($ageDays <= 30) {
                    $current += $amount;
                } elseif ($ageDays <= 60) {
                    $days30 += $amount;
                } elseif ($ageDays <= 90) {
                    $days60 += $amount;
                } else {
                    $days90Plus += $amount;
                }
            }

            if (abs($total) > 0.01) {
                $report[] = [
                    'customer_id' => $cust->id,
                    'customer_name' => $cust->name,
                    'credit_limit' => floatval($cust->credit_limit),
                    'current' => $current,
                    'days_30' => $days30,
                    'days_60' => $days60,
                    'days_90_plus' => $days90Plus,
                    'total_outstanding' => $total,
                ];
            }
        }

        return $report;
    }
}
