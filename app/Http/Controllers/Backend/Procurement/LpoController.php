<?php

namespace App\Http\Controllers\Backend\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Lpo;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\LpoService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Exception;

class LpoController extends Controller
{
    protected LpoService $lpoService;

    public function __construct(LpoService $lpoService)
    {
        $this->lpoService = $lpoService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $lpos = Lpo::with('supplier')->orderBy('id', 'desc')->get();
            return DataTables::of($lpos)
                ->addIndexColumn()
                ->addColumn('supplier', fn($data) => $data->supplier->name ?? '-')
                ->addColumn('total_amount', fn($data) => 'KES ' . number_format($data->total_amount, 2))
                ->addColumn('status', function ($data) {
                    $badges = [
                        'requisition' => '<span class="badge bg-secondary">Requisition</span>',
                        'issued' => '<span class="badge bg-info">Issued</span>',
                        'goods_received' => '<span class="badge bg-warning">Goods Received (GRN)</span>',
                        'invoice_matched' => '<span class="badge bg-primary">Invoice Matched</span>',
                        'posted' => '<span class="badge bg-success">Posted to AP</span>',
                    ];
                    return $badges[$data->status] ?? '<span class="badge bg-dark">' . $data->status . '</span>';
                })
                ->addColumn('action', function ($data) {
                    return '<a href="' . route('backend.admin.lpo.show', $data->id) . '" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View / Process</a>';
                })
                ->rawColumns(['supplier', 'total_amount', 'status', 'action'])
                ->toJson();
        }

        return view('backend.procurement.lpo.index');
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::where('status', 1)->get();
        return view('backend.procurement.lpo.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            $lpo = $this->lpoService->createLpo($request->all(), $request->items);
            return redirect()->route('backend.admin.lpo.show', $lpo->id)->with('success', 'LPO created and issued successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(int $id)
    {
        $lpo = Lpo::with(['supplier', 'items.product', 'goodsReceiptNotes.items.product', 'supplierInvoice'])->findOrFail($id);
        return view('backend.procurement.lpo.show', compact('lpo'));
    }

    public function storeGrn(Request $request, int $id)
    {
        $lpo = Lpo::findOrFail($id);
        $request->validate([
            'grn_items' => 'required|array|min:1',
            'grn_items.*.lpo_item_id' => 'required|exists:lpo_items,id',
            'grn_items.*.qty_received' => 'required|integer|min:1',
        ]);

        try {
            $this->lpoService->recordGoodsReceipt($lpo, $request->grn_items, $request->notes);
            return redirect()->route('backend.admin.lpo.show', $lpo->id)->with('success', 'Goods Received Note (GRN) logged and inventory updated.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeInvoice(Request $request, int $id)
    {
        $lpo = Lpo::findOrFail($id);
        $request->validate([
            'invoice_number' => 'required|string',
            'invoice_date' => 'required|date',
            'invoice_amount' => 'required|numeric|min:0',
        ]);

        try {
            $invoice = $this->lpoService->matchAndPostInvoice($lpo, $request->all());
            $msg = ($invoice->status === 'matched')
                ? 'Supplier Invoice matched successfully and posted to Creditors AP general ledger.'
                : 'Warning: Invoice amount discrepancy flagged for management review.';

            return redirect()->route('backend.admin.lpo.show', $lpo->id)->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
