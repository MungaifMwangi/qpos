@extends('backend.master')
@section('title', ' ')

@section('content')
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-file-alt"></i>
    </div>
    <div>
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">LPO Procurement</h2>
        <p style="margin:0;font-size:12px;color:#999">Manage Local Purchase Orders and track goods delivery</p>
    </div>
    <div style="margin-left:auto">
        @can('lpo_create')
        <a href="{{ route('backend.admin.lpo.create') }}" class="btn btn-sm" style="background:#2d2d2d;color:#fff;border-radius:8px;padding:6px 16px;font-weight:600">
            <i class="fas fa-plus-circle mr-1"></i> New LPO
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
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" style="border-radius:10px;margin-bottom:16px">
        <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<div class="filter-bar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:16px;padding:12px 16px;background:#fff;border:1px solid #e8e8e8;border-radius:12px">
    <label style="font-size:13px;font-weight:600">Status:</label>
    <select id="filterStatus" class="form-control form-control-sm" style="width:180px;border-radius:8px">
        <option value="">All</option>
        <option value="requisition">Requisition</option>
        <option value="issued">Issued</option>
        <option value="goods_received">Goods Received</option>
        <option value="invoice_matched">Invoice Matched</option>
        <option value="posted">Posted to AP</option>
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
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">All LPOs</h6>
    </div>
    <div style="padding:0 8px 8px">
        <table id="lpoTable" class="table table-hover" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">#</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">LPO Number</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Supplier</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Total</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Date</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Status</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-center">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- ── GRN Receive Modal ─────────────────────────────────────────── --}}
<div class="modal fade" id="grnModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <form id="grnForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-truck-loading mr-1"></i>
                        Receive Goods — <span id="grnLpoNumber"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Enter the quantity physically received for each line.
                        Leave a line at <strong>0</strong> to skip it this delivery.
                        Partial deliveries are supported.
                    </p>

                    <div id="grnLoading" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-warning"></i>
                        <p class="mt-2 text-muted">Loading order lines…</p>
                    </div>

                    <div id="grnItemsWrap" class="table-responsive d-none">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-right">Ordered</th>
                                    <th class="text-right">Already Received</th>
                                    <th class="text-right">Remaining</th>
                                    <th style="width:130px">Qty Receiving <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody id="grnItemsBody"></tbody>
                        </table>
                    </div>

                    <div class="form-group mt-3 mb-0">
                        <label>Delivery Notes <small class="text-muted">(optional)</small></label>
                        <input type="text" id="grnNotes" class="form-control"
                               placeholder="e.g. Partial delivery, batch ref, driver name…">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="grnSubmitBtn" disabled>
                        <i class="fas fa-box-open"></i> Log GRN & Update Stock
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
(function () {
    'use strict';

    var currentLpoId = null;
    var itemsUrl     = "{{ url('admin/lpo') }}";
    var grnBaseUrl   = "{{ url('admin/lpo') }}";
    var csrfToken    = "{{ csrf_token() }}";

    var table = $('#lpoTable').DataTable({
        processing: true,
        serverSide: true,
        ordering:   true,
        order:      [[4, 'desc']],
        ajax: {
            url: "{{ route('backend.admin.lpo.index') }}",
            data: function (d) {
                d.status = $('#filterStatus').val();
                d.from   = $('#filterFrom').val();
                d.to     = $('#filterTo').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',   name: 'DT_RowIndex',   orderable: false, searchable: false },
            { data: 'lpo_number',    name: 'lpo_number' },
            { data: 'supplier',      name: 'supplier.name' },
            { data: 'total_amount',  name: 'total_amount',  className: 'text-right' },
            { data: 'date',          name: 'created_at' },
            { data: 'status',        name: 'status',        orderable: false, searchable: false },
            { data: 'action',        name: 'action',        orderable: false, searchable: false, className: 'text-center' }
        ]
    });

    $('#applyFilter').on('click', function () { table.ajax.reload(); });

    $('#resetFilter').on('click', function () {
        $('#filterStatus').val('');
        $('#filterFrom').val('');
        $('#filterTo').val('');
        table.ajax.reload();
    });

    // ── Open GRN modal ────────────────────────────────────────────
    $(document).on('click', '.receive-btn', function () {
        currentLpoId = $(this).data('id');
        var lpoNum   = $(this).data('lpo');

        $('#grnLpoNumber').text(lpoNum);
        $('#grnItemsBody').empty();
        $('#grnItemsWrap').addClass('d-none');
        $('#grnLoading').removeClass('d-none');
        $('#grnNotes').val('');
        $('#grnSubmitBtn').prop('disabled', true);
        $('#grnModal').modal('show');

        $.ajax({
            url:  itemsUrl + '/' + currentLpoId + '/items',
            type: 'GET',
            success: function (res) {
                var rows = '';
                $.each(res.items, function (i, item) {
                    var remaining = parseInt(item.qty_remaining);
                    var inputVal  = remaining > 0 ? remaining : 0;
                    rows += '<tr>'
                          + '<td>' + item.product_name + '</td>'
                          + '<td class="text-right">' + item.qty_ordered + '</td>'
                          + '<td class="text-right">' + item.qty_received + '</td>'
                          + '<td class="text-right">'
                          +   (remaining > 0
                                ? '<strong class="text-success">' + remaining + '</strong>'
                                : '<span class="text-muted">0</span>')
                          + '</td>'
                          + '<td>'
                          +   '<input type="hidden"  name="grn_items[' + i + '][lpo_item_id]" value="' + item.id + '">'
                          +   '<input type="number"  name="grn_items[' + i + '][qty_received]"'
                          +   ' class="form-control form-control-sm grn-qty-input"'
                          +   ' value="' + inputVal + '"'
                          +   ' min="0" max="' + remaining + '"'
                          +   (remaining <= 0 ? ' disabled' : '') + '>'
                          + '</td>'
                          + '</tr>';
                });
                $('#grnItemsBody').html(rows);
                $('#grnLoading').addClass('d-none');
                $('#grnItemsWrap').removeClass('d-none');
                $('#grnSubmitBtn').prop('disabled', false);
            },
            error: function () {
                $('#grnLoading').html(
                    '<div class="alert alert-danger">Failed to load order lines. Please try again.</div>'
                );
            }
        });
    });

    // ── Submit GRN ────────────────────────────────────────────────
    $('#grnForm').on('submit', function (e) {
        e.preventDefault();

        var btn = $('#grnSubmitBtn').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Saving…');

        var payload = {
            _token:    csrfToken,
            grn_notes: $('#grnNotes').val(),
            grn_items: [],
        };

        $('#grnItemsBody tr').each(function () {
            var itemId = $(this).find('input[name*="lpo_item_id"]').val();
            var qty    = parseInt($(this).find('.grn-qty-input').val()) || 0;
            payload.grn_items.push({ lpo_item_id: itemId, qty_received: qty });
        });

        $.ajax({
            url:         grnBaseUrl + '/' + currentLpoId + '/grn',
            type:        'POST',
            data:        payload,
            traditional: true,
            success: function (res) {
                $('#grnModal').modal('hide');
                Swal.fire({
                    icon:  'success',
                    title: 'GRN Recorded',
                    text:  res.message,
                    timer: 3000,
                    showConfirmButton: false,
                });
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Could not record GRN.';
                Swal.fire('Error', msg, 'error');
                btn.prop('disabled', false)
                   .html('<i class="fas fa-box-open"></i> Log GRN & Update Stock');
            }
        });
    });

}());
</script>
@endpush
