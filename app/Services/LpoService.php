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
     * Stage 1 & 2: Create and issue an LPO with multiple line items.
     */
    public function createLpo(array $data, array $items): Lpo
    {
        return DB::transaction(function () use ($data, $items) {
            $lpoNumber = 'LPO-' . date('Y') . '-' . str_pad(
                (Lpo::whereYear('created_at', date('Y'))->count() + 1),
                4, '0', STR_PAD_LEFT
            );
            $totalAmount = 0.00;

            $lpo = Lpo::create([
                'lpo_number'  => $lpoNumber,
                'supplier_id' => $data['supplier_id'],
                'status'      => 'issued',
                'total_amount'=> 0,
                'issued_at'   => now(),
                'notes'       => $data['notes'] ?? null,
                'created_by'  => auth()->id(),
            ]);

            foreach ($items as $item) {
                $qty      = intval($item['qty_ordered']);
                $cost     = floatval($item['unit_cost']);
                $lineTotal = round($qty * $cost, 2);
                $totalAmount += $lineTotal;

                LpoItem::create([
                    'lpo_id'      => $lpo->id,
                    'product_id'  => $item['product_id'],
                    'qty_ordered' => $qty,
                    'unit_cost'   => $cost,
                    'total_cost'  => $lineTotal,
                ]);
            }

            $lpo->update(['total_amount' => round($totalAmount, 2)]);

            return $lpo;
        });
    }

    /**
     * Stage 3: Record a Goods Receipt Note (GRN) against an LPO.
     * Updates product stock using the correct `quantity` column.
     * May be called multiple times (partial deliveries) while status stays `goods_received`.
     */
    public function recordGoodsReceipt(Lpo $lpo, array $grnItems, ?string $notes = null): GoodsReceiptNote
    {
        if (!in_array($lpo->status, ['issued', 'goods_received'])) {
            throw new Exception("Cannot record a GRN for an LPO in '{$lpo->status}' status.");
        }

        return DB::transaction(function () use ($lpo, $grnItems, $notes) {
            $grnNumber = 'GRN-' . date('Y') . '-' . str_pad(
                (GoodsReceiptNote::whereYear('created_at', date('Y'))->count() + 1),
                4, '0', STR_PAD_LEFT
            );

            $grn = GoodsReceiptNote::create([
                'grn_number'   => $grnNumber,
                'lpo_id'       => $lpo->id,
                'supplier_id'  => $lpo->supplier_id,
                'received_date'=> date('Y-m-d'),
                'received_by'  => auth()->id(),
                'notes'        => $notes,
            ]);

            $totalReceived = 0.00;

            foreach ($grnItems as $item) {
                $qty = intval($item['qty_received']);
                if ($qty <= 0) {
                    continue; // skip zero-quantity lines
                }

                $lpoItem = LpoItem::where('lpo_id', $lpo->id)
                    ->lockForUpdate()
                    ->findOrFail($item['lpo_item_id']);

                // Check cumulative received does not exceed ordered qty
                $alreadyReceived = GrnItem::where('lpo_item_id', $lpoItem->id)->sum('qty_received');
                $remaining = $lpoItem->qty_ordered - $alreadyReceived;

                if ($qty > $remaining) {
                    throw new Exception(
                        "Received quantity ({$qty}) for \"{$lpoItem->product->name}\" exceeds remaining ordered quantity ({$remaining})."
                    );
                }

                GrnItem::create([
                    'goods_receipt_note_id' => $grn->id,
                    'lpo_item_id'           => $lpoItem->id,
                    'product_id'            => $lpoItem->product_id,
                    'qty_received'          => $qty,
                    'unit_cost'             => $lpoItem->unit_cost,
                    'line_total'            => round($qty * $lpoItem->unit_cost, 2),
                ]);

                // Increment inventory — correct column is `quantity`
                Product::where('id', $lpoItem->product_id)->increment('quantity', $qty);

                $totalReceived += round($qty * $lpoItem->unit_cost, 2);
            }

            if ($totalReceived <= 0) {
                throw new Exception('At least one item must have a positive quantity received.');
            }

            $lpo->update(['status' => 'goods_received']);

            return $grn;
        });
    }

    /**
     * Stage 4 & 5: 3-way match supplier invoice against PO + GRN, then post to creditors GL.
     */
    public function matchAndPostInvoice(Lpo $lpo, array $invoiceData): SupplierInvoice
    {
        if (!in_array($lpo->status, ['goods_received', 'invoice_matched'])) {
            throw new Exception("Goods must be received before matching a supplier invoice.");
        }

        return DB::transaction(function () use ($lpo, $invoiceData) {
            if (SupplierInvoice::where('lpo_id', $lpo->id)->where('status', 'matched')->exists()) {
                throw new Exception('A matched supplier invoice already exists for this LPO.');
            }

            $invoiceAmount  = floatval($invoiceData['invoice_amount']);
            $receivedValue  = (float) GrnItem::query()
                ->join('lpo_items', 'grn_items.lpo_item_id', '=', 'lpo_items.id')
                ->join('goods_receipt_notes', 'grn_items.goods_receipt_note_id', '=', 'goods_receipt_notes.id')
                ->where('lpo_items.lpo_id', $lpo->id)
                ->sum(DB::raw('grn_items.qty_received * lpo_items.unit_cost'));

            $isMatch = abs($invoiceAmount - $receivedValue) < 0.01;
            $status  = $isMatch ? 'matched' : 'discrepancy';

            $invoice = SupplierInvoice::create([
                'lpo_id'         => $lpo->id,
                'supplier_id'    => $lpo->supplier_id,
                'invoice_number' => $invoiceData['invoice_number'],
                'invoice_date'   => $invoiceData['invoice_date'],
                'invoice_amount' => $invoiceAmount,
                'status'         => $status,
                'notes'          => $invoiceData['notes'] ?? null,
            ]);

            $lpo->update(['status' => $status === 'matched' ? 'posted' : 'invoice_matched']);

            if ($status === 'matched') {
                // Post to GL: Dr Inventory (1200) / Cr Accounts Payable (2100)
                $this->ledgerService->postJournalEntry(
                    "Supplier Invoice Match — LPO #{$lpo->lpo_number} — {$lpo->supplier->name}",
                    [
                        ['account_code' => '1200', 'debit' => $receivedValue, 'credit' => 0,
                         'memo' => "Inventory receipt — GRN against LPO #{$lpo->lpo_number}"],
                        ['account_code' => '2100', 'debit' => 0, 'credit' => $receivedValue,
                         'memo' => "AP liability — Supplier: {$lpo->supplier->name}"],
                    ],
                    'Lpo',
                    $lpo->id
                );

                // Record in creditors sub-ledger
                DB::table('creditor_transactions')->insert([
                    'supplier_id'      => $lpo->supplier_id,
                    'lpo_id'           => $lpo->id,
                    'type'             => 'invoice',
                    'reference'        => 'SINV-' . $invoiceData['invoice_number'],
                    'amount'           => $receivedValue,
                    'transaction_date' => $invoiceData['invoice_date'],
                    'notes'            => "Matched invoice for LPO #{$lpo->lpo_number}",
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            return $invoice;
        });
    }
}
