@extends('backend.master')
@section('title', ' ')

@section('content')
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#1a7a4e,#28a745);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-file-invoice"></i>
    </div>
    <div>
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Sales Report</h2>
        <p style="margin:0;font-size:12px;color:#999">Detailed view of all sales transactions with filters</p>
    </div>
</div>

<div class="kpi-row" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 18px;text-align:center">
        <div style="font-size:22px;font-weight:800;color:#1a7a4e">{{ number_format($totalSales) }}</div>
        <div style="font-size:11px;font-weight:600;color:#999;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px">Total Sales</div>
    </div>
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 18px;text-align:center">
        <div style="font-size:22px;font-weight:800;color:#2d6a4f">KES {{ number_format($totalRevenue, 2) }}</div>
        <div style="font-size:11px;font-weight:600;color:#999;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px">Total Revenue</div>
    </div>
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 18px;text-align:center">
        <div style="font-size:22px;font-weight:800;color:#52796f">KES {{ number_format($totalPaid, 2) }}</div>
        <div style="font-size:11px;font-weight:600;color:#999;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px">Total Paid</div>
    </div>
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 18px;text-align:center">
        <div style="font-size:22px;font-weight:800;color:#b07d62">KES {{ number_format($totalDue, 2) }}</div>
        <div style="font-size:11px;font-weight:600;color:#999;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px">Total Due</div>
    </div>
</div>

<div class="filter-bar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:16px;padding:12px 16px;background:#fff;border:1px solid #e8e8e8;border-radius:12px">
    <label style="font-size:13px;font-weight:600">Status:</label>
    <select id="filterStatus" class="form-control form-control-sm" style="width:160px;border-radius:8px">
        <option value="">All</option>
        <option value="paid">Paid</option>
        <option value="pending">Pending</option>
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
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">All Sales</h6>
    </div>
    <div style="padding:0 8px 8px">
        <table id="salesReportTable" class="table table-hover" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">#</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Sale ID</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Customer</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Items</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Date</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Sub Total</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Discount</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Total</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Paid</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Due</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">M-Pesa Code</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Status</th>
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

    var table = $('#salesReportTable').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        order: [[4, 'desc']],
        ajax: {
            url: "{{ route('backend.admin.sale.report') }}",
            data: function (d) {
                d.status = $('#filterStatus').val();
                d.from   = $('#filterFrom').val();
                d.to     = $('#filterTo').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'saleId',      name: 'saleId' },
            { data: 'customer',    name: 'customer.name' },
            { data: 'item',        name: 'item', orderable: false, searchable: false },
            { data: 'date',        name: 'created_at' },
            { data: 'sub_total',   name: 'sub_total', className: 'text-right' },
            { data: 'discount',    name: 'discount',  className: 'text-right' },
            { data: 'total',       name: 'total',     className: 'text-right' },
            { data: 'paid',        name: 'paid',      className: 'text-right' },
            { data: 'due',         name: 'due',       className: 'text-right' },
            { data: 'mpesa_code',  name: 'mpesa_code' },
            { data: 'status',      name: 'status',    orderable: false, searchable: false },
        ]
    });

    $('#applyFilter').on('click', function () { table.ajax.reload(); });

    $('#resetFilter').on('click', function () {
        $('#filterStatus').val('');
        $('#filterFrom').val('');
        $('#filterTo').val('');
        table.ajax.reload();
    });

}());
</script>
@endpush
