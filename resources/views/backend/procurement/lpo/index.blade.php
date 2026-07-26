@extends('backend.master')
@section('title', 'LPO Procurement')

@section('content')
<div class="card">

    <div class="mt-n5 mb-3 d-flex justify-content-end">
        <a href="{{ route('backend.admin.lpo.create') }}" class="btn bg-gradient-primary">
            <i class="fas fa-plus-circle"></i> New LPO Requisition
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-3">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mx-3">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card-body p-2 p-md-4 pt-0">
        <div class="row g-4">
            <div class="col-md-12">
                <div class="card-body table-responsive p-0">
                    <table id="lpoTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th data-orderable="false">#</th>
                                <th>LPO Number</th>
                                <th>Supplier</th>
                                <th class="text-right">Total ({{ currency()->symbol ?? 'KES' }})</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th data-orderable="false">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
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

                    {{-- Spinner shown while loading items --}}
                    <div id="grnLoading" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-warning"></i>
                        <p class="mt-2 text-muted">Loading order lines…</p>
                    </div>

                    {{-- Table rendered by JS --}}
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
    var itemsUrl     = "{{ url('admin/lpo') }}";   // base; appended: /{id}/items
    var grnBaseUrl   = "{{ url('admin/lpo') }}";   // base; appended: /{id}/grn
    var csrfToken    = "{{ csrf_token() }}";

    // ── DataTable ─────────────────────────────────────────────────
    var table = $('#lpoTable').DataTable({
        processing: true,
        serverSide: true,
        ordering:   true,
        order:      [[1, 'desc']],
        ajax: { url: "{{ route('backend.admin.lpo.index') }}" },
        columns: [
            { data: 'DT_RowIndex',   name: 'DT_RowIndex',   orderable: false, searchable: false },
            { data: 'lpo_number',    name: 'lpo_number' },
            { data: 'supplier',      name: 'supplier.name' },
            { data: 'total_amount',  name: 'total_amount',  className: 'text-right' },
            { data: 'date',          name: 'issued_at' },
            { data: 'status',        name: 'status' },
            { data: 'action',        name: 'action',        orderable: false, searchable: false }
        ]
    });

    // ── Open GRN modal ────────────────────────────────────────────
    $(document).on('click', '.receive-btn', function () {
        currentLpoId = $(this).data('id');
        var lpoNum   = $(this).data('lpo');

        // Reset modal state
        $('#grnLpoNumber').text(lpoNum);
        $('#grnItemsBody').empty();
        $('#grnItemsWrap').addClass('d-none');
        $('#grnLoading').removeClass('d-none');
        $('#grnNotes').val('');
        $('#grnSubmitBtn').prop('disabled', true);
        $('#grnModal').modal('show');

        // Fetch LPO line items via AJAX
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

        // Collect form data
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
            traditional: true,   // send arrays as PHP expects
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
