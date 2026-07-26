<?php

namespace App\Http\Controllers\Backend\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ProfitLossController extends Controller
{
    protected static $expenseCategories = [
        'rent' => 'Rent',
        'utilities' => 'Utilities (Electricity, Water, Internet)',
        'salaries' => 'Salaries & Wages',
        'marketing' => 'Marketing & Advertising',
        'supplies' => 'Office Supplies',
        'maintenance' => 'Maintenance & Repairs',
        'other' => 'Other / Miscellaneous'
    ];

    /**
     * Show the Profit & Loss page (React mount point).
     */
    public function index()
    {
        abort_if(!auth()->user()->can('profit_loss_view'), 403);
        return view('backend.reports.profit-loss');
    }

    /**
     * API: Return P&L data as JSON for a given period.
     * GET /api/reports/profit-loss?period=this_month
     * GET /api/reports/profit-loss?from=2026-01-01&to=2026-01-31
     */
    public function apiData(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->can('profit_loss_view'), 403);

        [$start, $end] = $this->resolvePeriod($request);

        // --- Revenue by category ---
        $orderIds = Order::whereBetween('created_at', [$start, $end])->pluck('id');

        $revenueByCategory = OrderProduct::whereIn('order_id', $orderIds)
            ->join('products', 'order_products.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category, SUM(order_products.total) as revenue')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->pluck('revenue', 'category')
            ->toArray();

        $totalRevenue = array_sum($revenueByCategory);

        // --- COGS (cost side from order_products) ---
        $cogs = OrderProduct::whereIn('order_id', $orderIds)
            ->selectRaw('SUM(purchase_price * quantity) as total_cogs')
            ->value('total_cogs') ?: 0;

        // --- Operating Expenses by category ---
        $opexRaw = Expense::whereBetween('expense_date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        // Map keys to human-readable names
        $opexReadable = [];
        foreach ($opexRaw as $rawKey => $total) {
            $label = self::$expenseCategories[$rawKey] ?? ucfirst($rawKey);
            $opexReadable[$label] = $total;
        }

        $totalOpex = array_sum($opexReadable);

        $currencySymbol = currency()->symbol ?? 'KES';

        return response()->json([
            'period' => [
                'from' => $start->format('Y-m-d'),
                'to'   => $end->format('Y-m-d'),
                'label' => $start->format('M d, Y') . ' — ' . $end->format('M d, Y'),
            ],
            'currency' => $currencySymbol,
            'revenue' => [
                'total' => round($totalRevenue, 2),
                'byCategory' => array_map('round', $revenueByCategory, array_fill(0, count($revenueByCategory), 2)),
            ],
            'cogs' => round($cogs, 2),
            'opex' => [
                'total' => round($totalOpex, 2),
                'byCategory' => array_map('round', $opexReadable, array_fill(0, count($opexReadable), 2)),
            ],
        ]);
    }

    /**
     * Resolve start/end Carbon instances from the request.
     */
    protected function resolvePeriod(Request $request): array
    {
        $period = $request->input('period', 'this_month');

        if ($period === 'custom' && $request->filled('from') && $request->filled('to')) {
            return [
                Carbon::parse($request->input('from'))->startOfDay(),
                Carbon::parse($request->input('to'))->endOfDay(),
            ];
        }

        $now = Carbon::now();

        return match ($period) {
            'today'        => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'yesterday'    => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'this_week'    => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'last_week'    => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'this_month'   => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month'   => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'last_quarter' => [$now->copy()->subQuarter()->startOfQuarter(), $now->copy()->subQuarter()->endOfQuarter()],
            'ytd'          => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'this_year'    => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'last_year'    => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            default        => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }
}
