<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('payment_status', '!=', 'voided')->get();

        $totalRevenue = $orders->sum('total');
        $totalSales = $orders->count();
        $totalCustomers = Customer::count();
        $lowStockCount = Product::where('status', 1)->where('quantity', '<=', 10)->count();

        $startOfMonth = Carbon::now()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();

        $thisMonthRevenue = Order::where('payment_status', '!=', 'voided')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total');
        $lastMonthRevenue = Order::where('payment_status', '!=', 'voided')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('total');

        $thisMonthSales = Order::where('payment_status', '!=', 'voided')
            ->where('created_at', '>=', $startOfMonth)
            ->count();
        $lastMonthSales = Order::where('payment_status', '!=', 'voided')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $thisMonthCustomers = Customer::where('created_at', '>=', $startOfMonth)->count();

        $revenueChange = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;
        $salesChange = $lastMonthSales > 0
            ? round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1)
            : 0;

        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $last7Days->push([
                'day' => $day->format('D'),
                'date' => $day->format('Y-m-d'),
            ]);
        }

        $dailySales = Order::where('payment_status', '!=', 'voided')
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(total) as daily_total')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = $last7Days->pluck('day')->toArray();
        $chartData = $last7Days->map(function ($d) use ($dailySales) {
            return $dailySales->get($d['date'], (object) ['daily_total' => 0])->daily_total ?? 0;
        })->toArray();

        $recentSales = Order::with('customer')
            ->where('payment_status', '!=', 'voided')
            ->latest()
            ->take(5)
            ->get();

        // Sales by category (last 7 days) for donut chart
        $categoryData = OrderProduct::whereHas('order', function ($q) {
                $q->where('payment_status', '!=', 'voided')
                  ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay());
            })
            ->join('products', 'order_products.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category, SUM(order_products.total) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        $categoryLabels = $categoryData->pluck('category')->toArray();
        $categoryTotals = $categoryData->pluck('total')->map(fn($v) => round($v, 2))->toArray();

        $topProducts = OrderProduct::select(
                'product_id',
                DB::raw('SUM(order_products.quantity) as total_qty'),
                DB::raw('SUM(order_products.total) as total_revenue')
            )
            ->join('products', 'order_products.product_id', '=', 'products.id')
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->where('orders.payment_status', '!=', 'voided')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $product = Product::find($item->product_id);
                return [
                    'name' => $product->name ?? 'Deleted Product',
                    'quantity_sold' => number_format($item->total_qty, 2),
                    'revenue' => number_format($item->total_revenue, 2),
                ];
            });

        $data = [
            'totalRevenue' => number_format($totalRevenue, 2, '.', ','),
            'totalSales' => $totalSales,
            'totalCustomers' => $totalCustomers,
            'lowStockCount' => $lowStockCount,
            'revenueChange' => $revenueChange,
            'salesChange' => $salesChange,
            'newCustomersThisMonth' => $thisMonthCustomers,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'recentSales' => $recentSales,
            'topProducts' => $topProducts,
            'categoryLabels' => $categoryLabels,
            'categoryTotals' => $categoryTotals,
        ];

        return view('backend.index', $data);
    }

    public function profile()
    {
        $user = auth()->user();
        return view('backend.profile.index', compact('user'));
    }
}
