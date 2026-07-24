<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Exception;

class CreditorService
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Record a supplier payment voucher
     */
    public function recordPayment(int $supplierId, float $amount, string $paymentMode = 'bank', ?string $notes = null): int
    {
        $supplier = Supplier::findOrFail($supplierId);

        if ($amount <= 0) {
            throw new Exception("Payment amount must be greater than zero.");
        }

        $accountCode = ($paymentMode === 'bank') ? '1030' : (($paymentMode === 'mpesa') ? '1020' : '1010');

        return DB::transaction(function () use ($supplier, $amount, $accountCode, $paymentMode, $notes) {
            // Post to General Ledger: Dr Accounts Payable (2100) / Cr Cash/Bank/M-Pesa
            $entry = $this->ledgerService->postJournalEntry(
                "Supplier Payment Voucher - {$supplier->name}",
                [
                    ['account_code' => '2100', 'debit' => $amount, 'credit' => 0, 'memo' => 'Accounts Payable settlement'],
                    ['account_code' => $accountCode, 'debit' => 0, 'credit' => $amount, 'memo' => "Payment via {$paymentMode}"],
                ],
                'Supplier',
                $supplier->id
            );

            $pvRef = 'PV-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Record in Creditors Ledger (negative amount reduces AP liability)
            return DB::table('creditor_transactions')->insertGetId([
                'supplier_id' => $supplier->id,
                'lpo_id' => null,
                'type' => 'payment',
                'reference' => $pvRef,
                'amount' => -$amount,
                'transaction_date' => date('Y-m-d'),
                'notes' => $notes ?? "Payment voucher via {$paymentMode}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Generate AP Aging Report
     */
    public function getAgingReport(): array
    {
        $suppliers = Supplier::all();
        $report = [];
        $now = time();

        foreach ($suppliers as $supp) {
            $transactions = DB::table('creditor_transactions')
                ->where('supplier_id', $supp->id)
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
                    'supplier_id' => $supp->id,
                    'supplier_name' => $supp->name,
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
