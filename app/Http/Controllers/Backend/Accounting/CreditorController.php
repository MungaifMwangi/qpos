<?php

namespace App\Http\Controllers\Backend\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\CreditorService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Exception;

class CreditorController extends Controller
{
    protected CreditorService $creditorService;

    public function __construct(CreditorService $creditorService)
    {
        $this->creditorService = $creditorService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $aging = $this->creditorService->getAgingReport();
            // DataTables::of() with a plain PHP array uses client-side processing
            return DataTables::of(collect($aging))
                ->addIndexColumn()
                ->addColumn('supplier_name', fn($data) => $data['supplier_name'])
                ->addColumn('current', fn($data) => 'KES ' . number_format($data['current'], 2))
                ->addColumn('days_30', fn($data) => 'KES ' . number_format($data['days_30'], 2))
                ->addColumn('days_60', fn($data) => 'KES ' . number_format($data['days_60'], 2))
                ->addColumn('days_90_plus', fn($data) => 'KES ' . number_format($data['days_90_plus'], 2))
                ->addColumn('total_outstanding', fn($data) => '<strong>KES ' . number_format($data['total_outstanding'], 2) . '</strong>')
                ->rawColumns(['total_outstanding'])
                ->toJson();
        }

        $suppliers = Supplier::all();
        return view('backend.accounting.creditors.index', compact('suppliers'));
    }

    public function storePayment(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:1',
            'payment_mode' => 'required|in:cash,bank,mpesa',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->creditorService->recordPayment(
                $request->supplier_id,
                floatval($request->amount),
                $request->payment_mode,
                $request->notes
            );

            return back()->with('success', 'Supplier payment voucher recorded and posted to General Ledger.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
