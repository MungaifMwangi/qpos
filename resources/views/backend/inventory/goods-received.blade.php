@extends('backend.master')
@section('title', ' ')

@section('content')
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#5b5ea6,#9b59b6);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-truck-loading"></i>
    </div>
    <div>
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Goods Received Notes</h2>
        <p style="margin:0;font-size:12px;color:#999">Track all received stock — from LPO deliveries and direct purchases</p>
    </div>
</div>

<div class="filter-bar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:16px;padding:12px 16px;background:#fff;border:1px solid #e8e8e8;border-radius:12px">
    <label style="font-size:13px;font-weight:600">Source:</label>
    <select id="filterSource" class="form-control form-control-sm" style="width:160px;border-radius:8px">
        <option value="">All</option>
        <option value="lpo">LPO Delivery</option>
        <option value="direct">Direct Purchase</option>
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
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">All Goods Received Notes</h6>
    </div>
    <div style="padding:0 8px 8px">
        <table id="grnTable" class="table table-hover" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">#</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">GRN Number</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Source</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Supplier</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Date</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Items</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Received By</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Total Value</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-center">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="grnDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:14px;overflow:hidden">
            <div class="modal-header" style="background:linear-gradient(135deg,#5b5ea6,#9b59b6);color:#fff;padding:14px 20px">
                <h5 class="modal-title" style="font-size:15px;font-weight:700">
                    <i class="fas fa-truck-loading mr-1"></i>
                    <span id="grnModalTitle">GRN Details</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity:0.8"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="padding:20px">
                <div id="grnModalBody" style="min-height:100px">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="mt-2 text-muted" style="font-size:12px">Loading GRN details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding:12px 20px;border-top:1px solid #f0f0f0">
                <button type="button" class="btn btn-sm" style="background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:8px" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-sm" id="grnModalPrintBtn" style="background:#2d2d2d;color:#fff;border-radius:8px">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
(function () {
    'use strict';

    var currentGrnId = null;

    var table = $('#grnTable').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        order: [[4, 'desc']],
        ajax: {
            url: "{{ route('backend.admin.inventory.goods-received.index') }}",
            data: function (d) {
                d.source = $('#filterSource').val();
                d.from   = $('#filterFrom').val();
                d.to     = $('#filterTo').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',    name: 'DT_RowIndex',    orderable: false, searchable: false },
            { data: 'grn_number',     name: 'grn_number' },
            { data: 'source',         name: 'source',         orderable: false, searchable: false },
            { data: 'supplier_name',  name: 'supplier.name' },
            { data: 'received_date',  name: 'received_date' },
            { data: 'items_count',    name: 'items_count',    orderable: false, searchable: false },
            { data: 'received_by_name', name: 'received_by_name', orderable: false, searchable: false },
            { data: 'total_value',    name: 'total_value',    className: 'text-right' },
            { data: 'action',         name: 'action',         orderable: false, searchable: false, className: 'text-center' },
        ]
    });

    $('#applyFilter').on('click', function () { table.ajax.reload(); });

    $('#resetFilter').on('click', function () {
        $('#filterSource').val('');
        $('#filterFrom').val('');
        $('#filterTo').val('');
        table.ajax.reload();
    });

    $(document).on('click', '.view-grn-btn', function () {
        currentGrnId = $(this).data('url');
        $('#grnModalBody').html(
            '<div class="text-center py-4">' +
            '<i class="fas fa-spinner fa-spin fa-2x text-muted"></i>' +
            '<p class="mt-2 text-muted" style="font-size:12px">Loading GRN details...</p>' +
            '</div>'
        );
        $('#grnDetailModal').modal('show');

        $.ajax({
            url: currentGrnId,
            type: 'GET',
            success: function (grn) {
                var source = grn.lpo ? 'LPO: ' + grn.lpo.lpo_number : (grn.purchase ? 'Direct Purchase #' + grn.purchase.id : '—');
                var supplier = grn.supplier ? grn.supplier.name : '—';
                var date = new Date(grn.received_date).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });

                var html = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">';
                html += '<div style="font-size:12px"><strong style="color:#999">GRN Number</strong><br><span style="font-weight:700;color:#303030">' + grn.grn_number + '</span></div>';
                html += '<div style="font-size:12px"><strong style="color:#999">Source</strong><br>' + source + '</div>';
                html += '<div style="font-size:12px"><strong style="color:#999">Supplier</strong><br>' + supplier + '</div>';
                html += '<div style="font-size:12px"><strong style="color:#999">Received Date</strong><br>' + date + '</div>';
                html += '</div>';

                if (grn.notes) {
                    html += '<div style="font-size:12px;color:#666;margin-bottom:14px;padding:8px 12px;background:#f9f9f9;border-radius:8px">' +
                        '<strong style="color:#999">Notes:</strong> ' + grn.notes + '</div>';
                }

                html += '<table class="table table-sm" style="border:1px solid #e8e8e8;border-radius:8px;overflow:hidden;font-size:12px">';
                html += '<thead><tr style="background:#f5f5f5">';
                html += '<th style="padding:8px 12px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px">Product</th>';
                html += '<th style="padding:8px 12px;text-align:right;font-weight:700;text-transform:uppercase;letter-spacing:0.3px">Qty</th>';
                html += '<th style="padding:8px 12px;text-align:right;font-weight:700;text-transform:uppercase;letter-spacing:0.3px">Unit Cost</th>';
                html += '<th style="padding:8px 12px;text-align:right;font-weight:700;text-transform:uppercase;letter-spacing:0.3px">Total</th>';
                html += '</tr></thead><tbody>';

                var totalValue = 0;
                if (grn.items && grn.items.length > 0) {
                    grn.items.forEach(function (item) {
                        var lineTotal = parseFloat(item.line_total) || 0;
                        totalValue += lineTotal;
                        html += '<tr>';
                        html += '<td style="padding:8px 12px">' + (item.product ? item.product.name : '—') + '</td>';
                        html += '<td style="padding:8px 12px;text-align:right;font-weight:600">' + item.qty_received + '</td>';
                        html += '<td style="padding:8px 12px;text-align:right">' + parseFloat(item.unit_cost).toFixed(2) + '</td>';
                        html += '<td style="padding:8px 12px;text-align:right;font-weight:600">' + lineTotal.toFixed(2) + '</td>';
                        html += '</tr>';
                    });
                }

                html += '<tr style="background:#f5f5f5">';
                html += '<td colspan="3" style="padding:8px 12px;text-align:right;font-weight:700">Total Value</td>';
                html += '<td style="padding:8px 12px;text-align:right;font-weight:700;color:#5b5ea6">' + totalValue.toFixed(2) + '</td>';
                html += '</tr>';
                html += '</tbody></table>';

                $('#grnModalTitle').text(grn.grn_number);
                $('#grnModalBody').html(html);
                $('#grnModalPrintBtn').off('click').on('click', function () {
                    var printUrl = '{{ url("admin/inventory/goods-received") }}/' + grn.id + '/print';
                    window.open(printUrl, '_blank');
                });
            },
            error: function () {
                $('#grnModalBody').html(
                    '<div class="alert alert-danger" style="border-radius:8px">' +
                    '<i class="fas fa-exclamation-triangle mr-1"></i> Failed to load GRN details.' +
                    '</div>'
                );
            }
        });
    });

}());
</script>
@endpush
