@extends('backend.master')
@section('title', ' ')

@section('content')
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#d35400,#e67e22);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-receipt"></i>
    </div>
    <div>
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Expenses</h2>
        <p style="margin:0;font-size:12px;color:#999">Track and manage business expenses</p>
    </div>
    <div style="margin-left:auto">
        @can('expense_create')
        <a href="{{ route('backend.admin.expenses.create') }}" class="btn btn-sm" style="background:#2d2d2d;color:#fff;border-radius:8px;padding:6px 16px;font-weight:600">
            <i class="fas fa-plus-circle mr-1"></i> Add Expense
        </a>
        @endcan
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="border-radius:10px;margin-bottom:16px">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<div class="filter-bar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:16px;padding:12px 16px;background:#fff;border:1px solid #e8e8e8;border-radius:12px">
    <label style="font-size:13px;font-weight:600">Category:</label>
    <select id="filterCategory" class="form-control form-control-sm" style="width:200px;border-radius:8px">
        <option value="">All Categories</option>
        <option value="Rent">Rent</option>
        <option value="Utilities">Utilities</option>
        <option value="Salaries">Salaries & Wages</option>
        <option value="Marketing">Marketing & Advertising</option>
        <option value="Supplies">Office Supplies</option>
        <option value="Maintenance">Maintenance & Repairs</option>
        <option value="Other">Other / Miscellaneous</option>
    </select>

    <label style="font-size:13px;font-weight:600">Date From:</label>
    <input type="date" id="filterFrom" class="form-control form-control-sm" style="width:150px;border-radius:8px">

    <label style="font-size:13px;font-weight:600">Date To:</label>
    <input type="date" id="filterTo" class="form-control form-control-sm" style="width:150px;border-radius:8px">

    <button id="applyFilter" class="btn btn-sm" style="background:#2d2d2d;color:#fff;border-radius:8px;padding:6px 16px;font-weight:600">
        Apply
    </button>
    <button id="resetFilter" class="btn btn-sm" style="background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:8px;padding:6px 12px;font-weight:600">
        Reset
    </button>
</div>

<div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;overflow:hidden">
    <div style="padding:16px 20px 8px">
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">All Expenses</h6>
    </div>
    <div style="padding:0 8px 8px">
        <table id="datatables" class="table table-hover" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">#</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Title</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Category</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Amount</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Date</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Created By</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-center">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('script')
<script>
(function () {
    'use strict';

    var table = $('#datatables').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        order: [[4, 'desc']],
        ajax: {
            url: "{{ route('backend.admin.expenses.index') }}",
            data: function (d) {
                d.category = $('#filterCategory').val();
                d.from     = $('#filterFrom').val();
                d.to       = $('#filterTo').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'title',       name: 'title' },
            { data: 'category',    name: 'category' },
            { data: 'amount',      name: 'amount',       className: 'text-right' },
            { data: 'expense_date', name: 'expense_date' },
            { data: 'creator',     name: 'creator',      orderable: false },
            { data: 'action',      name: 'action',       orderable: false, searchable: false, className: 'text-center' }
        ]
    });

    $('#applyFilter').on('click', function () { table.ajax.reload(); });

    $('#resetFilter').on('click', function () {
        $('#filterCategory').val('');
        $('#filterFrom').val('');
        $('#filterTo').val('');
        table.ajax.reload();
    });
}());
</script>
@endpush
