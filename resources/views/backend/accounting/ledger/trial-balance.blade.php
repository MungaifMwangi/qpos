@extends('backend.master')
@section('title', ' ')

@section('content')
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#8e44ad,#9b59b6);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-balance-scale"></i>
    </div>
    <div>
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Trial Balance</h2>
        <p style="margin:0;font-size:12px;color:#999">Summary of all ledger account balances</p>
    </div>
    <div style="margin-left:auto">
        <button class="btn btn-sm" onclick="window.print()" style="background:#2d2d2d;color:#fff;border-radius:8px;padding:6px 16px;font-weight:600">
            <i class="fas fa-print mr-1"></i> Print
        </button>
    </div>
</div>

<div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;overflow:hidden">
    <div style="padding:16px 20px 8px">
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">Account Balances</h6>
    </div>
    <div style="padding:0 8px 8px">
        <table class="table table-hover" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Code</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Account Name</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Type</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Debit</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report as $row)
                @if($row['debit'] > 0 || $row['credit'] > 0)
                <tr>
                    <td><strong>{{ $row['code'] }}</strong></td>
                    <td>{{ $row['name'] }}</td>
                    <td><span class="badge bg-secondary">{{ $row['type'] }}</span></td>
                    <td class="text-right text-success">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '-' }}</td>
                    <td class="text-right text-danger">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '-' }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f5f5f5">
                    <td colspan="3" class="text-right"><strong>TOTALS</strong></td>
                    <td class="text-right text-success"><strong>{{ number_format($totalDebit, 2) }}</strong></td>
                    <td class="text-right text-danger"><strong>{{ number_format($totalCredit, 2) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-center py-3">
                        @if(abs($totalDebit - $totalCredit) < 0.01)
                            <span class="badge badge-success p-2" style="font-size:13px">
                                <i class="fas fa-check-circle mr-1"></i> Trial Balance is Balanced (Debits = Credits)
                            </span>
                        @else
                            <span class="badge badge-danger p-2" style="font-size:13px">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Unbalanced — Difference: {{ number_format(abs($totalDebit - $totalCredit), 2) }}
                            </span>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
