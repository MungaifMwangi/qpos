<?php

namespace App\Http\Controllers\Backend\Procurement;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceiptNote;
use App\Models\Lpo;
use App\Models\LpoItem;
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

    // ---------------------------------------------------------------
    // Index — DataTable list of all LPOs
    // ---------------------------------------------------------------
    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('lpo_view'), 403);

        if ($request->ajax()) {
            $query = Lpo::with('supplier');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('from')) {
                $query->whereDate('created_at', '>=', $request->from);
            }

            if ($request->filled('to')) {
                $query->whereDate('created_at', '<=', $request->to);
            }

            $lpos = $query->orderBy('id', 'desc');

            return DataTables::of($lpos)
                ->addIndexColumn()
                ->addColumn('lpo_number', fn($d) => '<strong>' . $d->lpo_number . '</strong>')
                ->addColumn('supplier',   fn($d) => $d->supplier->name ?? '-')
                ->addColumn('total_amount', fn($d) => number_format($d->total_amount, 2))
                ->addColumn('date', fn($d) => \Carbon\Carbon::parse($d->created_at)->format('d M, Y'))
                ->addColumn('status', function ($d) {
                    $map = [
                        'requisition'    => ['secondary', 'Requisition'],
                        'issued'         => ['info',      'Issued'],
                        'goods_received' => ['warning',   'Goods Received'],
                        'invoice_matched'=> ['primary',   'Invoice Matched'],
                        'posted'         => ['success',   'Posted to AP'],
                    ];
                    [$colour, $label] = $map[$d->status] ?? ['dark', ucfirst($d->status)];
                    return '<span class="badge bg-' . $colour . '">' . $label . '</span>';
                })
                ->addColumn('action', function ($d) {
                    $btns = '<a href="' . route('backend.admin.lpo.show', $d->id) . '"'
                          . ' class="btn btn-primary btn-sm m-1">'
                          . '<i class="fas fa-eye"></i> View</a>';

                    // Print LPO button
                    $btns .= '<a href="' . route('backend.admin.lpo.print', $d->id) . '"'
                           . ' target="_blank" class="btn btn-secondary btn-sm m-1">'
                           . '<i class="fas fa-print"></i> Print LPO</a>';

                    // Receive GRN button — only for issued status
                    if ($d->status === 'issued' && auth()->user()->can('grn_receive')) {
                        $btns .= '<button class="btn btn-warning btn-sm m-1 receive-btn"'
                               . ' data-id="' . $d->id . '"'
                               . ' data-lpo="' . $d->lpo_number . '">'
                               . '<i class="fas fa-truck-loading"></i> Receive GRN</button>';
                    }

                    // Match Invoice button — for goods_received status
                    if ($d->status === 'goods_received' && auth()->user()->can('lpo_invoice_match')) {
                        $btns .= '<a href="' . route('backend.admin.lpo.show', $d->id) . '"'
                               . ' class="btn btn-primary btn-sm m-1">'
                               . '<i class="fas fa-file-invoice-dollar"></i> Match Invoice</a>';
                    }

                    return $btns;
                })
                ->rawColumns(['lpo_number', 'status', 'action'])
                ->toJson();
        }

        return view('backend.procurement.lpo.index');
    }

    // ---------------------------------------------------------------
    // Create form
    // ---------------------------------------------------------------
    public function create()
    {
        abort_if(!auth()->user()->can('lpo_create'), 403);

        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::where('status', 1)->orderBy('name')->get();

        // Pre-map to a plain array so @json in the view has no closure syntax
        $productData = $products->map(fn($p) => [
            'id'             => $p->id,
            'name'           => $p->name,
            'purchase_price' => $p->purchase_price ?? 0,
        ])->values()->all();

        return view('backend.procurement.lpo.create', compact('suppliers', 'products', 'productData'));
    }

    // ---------------------------------------------------------------
    // Store new LPO
    // ---------------------------------------------------------------
    public function store(Request $request)
    {
        abort_if(!auth()->user()->can('lpo_create'), 403);

        $request->validate([
            'supplier_id'              => 'required|exists:suppliers,id',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:products,id',
            'items.*.qty_ordered'      => 'required|integer|min:1',
            'items.*.unit_cost'        => 'required|numeric|min:0.01',
        ]);

        try {
            $lpo = $this->lpoService->createLpo($request->all(), $request->items);
            return redirect()
                ->route('backend.admin.lpo.show', $lpo->id)
                ->with('success', "LPO {$lpo->lpo_number} created and issued successfully.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // ---------------------------------------------------------------
    // Show single LPO (detail + invoice match)
    // ---------------------------------------------------------------
    public function show(int $id)
    {
        abort_if(!auth()->user()->can('lpo_view'), 403);

        $lpo = Lpo::with([
            'supplier',
            'items.product.unit',
            'goodsReceiptNotes.items.product',
            'supplierInvoice',
        ])->findOrFail($id);

        return view('backend.procurement.lpo.show', compact('lpo'));
    }

    // ---------------------------------------------------------------
    // Print-ready LPO view (no master layout)
    // ---------------------------------------------------------------
    public function printLpo(int $id)
    {
        abort_if(!auth()->user()->can('lpo_view'), 403);

        $lpo = Lpo::with(['supplier', 'items.product.unit'])->findOrFail($id);
        return view('backend.procurement.lpo.print', compact('lpo'));
    }

    // ---------------------------------------------------------------
    // AJAX — Return LPO items JSON for the GRN modal
    // ---------------------------------------------------------------
    public function getItems(int $id)
    {
        abort_if(!auth()->user()->can('grn_receive'), 403);

        $lpo = Lpo::with(['items.product', 'items.grnItems'])->findOrFail($id);

        $items = $lpo->items->map(function ($item) {
            $received = $item->grnItems->sum('qty_received');
            return [
                'id'           => $item->id,
                'product_name' => $item->product->name ?? '-',
                'qty_ordered'  => $item->qty_ordered,
                'unit_cost'    => $item->unit_cost,
                'qty_received' => $received,
                'qty_remaining'=> $item->qty_ordered - $received,
            ];
        });

        return response()->json([
            'lpo_number' => $lpo->lpo_number,
            'supplier'   => $lpo->supplier->name ?? '-',
            'items'      => $items,
        ]);
    }

    // ---------------------------------------------------------------
    // Store GRN — supports both regular POST and AJAX POST
    // ---------------------------------------------------------------
    public function storeGrn(Request $request, int $id)
    {
        abort_if(!auth()->user()->can('grn_receive'), 403);

        $lpo = Lpo::findOrFail($id);

        $request->validate([
            'grn_items'                     => 'required|array|min:1',
            'grn_items.*.lpo_item_id'       => 'required|exists:lpo_items,id',
            'grn_items.*.qty_received'      => 'required|integer|min:0',
            'grn_notes'                     => 'nullable|string|max:500',
        ]);

        try {
            $grn = $this->lpoService->recordGoodsReceipt(
                $lpo,
                $request->grn_items,
                $request->grn_notes
            );

            if ($request->ajax()) {
                return response()->json([
                    'message'    => "GRN {$grn->grn_number} recorded — inventory updated.",
                    'grn_number' => $grn->grn_number,
                ]);
            }

            return redirect()
                ->route('backend.admin.lpo.show', $lpo->id)
                ->with('success', "GRN {$grn->grn_number} logged and stock updated.");
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // ---------------------------------------------------------------
    // Store supplier invoice (3-way match)
    // ---------------------------------------------------------------
    public function storeInvoice(Request $request, int $id)
    {
        abort_if(!auth()->user()->can('lpo_invoice_match'), 403);

        $lpo = Lpo::findOrFail($id);

        $request->validate([
            'invoice_number' => 'required|string|max:100',
            'invoice_date'   => 'required|date',
            'invoice_amount' => 'required|numeric|min:0.01',
            'notes'          => 'nullable|string|max:500',
        ]);

        try {
            $invoice = $this->lpoService->matchAndPostInvoice($lpo, $request->all());

            $msg = $invoice->status === 'matched'
                ? 'Supplier invoice matched and posted to the Creditors AP ledger.'
                : 'Invoice recorded with a discrepancy — flagged for review.';

            return redirect()
                ->route('backend.admin.lpo.show', $lpo->id)
                ->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
