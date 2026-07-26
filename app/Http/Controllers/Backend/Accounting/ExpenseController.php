<?php

namespace App\Http\Controllers\Backend\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public static $categories = [
        'rent' => 'Rent',
        'utilities' => 'Utilities (Electricity, Water, Internet)',
        'salaries' => 'Salaries & Wages',
        'marketing' => 'Marketing & Advertising',
        'supplies' => 'Office Supplies',
        'maintenance' => 'Maintenance & Repairs',
        'other' => 'Other / Miscellaneous'
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('expense_view'), 403);

        if ($request->ajax()) {
            $query = Expense::with('user');

            if ($request->filled('category')) {
                $categoryKey = array_search($request->category, self::$categories);
                if ($categoryKey !== false) {
                    $query->where('category', $categoryKey);
                }
            }

            if ($request->filled('from')) {
                $query->whereDate('expense_date', '>=', $request->from);
            }

            if ($request->filled('to')) {
                $query->whereDate('expense_date', '<=', $request->to);
            }

            $expenses = $query->latest('expense_date');

            return DataTables::of($expenses)
                ->addIndexColumn()
                ->addColumn('title', fn($data) => $data->title)
                ->addColumn('category', fn($data) => self::$categories[$data->category] ?? ucfirst($data->category))
                ->addColumn('amount', fn($data) => number_format($data->amount, 2, '.', ','))
                ->addColumn('expense_date', fn($data) => Carbon::parse($data->expense_date)->format('d M, Y'))
                ->addColumn('creator', fn($data) => $data->user->name ?? '-')
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group">
                    <button type="button" class="btn bg-gradient-primary btn-flat">Action</button>
                    <button type="button" class="btn bg-gradient-primary btn-flat dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false">
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" role="menu">
                      <a class="dropdown-item" href="' . route('backend.admin.expenses.edit', $data->id) . '">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                      <div class="dropdown-divider"></div>
                      <form action="' . route('backend.admin.expenses.destroy', $data->id) . '" method="POST" style="display:inline;">
                        ' . csrf_field() . '
                        ' . method_field("DELETE") . '
                        <button type="submit" class="dropdown-item" onclick="return confirm(\'Are you sure?\')">
                          <i class="fas fa-trash"></i> Delete
                        </button>
                      </form>
                    </div>
                  </div>';
                })
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('backend.accounting.expenses.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(!auth()->user()->can('expense_create'), 403);
        $categories = self::$categories;
        return view('backend.accounting.expenses.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->can('expense_create'), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(self::$categories)),
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        Expense::create($validated);

        return redirect()->route('backend.admin.expenses.index')->with('success', 'Expense created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        abort_if(!auth()->user()->can('expense_update'), 403);
        $expense = Expense::findOrFail($id);
        $categories = self::$categories;
        return view('backend.accounting.expenses.edit', compact('expense', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        abort_if(!auth()->user()->can('expense_update'), 403);

        $expense = Expense::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(self::$categories)),
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $expense->update($validated);

        return redirect()->route('backend.admin.expenses.index')->with('success', 'Expense updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        abort_if(!auth()->user()->can('expense_delete'), 403);

        $expense = Expense::findOrFail($id);
        $expense->delete();

        return redirect()->route('backend.admin.expenses.index')->with('success', 'Expense deleted successfully!');
    }
}
