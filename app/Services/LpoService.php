<?php

namespace App\Services;

use App\Models\GoodsReceiptNote;
use App\Models\GrnItem;
use App\Models\Lpo;
use App\Models\LpoItem;
use App\Models\Product;
use App\Models\SupplierInvoice;
use Illuminate\Support\Facades\DB;
use Exception;

class LpoService
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Stage 1 & 2: Create / Issue LPO Requisition
     */
    public function createLpo(array $data, array $items): Lpo
    {
        return DB::transaction(function () use ($data, $items) {
            $lpoNumber = 'LPO-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $totalAmount = 0.00;

            $lpo = Lpo::create([
                'lpo_number' => $lpoNumber,
                'supplier_id' => $data['supplier_id'],
                'status' => 'issued', // Issued once approved
                'total_amount' => 0,
                'issued_at' => now(),
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $item) {
                $itemTotal = floatval($item['unit_cost']) * intval($item['qty_ordered']);
                $totalAmount += $itemTotal;

                LpoItem::create([
                    'lpo_id' => $lpo->id,
                    'product_id' => $item['product_id'],
                    'qty_ordered' => $item['qty_ordered'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $itemTotal,
                ]);
            }

            $lpo->update(['total_amount' => $totalAmount]);

            return $lpo;
        });
    }

    /**
     * Stage 3: Record Goods Received Note (GRN) against LPO and update stock
     */
    public function recordGoodsReceipt(Lpo $lpo, array $grnItems, ?string $notes = null): GoodsReceiptNote
    {
        if (!in_array($lpo->status, ['issued', 'goods_received'])) {
            throw new Exception("Cannot record GRN for LPO in '{$lpo->status}' state.");
        }

        return DB::transaction(function () use ($lpo, $grnItems, $notes) {
            $grnNumber = 'GRN-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

            $grn = GoodsReceiptNote::create([
                'grn_number' => $grnNumber,
                'lpo_id' => $lpo->id,
                'supplier_id' => $lpo->supplier_id,
                'received_date' => date('Y-m-d'),
                'received_by' => auth()->id(),
                'notes' => $notes,
            ]);

            foreach ($grnItems as $item) {
                $lpoItem = LpoItem::where('lpo_id', $lpo->id)->lockForUpdate()->findOrFail($item['lpo_item_id']);
                $receivedToDate = GrnItem::where('lpo_item_id', $lpoItem->id)->sum('qty_received');
                if (($receivedToDate + $item['qty_received']) > $lpoItem->qty_ordered) {
                    throw new Exception("Received quantity exceeds the quantity ordered for LPO item #{$lpoItem->id}.");
                }

                GrnItem::create([
                    'goods_receipt_note_id' => $grn->id,
                    'lpo_item_id' => $lpoItem->id,
                    'product_id' => $lpoItem->product_id,
                    'qty_received' => $item['qty_received'],
                ]);

                // Update inventory product stock
                Product::where('id', $lpoItem->product_id)->increment('product_qty', $item['qty_received']);
            }

            $lpo->update(['status' => 'goods_received']);

            return $grn;
        });
    }

    /**
     * Stage 4 & 5: Match Supplier Invoice (3-way match) and Post to Creditors & GL
     */
    public function matchAndPostInvoice(Lpo $lpo, array $invoiceData): SupplierInvoice
    {
        if ($lpo->status !== 'goods_received') {
            throw new Exception("LPO must have received goods before matching supplier invoice.");
        }

        return DB::transaction(function () use ($lpo, $invoiceData) {
            if (SupplierInvoice::where('lpo_id', $lpo->id)->exists()) {
                throw new Exception('A supplier invoice has already been recorded for this LPO.');
            }
            $invoiceAmount = floatval($invoiceData['invoice_amount']);
            $receivedAmount = (float) GrnItem::query()
                ->join('lpo_items', 'grn_items.lpo_item_id', '=', 'lpo_items.id')
                ->where('lpo_items.lpo_id', $lpo->id)
                ->sum(DB::raw('grn_items.qty_received * lpo_items.unit_cost'));
            $isMatch = abs($invoiceAmount - $receivedAmount) < 0.01;
            $status = $isMatch ? 'matched' : 'discrepancy';

            $invoice = SupplierInvoice::create([
                'lpo_id' => $lpo->id,
                'supplier_id' => $lpo->supplier_id,
                'invoice_number' => $invoiceData['invoice_number'],
                'invoice_date' => $invoiceData['invoice_date'],
                'invoice_amount' => $invoiceAmount,
                'status' => $status,
                'notes' => $invoiceData['notes'] ?? null,
            ]);

            $lpo->update(['status' => $status === 'matched' ? 'posted' : 'invoice_matched']);

            if ($status === 'matched') {
                // Post only the value actually received and matched.
                $this->ledgerService->postJournalEntry(
                    "LPO #{$lpo->lpo_number} Supplier Invoice Match - {$lpo->supplier->name}",
                    [
                        ['account_code' => '1200', 'debit' => $receivedAmount, 'credit' => 0, 'memo' => 'Inventory receipt valuation'],
                        ['account_code' => '2100', 'debit' => 0, 'credit' => $receivedAmount, 'memo' => 'Accounts Payable liability'],
                    ],
                    'Lpo',
                    $lpo->id
                );

                // Record in Creditors Sub-ledger
                DB::table('creditor_transactions')->insert([
                    'supplier_id' => $lpo->supplier_id,
                    'lpo_id' => $lpo->id,
                    'type' => 'invoice',
                    'reference' => 'SINV-' . $invoiceData['invoice_number'],
                    'amount' => $receivedAmount,
                    'transaction_date' => $invoiceData['invoice_date'],
                    'notes' => "LPO #{$lpo->lpo_number} Matched Invoice",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $invoice;
        });
    }
}
