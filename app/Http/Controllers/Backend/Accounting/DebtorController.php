<?php

namespace App\Http\Controllers\Backend\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\DebtorService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Exception;

class DebtorController extends Controller
{
    protected DebtorService $debtorService;

    public function __construct(DebtorService $debtorService)
    {
        $this->debtorService = $debtorService;
    }

    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('debtors_view'), 403);

        if ($request->ajax()) {
            $aging = $this->debtorService->getAgingReport();
            // DataTables::of() with a plain PHP array uses client-side processing
            return DataTables::of(collect($aging))
                ->addIndexColumn()
                ->addColumn('customer_name', fn($data) => $data['customer_name'])
                ->addColumn('current', fn($data) => 'KES ' . number_format($data['current'], 2))
                ->addColumn('days_30', fn($data) => 'KES ' . number_format($data['days_30'], 2))
                ->addColumn('days_60', fn($data) => 'KES ' . number_format($data['days_60'], 2))
                ->addColumn('days_90_plus', fn($data) => 'KES ' . number_format($data['days_90_plus'], 2))
                ->addColumn('total_outstanding', fn($data) => '<strong>KES ' . number_format($data['total_outstanding'], 2) . '</strong>')
                ->addColumn('action', function ($data) {
                    return '<a href="' . route('backend.admin.debtors.statement', $data['customer_id']) . '" class="btn btn-info btn-sm"><i class="fas fa-file-alt"></i> Statement</a>';
                })
                ->rawColumns(['total_outstanding', 'action'])
                ->toJson();
        }

        $customers = Customer::all();
        return view('backend.accounting.debtors.index', compact('customers'));
    }

    public function statement(Request $request, int $customerId)
    {
        abort_if(!auth()->user()->can('debtors_view'), 403);

        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $statement = $this->debtorService->getCustomerStatement($customerId, $fromDate, $toDate);

        return view('backend.accounting.debtors.statement', compact('statement', 'fromDate', 'toDate'));
    }

    public function storeReceipt(Request $request)
    {
        abort_if(!auth()->user()->can('debtors_receipt_create'), 403);

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'payment_mode' => 'required|in:cash,bank,mpesa',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->debtorService->recordReceipt(
                $request->customer_id,
                floatval($request->amount),
                $request->payment_mode,
                $request->notes
            );

            return back()->with('success', 'Customer payment receipt recorded and posted to General Ledger.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
