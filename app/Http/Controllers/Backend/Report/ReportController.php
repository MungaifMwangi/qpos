<?php

namespace App\Http\Controllers\Backend\Report;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
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

        // Sales by category (for donut chart)
        $categoryData = \App\Models\OrderProduct::whereHas('order', function ($q) use ($start_date, $end_date) {
            $q->whereBetween('created_at', [$start_date, $end_date]);
        })
            ->join('products', 'order_products.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category, SUM(order_products.total) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        $categoryLabels = $categoryData->pluck('category')->toArray();
        $categoryTotals = $categoryData->pluck('total')->map(fn($v) => round($v, 2))->toArray();

        // Daily sales data for the line chart
        $dailySales = Order::whereBetween('created_at', [$start_date, $end_date])
            ->selectRaw('DATE(created_at) as date, SUM(total) as daily_total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartLabels = $dailySales->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray();
        $chartData = $dailySales->pluck('daily_total')->map(fn($v) => round($v, 2))->toArray();

        // Calculate totals
        $data = [
            'sub_total' => $orders->sum('sub_total'),
            'discount' => $orders->sum('discount'),
            'paid' => $orders->sum('paid'),
            'due' => $orders->sum('due'),
            'total' => $orders->sum('total'),
            'order_count' => $orders->count(),
            'avg_order' => $orders->count() > 0 ? round($orders->sum('total') / $orders->count(), 2) : 0,
            'start_date' => $start_date->format('M d, Y'),
            'end_date' => $end_date->format('M d, Y'),
            'start_date_raw' => $start_date->format('Y-m-d'),
            'end_date_raw' => $end_date->format('Y-m-d'),
            'categoryLabels' => $categoryLabels,
            'categoryTotals' => $categoryTotals,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
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
                ->addColumn('stock_value', fn($data) => number_format($data->quantity * $data->purchase_price, 2))
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
        $totalValue = $products->sum(fn($p) => $p->quantity * $p->purchase_price);
        $productCount = $products->count();

        return view('backend.reports.inventory', compact('totalCount', 'totalValue', 'productCount'));
    }

    public function inventoryOverview()
    {
        abort_if(!auth()->user()->can('reports_inventory'), 403);

        $products = Product::with('category')->active()->get();

        $totalProducts = $products->count();
        $totalQuantity = $products->sum('quantity');
        $inventoryValue = $products->sum(fn($p) => $p->quantity * $p->purchase_price);
        $outOfStock = $products->where('quantity', 0)->count();
        $lowStock = $products->filter(fn($p) => $p->quantity > 0 && $p->quantity <= 10)->count();
        $inStock = $products->filter(fn($p) => $p->quantity > 10)->count();

        $categoryData = $products->groupBy(fn($p) => $p->category->name ?? 'Uncategorized')
            ->map(function ($items, $name) {
                return [
                    'name' => $name,
                    'items' => $items->count(),
                    'qty' => $items->sum('quantity'),
                    'value' => $items->sum(fn($p) => $p->quantity * $p->purchase_price),
                ];
            })
            ->sortByDesc('value')
            ->values();

        $topProducts = $products->sortByDesc('quantity')->values();

        // Daily sales by category for last 7 days (line chart)
        $now = now();
        $start7 = $now->copy()->subDays(6)->startOfDay();
        $dailyRows = \DB::table('order_products')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->join('products', 'products.id', '=', 'order_products.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.created_at', '>=', $start7)
            ->selectRaw('DATE(orders.created_at) as day, COALESCE(categories.name, ?) as cat, SUM(order_products.total) as total', ['Uncategorized'])
            ->groupBy('day', 'cat')
            ->get();

        $days = collect();
        for ($d = $start7; $d->lte($now); $d->addDay()) {
            $days->push($d->format('Y-m-d'));
        }

        // Get top categories by total sales in this period
        $catTotals = $dailyRows->groupBy('cat')
            ->map(fn($rows) => $rows->sum('total'))
            ->sortDesc()
            ->keys()
            ->take(6);

        $chartLabels = $days->map(fn($d) => \Carbon\Carbon::parse($d)->format('D d'))->toArray();

        $chartDatasets = $catTotals->map(function ($cat) use ($dailyRows, $days) {
            $byDay = $dailyRows->where('cat', $cat)->keyBy('day');
            $data = $days->map(fn($d) => round($byDay->get($d, (object)['total' => 0])->total ?? 0, 2))->toArray();
            return ['label' => $cat, 'data' => $data];
        })->toArray();

        $currencySymbol = 'KES';

        return view('backend.reports.inventory-overview', compact(
            'totalProducts', 'totalQuantity', 'inventoryValue', 'outOfStock',
            'lowStock', 'inStock', 'categoryData', 'currencySymbol', 'topProducts',
            'chartLabels', 'chartDatasets'
        ));
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
