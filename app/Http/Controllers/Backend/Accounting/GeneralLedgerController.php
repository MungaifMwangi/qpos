<?php

namespace App\Http\Controllers\Backend\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class GeneralLedgerController extends Controller
{
    public function chartOfAccounts()
    {
        abort_if(!auth()->user()->can('chart_of_accounts_view'), 403);

        $accounts = ChartOfAccount::orderBy('code', 'asc')->get();
        return view('backend.accounting.ledger.chart', compact('accounts'));
    }

    public function journalEntries(Request $request)
    {
        abort_if(!auth()->user()->can('general_ledger_view'), 403);

        if ($request->ajax()) {
            $entries = JournalEntry::with(['lines.account', 'user'])->orderBy('id', 'desc')->get();
            return DataTables::of($entries)
                ->addIndexColumn()
                ->addColumn('entry_number', fn($data) => '<strong>' . $data->entry_number . '</strong>')
                ->addColumn('date', fn($data) => $data->entry_date)
                ->addColumn('narration', fn($data) => $data->narration)
                ->addColumn('posted_by', fn($data) => optional($data->user)->name ?? 'System')
                ->addColumn('status', fn($data) => '<span class="badge bg-' . ($data->status === 'posted' ? 'success' : ($data->status === 'reversed' ? 'warning' : 'danger')) . '">' . ucfirst($data->status) . '</span>')
                ->addColumn('action', function ($data) {
                    return '<button class="btn btn-info btn-sm view-lines-btn" data-id="' . $data->id . '"><i class="fas fa-list"></i> View Lines</button>';
                })
                ->rawColumns(['entry_number', 'status', 'action'])
                ->toJson();
        }

        return view('backend.accounting.ledger.entries');
    }

    /**
     * Return the debit/credit lines for a single journal entry as JSON
     * (used by the "View Lines" modal in the entries DataTable).
     */
    public function entryLines(int $id)
    {
        abort_if(!auth()->user()->can('general_ledger_view'), 403);

        $entry = JournalEntry::with(['lines.account'])->findOrFail($id);

        $lines = $entry->lines->map(fn($line) => [
            'account_code' => optional($line->account)->code ?? '-',
            'account_name' => optional($line->account)->name ?? 'Unknown',
            'debit'        => $line->debit,
            'credit'       => $line->credit,
            'memo'         => $line->memo,
        ]);

        return response()->json($lines);
    }

    public function trialBalance()
    {
        abort_if(!auth()->user()->can('trial_balance_view'), 403);

        $accounts = ChartOfAccount::with('journalLines')->orderBy('code', 'asc')->get();
        $totalDebit = 0.00;
        $totalCredit = 0.00;

        $report = [];
        foreach ($accounts as $acc) {
            $debits = $acc->journalLines->sum('debit');
            $credits = $acc->journalLines->sum('credit');

            $netDebit = 0.00;
            $netCredit = 0.00;

            if ($debits >= $credits) {
                $netDebit = $debits - $credits;
            } else {
                $netCredit = $credits - $debits;
            }

            $totalDebit += $netDebit;
            $totalCredit += $netCredit;

            $report[] = [
                'code' => $acc->code,
                'name' => $acc->name,
                'type' => ucfirst($acc->type),
                'debit' => $netDebit,
                'credit' => $netCredit,
            ];
        }

        return view('backend.accounting.ledger.trial-balance', compact('report', 'totalDebit', 'totalCredit'));
    }
}
