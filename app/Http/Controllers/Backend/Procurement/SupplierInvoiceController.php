<?php

namespace App\Http\Controllers\Backend\Procurement;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceiptNote;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Services\CreditorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Exception;

class SupplierInvoiceController extends Controller
{
    protected CreditorService $creditorService;

    public function __construct(CreditorService $creditorService)
    {
        $this->creditorService = $creditorService;
    }

    public function index()
    {
        abort_if(!auth()->user()->can('lpo_view'), 403);
        $suppliers = Supplier::orderBy('name')->get();
        return view('backend.procurement.supplier-invoices.index', compact('suppliers'));
    }

    private function getSupplierPaymentMap(array $supplierIds): array
    {
        if (empty($supplierIds)) {
            return [];
        }
        $rows = DB::table('creditor_transactions')
            ->whereIn('supplier_id', $supplierIds)
            ->where('type', 'payment')
            ->select('supplier_id', DB::raw('ABS(SUM(amount)) as total_paid'))
            ->groupBy('supplier_id')
            ->get();

        return $rows->pluck('total_paid', 'supplier_id')->toArray();
    }

    /**
     * Build the base query from GRN receipts joined to LPOs posted to AP.
     * This ensures we capture ALL 3-way matched invoices, even if the
     * supplier_invoices table is missing records.
     */
    private function buildBaseQuery()
    {
        return DB::table('goods_receipt_notes as grn')
            ->join('lpos', 'lpos.id', '=', 'grn.lpo_id')
            ->join('suppliers', 'suppliers.id', '=', 'grn.supplier_id')
            ->leftJoin('supplier_invoices as si', function ($join) {
                $join->on('si.lpo_id', '=', 'lpos.id')
                     ->where('si.status', '=', 'matched');
            })
            ->join('grn_items as gi', 'gi.goods_receipt_note_id', '=', 'grn.id')
            ->join('lpo_items as li', 'li.id', '=', 'gi.lpo_item_id')
            ->where('lpos.status', 'posted')
            ->select(
                'grn.id as grn_id',
                'grn.grn_number',
                'grn.received_date',
                'lpos.id as lpo_id',
                'lpos.lpo_number',
                'suppliers.id as supplier_id',
                'suppliers.name as supplier_name',
                'si.id as invoice_id',
                'si.invoice_number',
                'si.invoice_date',
                DB::raw('SUM(gi.qty_received * li.unit_cost) as received_value')
            )
            ->groupBy(
                'grn.id', 'grn.grn_number', 'grn.received_date',
                'lpos.id', 'lpos.lpo_number',
                'suppliers.id', 'suppliers.name',
                'si.id', 'si.invoice_number', 'si.invoice_date'
            );
    }

    public function data(Request $request)
    {
        abort_if(!auth()->user()->can('lpo_view'), 403);

        $query = $this->buildBaseQuery();

        if ($request->filled('supplier_id')) {
            $query->where('suppliers.id', $request->supplier_id);
        }
        if ($request->filled('from')) {
            $query->where('grn.received_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('grn.received_date', '<=', $request->to);
        }

        $allRows = collect($query->get());

        $allSupplierIds = $allRows->pluck('supplier_id')->unique()->values()->all();
        $paymentMap = $this->getSupplierPaymentMap($allSupplierIds);

        // FIFO allocation per supplier
        $allocationMap = [];
        $runningPaid = [];
        foreach ($allRows as $row) {
            $sid = $row->supplier_id;
            $totalPaid = $paymentMap[$sid] ?? 0;
            if (!isset($runningPaid[$sid])) {
                $runningPaid[$sid] = 0;
            }
            $remaining = $totalPaid - $runningPaid[$sid];
            $allocated = min($row->received_value, max($remaining, 0));
            $runningPaid[$sid] += $allocated;
            $allocationMap[$row->grn_id] = $allocated;
        }

        return DataTables::of($allRows)
            ->addIndexColumn()
            ->addColumn('grn_number', fn($d) => '<strong>' . $d->grn_number . '</strong>')
            ->addColumn('date', fn($d) => optional($d->received_date ? date('Y-m-d', strtotime($d->received_date)) : null)
                ? date('d M Y', strtotime($d->received_date))
                : '-')
            ->addColumn('supplier_name', fn($d) => $d->supplier_name)
            ->addColumn('invoice_number', fn($d) => $d->invoice_number ?? '-')
            ->addColumn('invoice_amount', fn($d) => number_format($d->received_value, 2))
            ->addColumn('paid', function ($d) use ($allocationMap) {
                $allocated = $allocationMap[$d->grn_id] ?? 0;
                return number_format($allocated, 2);
            })
            ->addColumn('balance', function ($d) use ($allocationMap) {
                $allocated = $allocationMap[$d->grn_id] ?? 0;
                return number_format(max($d->received_value - $allocated, 0), 2);
            })
            ->addColumn('payment_status', function ($d) use ($allocationMap) {
                $allocated = $allocationMap[$d->grn_id] ?? 0;
                $balance = $d->received_value - $allocated;
                if ($balance <= 0) {
                    return '<span class="badge bg-success">Paid</span>';
                } elseif ($allocated > 0) {
                    return '<span class="badge bg-warning">Partial</span>';
                }
                return '<span class="badge bg-danger">Unpaid</span>';
            })
            ->addColumn('action', function ($d) use ($allocationMap) {
                $allocated = $allocationMap[$d->grn_id] ?? 0;
                $balance = max($d->received_value - $allocated, 0);

                $btns = '<button class="btn-view-grn" '
                      . 'data-grn-id="' . $d->grn_id . '" '
                      . 'data-lpo="' . $d->lpo_number . '" '
                      . 'title="View GRN">'
                      . '<i class="fas fa-eye"></i></button>';

                if ($balance > 0) {
                    $btns .= ' <button class="btn-pay" '
                           . 'data-supplier-id="' . $d->supplier_id . '" '
                           . 'data-supplier="' . htmlspecialchars($d->supplier_name) . '" '
                           . 'data-balance="' . number_format($balance, 2, '.', '') . '" '
                           . 'title="Settle Invoice">'
                           . '<i class="fas fa-dollar-sign"></i></button>';
                }

                return $btns;
            })
            ->rawColumns(['grn_number', 'invoice_amount', 'paid', 'balance', 'payment_status', 'action'])
            ->toJson();
    }

    public function viewGrn($grnId)
    {
        abort_if(!auth()->user()->can('lpo_view'), 403);

        $grn = GoodsReceiptNote::with('items.product', 'lpo', 'supplier')
            ->findOrFail($grnId);

        $invoice = SupplierInvoice::where('lpo_id', $grn->lpo_id)
            ->where('status', 'matched')
            ->first();

        // Compute received value
        $receivedValue = $grn->items->sum(function ($item) {
            return $item->qty_received * ($item->lpoItem->unit_cost ?? 0);
        });

        return response()->json([
            'grn_number' => $grn->grn_number,
            'received_date' => $grn->received_date,
            'supplier' => $grn->supplier->name ?? '-',
            'lpo_number' => $grn->lpo->lpo_number ?? '-',
            'invoice_number' => $invoice->invoice_number ?? '-',
            'invoice_amount' => number_format($invoice->invoice_amount ?? $receivedValue, 2),
            'items' => $grn->items->map(fn($item) => [
                'product' => $item->product->name ?? '-',
                'qty_received' => $item->qty_received,
                'unit_cost' => number_format($item->lpoItem->unit_cost ?? 0, 2),
                'line_total' => number_format($item->qty_received * ($item->lpoItem->unit_cost ?? 0), 2),
            ]),
        ]);
    }

    public function payInvoice(Request $request)
    {
        abort_if(!auth()->user()->can('creditors_payment_create'), 403);

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:1',
            'payment_mode' => 'required|in:cash,bank,mpesa',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->creditorService->recordPayment(
                $request->supplier_id,
                floatval($request->amount),
                $request->payment_mode,
                $request->notes
            );

            return response()->json([
                'message' => 'Payment recorded and posted to General Ledger successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function kpis()
    {
        abort_if(!auth()->user()->can('lpo_view'), 403);

        $rows = $this->buildBaseQuery()->get();

        $totalInvoices = $rows->count();
        $totalAmount = $rows->sum('received_value');

        $supplierIds = $rows->pluck('supplier_id')->unique()->values()->all();
        $totalPaid = 0;
        if (!empty($supplierIds)) {
            $totalPaid = abs(DB::table('creditor_transactions')
                ->whereIn('supplier_id', $supplierIds)
                ->where('type', 'payment')
                ->sum('amount'));
        }

        $outstanding = max($totalAmount - $totalPaid, 0);

        return response()->json([
            'total_invoices' => $totalInvoices,
            'total_amount' => number_format($totalAmount, 2),
            'amount_paid' => number_format($totalPaid, 2),
            'outstanding' => number_format($outstanding, 2),
        ]);
    }
}
