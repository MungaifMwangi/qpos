<?php

namespace App\Http\Controllers\Backend\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProfitLossController extends Controller
{
    /**
     * Display the Profit & Loss statement.
     */
    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('profit_loss_view'), 403);

        // Get date inputs or set defaults (last 30 days)
        $start_date_input = $request->input('start_date', Carbon::today()->subDays(29)->format('Y-m-d'));
        $end_date_input = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        // Parse dates
        $start_date = Carbon::parse($start_date_input)->startOfDay();
        $end_date = Carbon::parse($end_date_input)->endOfDay();

        // 1. Revenue Calculations
        $orders = Order::whereBetween('created_at', [$start_date, $end_date])->get();
        $gross_sales = $orders->sum('sub_total');
        $sales_discount = $orders->sum('discount');
        $net_sales = $orders->sum('total');

        // 2. Cost of Goods Sold (COGS)
        $cogs = OrderProduct::whereHas('order', function ($query) use ($start_date, $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        })->selectRaw('SUM(purchase_price * quantity) as total_cogs')->value('total_cogs') ?: 0;

        // 3. Gross Profit
        $gross_profit = $net_sales - $cogs;

        // 4. Other Business Expenses (grouped by category)
        $expenseCategories = ExpenseController::$categories;
        $categoryTotals = Expense::whereBetween('expense_date', [$start_date, $end_date])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $total_expenses = Expense::whereBetween('expense_date', [$start_date, $end_date])->sum('amount');

        // 5. Net Profit
        $net_profit = $gross_profit - $total_expenses;

        $data = [
            'start_date' => $start_date->format('M d, Y'),
            'end_date' => $end_date->format('M d, Y'),
            'start_date_raw' => $start_date_input,
            'end_date_raw' => $end_date_input,
            'gross_sales' => $gross_sales,
            'sales_discount' => $sales_discount,
            'net_sales' => $net_sales,
            'cogs' => $cogs,
            'gross_profit' => $gross_profit,
            'expenseCategories' => $expenseCategories,
            'categoryTotals' => $categoryTotals,
            'total_expenses' => $total_expenses,
            'net_profit' => $net_profit,
        ];

        return view('backend.accounting.profit-loss.index', $data);
    }
}
