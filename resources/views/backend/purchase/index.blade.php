@extends('backend.master')
@section('title', ' ')

@section('content')
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#d35400,#e67e22);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-shopping-bag"></i>
    </div>
    <div style="flex:1">
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Purchases</h2>
        <p style="margin:0;font-size:12px;color:#999">Manage all purchase transactions and stock intake</p>
    </div>
    @can('purchase_create')
    <a href="{{ route('backend.admin.purchase.create') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:linear-gradient(135deg,#d35400,#e67e22);color:#fff;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none">
        <i class="fas fa-plus-circle"></i> New Purchase
    </a>
    @endcan
</div>

<div class="filter-bar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:16px;padding:12px 16px;background:#fff;border:1px solid #e8e8e8;border-radius:12px">
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
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">All Purchases</h6>
    </div>
    <div style="padding:0 8px 8px">
        <table id="purchaseTable" class="table table-hover" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">#</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Purchase ID</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Supplier</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Total</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Date</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-center">Actions</th>
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

    var table = $('#purchaseTable').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        order: [[4, 'desc']],
        ajax: {
            url: "{{ route('backend.admin.purchase.index') }}",
            data: function (d) {
                d.from = $('#filterFrom').val();
                d.to   = $('#filterTo').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'id',          name: 'id' },
            { data: 'supplier',    name: 'supplier.name' },
            { data: 'total',       name: 'grand_total', className: 'text-right' },
            { data: 'created_at',  name: 'date' },
            { data: 'action',      name: 'action', orderable: false, searchable: false, className: 'text-center' },
        ]
    });

    $('#applyFilter').on('click', function () { table.ajax.reload(); });

    $('#resetFilter').on('click', function () {
        $('#filterFrom').val('');
        $('#filterTo').val('');
        table.ajax.reload();
    });

}());
</script>
@endpush
