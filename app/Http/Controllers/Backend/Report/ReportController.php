<?php

namespace App\Http\Controllers\Backend\Report;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ReportController extends Controller
{

    public function saleReport(Request $request)
    {

        abort_if(!auth()->user()->can(abilities: 'reports_sales'), 403);
        // Get user input or set default values
        $start_date_input = $request->input('start_date', Carbon::today()->subDays(29)->format('Y-m-d'));
        $end_date_input = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        // Parse and set start date
        $start_date = Carbon::createFromFormat('Y-m-d', $start_date_input) ?: Carbon::today()->subDays(29)->startOfDay();
        $start_date = $start_date->startOfDay();

        // Parse and set end date
        $end_date = Carbon::createFromFormat('Y-m-d', $end_date_input) ?: Carbon::today()->endOfDay();
        $end_date = $end_date->endOfDay();
        // Retrieve orders within the date range
        $orders = Order::whereBetween('created_at', [$start_date, $end_date])->with('customer')->get();

        // Calculate totals
        $data = [
            'orders' => $orders,
            'sub_total' => $orders->sum('sub_total'),
            'discount' => $orders->sum('discount'),
            'paid' => $orders->sum('paid'),
            'due' => $orders->sum('due'),
            'total' => $orders->sum('total'),
            'start_date' => $start_date->format('M d, Y'),
            'end_date' => $end_date->format('M d, Y'),
        ];

        return view('backend.reports.sale-report', $data);
    }
    public function saleSummery(Request $request)
    {

        abort_if(!auth()->user()->can('reports_summary'), 403);
        // Get user input or set default values
        $start_date_input = $request->input('start_date', Carbon::today()->subDays(29)->format('Y-m-d'));
        $end_date_input = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        // Parse and set start date
        $start_date = Carbon::createFromFormat('Y-m-d', $start_date_input) ?: Carbon::today()->subDays(29)->startOfDay();
        $start_date = $start_date->startOfDay();

        // Parse and set end date
        $end_date = Carbon::createFromFormat('Y-m-d', $end_date_input) ?: Carbon::today()->endOfDay();
        $end_date = $end_date->endOfDay();
        // Retrieve orders within the date range
        $orders = Order::whereBetween('created_at', [$start_date, $end_date])->get();

        // Calculate totals
        $data = [
            'sub_total' => $orders->sum('sub_total'),
            'discount' => $orders->sum('discount'),
            'paid' => $orders->sum('paid'),
            'due' => $orders->sum('due'),
            'total' => $orders->sum('total'),
            'start_date' => $start_date->format('M d, Y'),
            'end_date' => $end_date->format('M d, Y'),
        ];

        return view('backend.reports.sale-summery', $data);
    }
    function inventoryReport(Request $request)
    {
        abort_if(!auth()->user()->can('reports_inventory'), 403);

        if ($request->ajax()) {
            $products = Product::with('unit')->latest()->active()->get();
            return DataTables::of($products)
                ->addIndexColumn()
                ->addColumn('name', fn($data) => $data->name)
                ->addColumn('sku', fn($data) => $data->sku ?? '-')
                ->addColumn(
                    'price',
                    fn($data) => number_format($data->discounted_price, 2) .
                        ($data->price > $data->discounted_price
                            ? '<br><del>' . number_format($data->price, 2) . '</del>'
                            : '')
                )
                ->addColumn('quantity', fn($data) => $data->quantity . ' ' . optional($data->unit)->short_name)
                ->addColumn('stock_value', fn($data) => number_format($data->quantity * $data->discounted_price, 2))
                ->addColumn('action', fn($data) =>
                    '<button class="btn btn-warning btn-sm adjust-stock-btn"'
                    . ' data-id="' . $data->id . '"'
                    . ' data-name="' . htmlspecialchars($data->name) . '"'
                    . ' data-qty="' . $data->quantity . '">'
                    . '<i class="fas fa-edit"></i> Adjust</button>'
                )
                ->rawColumns(['price', 'quantity', 'stock_value', 'action'])
                ->toJson();
        }

        // Dashboard stats for the view
        $products = Product::with('unit')->active()->get();
        $totalCount = $products->sum('quantity');
        $totalValue = $products->sum(fn($p) => $p->quantity * $p->discounted_price);
        $productCount = $products->count();

        return view('backend.reports.inventory', compact('totalCount', 'totalValue', 'productCount'));
    }

    public function adjustStock(Request $request)
    {
        abort_if(!auth()->user()->can('reports_inventory'), 403);

        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'new_quantity' => 'required|numeric|min:0',
            'reason'       => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);
        $oldQty  = $product->quantity;
        $newQty  = (int) $request->new_quantity;

        $product->update(['quantity' => $newQty]);

        return response()->json([
            'message'      => "Stock for \"{$product->name}\" adjusted from {$oldQty} to {$newQty}.",
            'old_quantity' => $oldQty,
            'new_quantity' => $newQty,
        ]);
    }
}
