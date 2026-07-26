<?php

namespace App\Http\Controllers\Backend\Product;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceiptNote;
use App\Models\GrnItem;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('purchase_view'), 403);

        if ($request->ajax()) {
            $query = Purchase::with('supplier');

            if ($request->filled('from')) {
                $query->whereDate('date', '>=', $request->from);
            }

            if ($request->filled('to')) {
                $query->whereDate('date', '<=', $request->to);
            }

            $purchases = $query->latest();

            return DataTables::of($purchases)
                ->addIndexColumn()
                ->addColumn('supplier', fn($data) => $data->supplier->name ?? '-')
                ->addColumn('id', fn($data) => '#' . $data->id)
                ->addColumn('total', fn($data) => number_format($data->grand_total, 2, '.', ','))
                ->addColumn('created_at', fn($data) => \Carbon\Carbon::parse($data->date)->format('d M, Y'))
                ->addColumn('action', function ($data) {
                    $editUrl = route('backend.admin.purchase.create', ['purchase_id' => $data->id]);
                    $viewUrl = route('backend.admin.purchase.products', $data->id);
                    return '<div style="display:flex;gap:6px;justify-content:center">'
                        . '<a href="' . $editUrl . '" title="Edit" style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:#eef2ff;color:#4f46e5;font-size:12px"><i class="fas fa-pen"></i></a>'
                        . '<a href="' . $viewUrl . '" title="View Items" style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:#ecfdf5;color:#059669;font-size:12px"><i class="fas fa-eye"></i></a>'
                        . '</div>';
                })
                ->rawColumns(['supplier', 'id', 'total', 'created_at', 'action'])
                ->toJson();
        }

        return view('backend.purchase.index');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {


        abort_if(!auth()->user()->can('purchase_create'), 403);
        return view('backend.purchase.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        abort_if(!auth()->user()->can('purchase_create'), 403);
        if ($request->wantsJson()) {
            // Step 1: Validate the request data
            $validatedData = $request->validate([
                'products' => 'required|array',
                'purchase_id' => 'nullable|integer',
                'date' => 'nullable|date',
                'supplierId' => 'required|exists:suppliers,id',
                'totals' => 'required|array',
                'totals.subTotal' => 'required|numeric',
                'totals.tax' => 'nullable|numeric',
                'totals.discount' => 'nullable|numeric',
                'totals.shipping' => 'nullable|numeric',
                'totals.grandTotal' => 'required|numeric',
            ]);

            if ($validatedData['purchase_id'] == null) {
                DB::beginTransaction();
                try {
                    $purchase = Purchase::create([
                        'supplier_id' => $validatedData['supplierId'],
                        'user_id' => auth()->id(),
                        'sub_total' => $validatedData['totals']['subTotal'],
                        'tax' => $validatedData['totals']['tax'],
                        'discount_value' => $validatedData['totals']['discount'],
                        'shipping' => $validatedData['totals']['shipping'],
                        'grand_total' => $validatedData['totals']['grandTotal'],
                        'date' => $validatedData['date'] ?? Carbon::now()->toDateString(),
                        'status' => 1,
                    ]);

                    $grnNumber = 'GRN-' . date('Y') . '-' . str_pad(
                        (GoodsReceiptNote::whereYear('created_at', date('Y'))->count() + 1),
                        4, '0', STR_PAD_LEFT
                    );

                    $grn = GoodsReceiptNote::create([
                        'grn_number'   => $grnNumber,
                        'lpo_id'       => null,
                        'purchase_id'  => $purchase->id,
                        'supplier_id'  => $validatedData['supplierId'],
                        'received_date'=> $validatedData['date'] ?? Carbon::now()->toDateString(),
                        'received_by'  => auth()->id(),
                        'notes'        => 'Auto-generated from direct purchase #' . $purchase->id,
                    ]);

                    foreach ($validatedData['products'] as $product) {
                        $existingProduct = Product::findOrFail($product['id']);
                        PurchaseItem::create([
                            'purchase_id' => $purchase->id,
                            'product_id' => $product['id'],
                            'purchase_price' => $product['purchase_price'],
                            'price' => $product['price'],
                            'quantity' => $product['qty'],
                        ]);
                        $existingProduct->increment('quantity', $product['qty']);

                        GrnItem::create([
                            'goods_receipt_note_id' => $grn->id,
                            'lpo_item_id'           => null,
                            'product_id'            => $product['id'],
                            'qty_received'          => $product['qty'],
                            'unit_cost'             => $product['purchase_price'],
                            'line_total'            => round($product['qty'] * $product['purchase_price'], 2),
                        ]);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            } else {
                DB::beginTransaction();
                try {
                    $purchase = Purchase::findOrFail($validatedData['purchase_id']);
                    $purchase->update([
                        'supplier_id' => $validatedData['supplierId'],
                        'user_id' => auth()->id(),
                        'sub_total' => $validatedData['totals']['subTotal'],
                        'tax' => $validatedData['totals']['tax'],
                        'discount_value' => $validatedData['totals']['discount'],
                        'shipping' => $validatedData['totals']['shipping'],
                        'grand_total' => $validatedData['totals']['grandTotal'],
                        'date' => $validatedData['date'] ?? Carbon::now()->toDateString(),
                        'status' => 1,
                    ]);

                    // Delete existing GRN items and recreate
                    $existingGrn = GoodsReceiptNote::where('purchase_id', $purchase->id)->first();
                    if ($existingGrn) {
                        GrnItem::where('goods_receipt_note_id', $existingGrn->id)->delete();
                        $grn = $existingGrn;
                    } else {
                        $grnNumber = 'GRN-' . date('Y') . '-' . str_pad(
                            (GoodsReceiptNote::whereYear('created_at', date('Y'))->count() + 1),
                            4, '0', STR_PAD_LEFT
                        );
                        $grn = GoodsReceiptNote::create([
                            'grn_number'   => $grnNumber,
                            'lpo_id'       => null,
                            'purchase_id'  => $purchase->id,
                            'supplier_id'  => $validatedData['supplierId'],
                            'received_date'=> $validatedData['date'] ?? Carbon::now()->toDateString(),
                            'received_by'  => auth()->id(),
                            'notes'        => 'Auto-generated from direct purchase #' . $purchase->id,
                        ]);
                    }

                    foreach ($validatedData['products'] as $product) {
                        $existingProduct = Product::findOrFail($product['id']);
                        $oldPurchaseItem = PurchaseItem::find($product['item_id'] ?? 0);
                        $oldQuantity = $oldPurchaseItem ? $oldPurchaseItem->quantity : 0;
                        PurchaseItem::updateOrCreate(
                            ['id' => $product['item_id'] ?? null],
                            [
                                'purchase_id' => $purchase->id,
                                'product_id' => $product['id'],
                                'purchase_price' => $product['purchase_price'],
                                'price' => $product['price'],
                                'quantity' => $product['qty'],
                            ]
                        );
                        $existingProduct->decrement('quantity', $oldQuantity);
                        $existingProduct->increment('quantity', $product['qty']);

                        GrnItem::create([
                            'goods_receipt_note_id' => $grn->id,
                            'lpo_item_id'           => null,
                            'product_id'            => $product['id'],
                            'qty_received'          => $product['qty'],
                            'unit_cost'             => $product['purchase_price'],
                            'line_total'            => round($product['qty'] * $product['purchase_price'], 2),
                        ]);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            }
            // Step 4: Return a response
            return response()->json([
                'message' => 'Purchase saved successfully.',
                'purchase' => $purchase,
            ], 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {

        if ($request->wantsJson()) {
            $purchase = Purchase::with('items', 'supplier')->findOrFail($id);
            return $purchase;
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

        abort_if(!auth()->user()->can('purchase_update'), 403);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {

        abort_if(!auth()->user()->can('purchase_update'), 403);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {

        abort_if(!auth()->user()->can('purchase_delete'), 403);
        //
    }
    // purchaseProducts list by Purchase id
    public function purchaseProducts(Request $request, $id)
    {
        $purchase = Purchase::with('items.product')->findOrFail($id);
        return view('backend.purchase.products', compact('id', 'purchase'));
    }
}
