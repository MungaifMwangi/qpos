@extends('backend.master')
@section('title', ' ')

@section('content')
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#2980b9,#3498db);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-book"></i>
    </div>
    <div>
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Chart of Accounts</h2>
        <p style="margin:0;font-size:12px;color:#999">All general ledger accounts and their current balances</p>
    </div>
</div>

<div class="filter-bar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:16px;padding:12px 16px;background:#fff;border:1px solid #e8e8e8;border-radius:12px">
    <label style="font-size:13px;font-weight:600">Account Type:</label>
    <select id="filterType" class="form-control form-control-sm" style="width:180px;border-radius:8px">
        <option value="">All Types</option>
        <option value="asset">Asset</option>
        <option value="liability">Liability</option>
        <option value="equity">Equity</option>
        <option value="revenue">Revenue</option>
        <option value="expense">Expense</option>
    </select>

    <button id="applyFilter" class="btn btn-sm" style="background:#2d2d2d;color:#fff;border-radius:8px;padding:6px 16px;font-weight:600">
        Apply
    </button>
    <button id="resetFilter" class="btn btn-sm" style="background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:8px;padding:6px 12px;font-weight:600">
        Reset
    </button>
</div>

<div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;overflow:hidden">
    <div style="padding:16px 20px 8px">
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">All Accounts</h6>
    </div>
    <div style="padding:0 8px 8px">
        <table id="coaTable" class="table table-hover" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Code</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Account Name</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Type</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Normal Balance</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Current Balance</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $account)
                <tr data-type="{{ $account->type }}">
                    <td><strong>{{ $account->code }}</strong></td>
                    <td>{{ $account->name }}</td>
                    <td><span class="badge bg-info">{{ strtoupper($account->type) }}</span></td>
                    <td><span class="badge bg-secondary">{{ strtoupper($account->normal_balance) }}</span></td>
                    <td class="text-right">
                        <strong class="{{ $account->balance < 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($account->balance, 2) }}
                        </strong>
                    </td>
                    <td><small class="text-muted">{{ $account->description }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('script')
<script>
(function () {
    'use strict';

    var table = $('#coaTable').DataTable({
        ordering: true,
        order: [[0, 'asc']],
        pageLength: 25,
        language: { search: '', searchPlaceholder: 'Search accounts…' }
    });

    $('#applyFilter').on('click', function () {
        var val = $('#filterType').val();
        table.column(2).search(val).draw();
    });

    $('#resetFilter').on('click', function () {
        $('#filterType').val('');
        table.column(2).search('').draw();
    });
}());
</script>
@endpush
