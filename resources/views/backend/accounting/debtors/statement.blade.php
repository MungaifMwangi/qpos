@extends('backend.master')
@section('title', ' ')

@section('content')
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-file-invoice-dollar"></i>
    </div>
    <div style="flex:1">
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Customer Statement</h2>
        <p style="margin:0;font-size:12px;color:#999">Account statement for <strong>{{ $statement['customer']->name }}</strong></p>
    </div>
    <a href="{{ route('backend.admin.debtors.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none">
        <i class="fas fa-arrow-left"></i> Back to Debtors
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 20px">
        <h6 style="margin:0 0 10px;font-size:12px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:0.5px">Customer Info</h6>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:13px">
            <div>
                <span style="color:#999;font-weight:600">Name</span><br>
                <span style="font-weight:700;color:#303030">{{ $statement['customer']->name }}</span>
            </div>
            <div>
                <span style="color:#999;font-weight:600">Phone</span><br>
                <span style="font-weight:700;color:#303030">{{ $statement['customer']->phone ?? 'N/A' }}</span>
            </div>
        </div>
    </div>
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 20px;display:flex;flex-direction:column;justify-content:center;align-items:center">
        <h6 style="margin:0 0 6px;font-size:12px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:0.5px">Current Balance Due</h6>
        <div style="font-size:26px;font-weight:800;{{ $currentBalance > 0 ? 'color:#c0392b' : 'color:#27ae60' }}">
            KES {{ number_format($currentBalance, 2) }}
        </div>
    </div>
</div>

<div class="kpi-row" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 18px;text-align:center">
        <div style="font-size:22px;font-weight:800;color:#2980b9">KES {{ number_format($totalDebits, 2) }}</div>
        <div style="font-size:11px;font-weight:600;color:#999;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px">Total Invoices</div>
    </div>
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 18px;text-align:center">
        <div style="font-size:22px;font-weight:800;color:#27ae60">KES {{ number_format($totalCredits, 2) }}</div>
        <div style="font-size:11px;font-weight:600;color:#999;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px">Total Payments</div>
    </div>
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 18px;text-align:center">
        <div style="font-size:22px;font-weight:800;{{ $currentBalance > 0 ? 'color:#c0392b' : 'color:#27ae60' }}">KES {{ number_format($currentBalance, 2) }}</div>
        <div style="font-size:11px;font-weight:600;color:#999;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px">Balance Due</div>
    </div>
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 18px;text-align:center">
        <div style="font-size:22px;font-weight:800;color:#8e44ad">KES {{ number_format($creditLimit, 2) }}</div>
        <div style="font-size:11px;font-weight:600;color:#999;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px">Credit Limit</div>
    </div>
</div>

<form method="GET" action="{{ route('backend.admin.debtors.statement', $statement['customer']->id) }}">
    <div class="filter-bar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:16px;padding:12px 16px;background:#fff;border:1px solid #e8e8e8;border-radius:12px">
        <label style="font-size:13px;font-weight:600">Date From:</label>
        <input type="date" name="from_date" value="{{ $fromDate ?? '' }}" class="form-control form-control-sm" style="width:150px;border-radius:8px">

        <label style="font-size:13px;font-weight:600">Date To:</label>
        <input type="date" name="to_date" value="{{ $toDate ?? '' }}" class="form-control form-control-sm" style="width:150px;border-radius:8px">

        <button type="submit" class="btn btn-sm" style="background:#2d2d2d;color:#fff;border-radius:8px;padding:6px 16px;font-weight:600">
            Apply
        </button>
        <a href="{{ route('backend.admin.debtors.statement', $statement['customer']->id) }}" class="btn btn-sm" style="background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:8px;padding:6px 12px;font-weight:600;text-decoration:none">
            Reset
        </a>
    </div>
</form>

<div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;overflow:hidden">
    <div style="padding:16px 20px 8px;display:flex;justify-content:space-between;align-items:center">
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">Transaction History</h6>
        <button type="button" onclick="window.print()" class="btn btn-sm" style="background:#2d2d2d;color:#fff;border-radius:8px;padding:6px 14px;font-weight:600">
            <i class="fas fa-print mr-1"></i> Print
        </button>
    </div>
    <div style="padding:0 8px 8px">
        <table id="statementTable" class="table table-hover" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">#</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Date</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Reference</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Type</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Notes</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Debit</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Credit</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($statement['transactions'] as $index => $tx)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($tx['date'])->format('d M, Y') }}</td>
                    <td><strong>{{ $tx['reference'] }}</strong></td>
                    <td>
                        @if($tx['type'] === 'Invoice')
                            <span style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;background:#d4e6f1;color:#1a5276">Invoice</span>
                        @else
                            <span style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;background:#d4edda;color:#155724">Receipt</span>
                        @endif
                    </td>
                    <td>{{ $tx['notes'] ?? '-' }}</td>
                    <td class="text-right" style="font-weight:600">{{ $tx['debit'] > 0 ? number_format($tx['debit'], 2, '.', ',') : '-' }}</td>
                    <td class="text-right" style="font-weight:600">{{ $tx['credit'] > 0 ? number_format($tx['credit'], 2, '.', ',') : '-' }}</td>
                    <td class="text-right" style="font-weight:700;{{ $tx['balance'] > 0 ? 'color:#c0392b' : 'color:#27ae60' }}">KES {{ number_format($tx['balance'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding:24px;color:#999">
                        <i class="fas fa-inbox" style="font-size:24px;margin-bottom:8px;display:block"></i>
                        No transactions recorded for this customer.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('script')
<script>
$(function () {
    $('#statementTable').DataTable({
        ordering: true,
        order: [[1, 'asc']],
        pageLength: 25,
        language: {
            emptyTable: "No transactions recorded",
            search: "Search transactions:"
        }
    });
});
</script>
@endpush
