@extends('backend.master')

@section('title', 'Sales')

@section('content')
<div class="card">
  <div class="card-body p-2 p-md-4 pt-0">
    <div class="row g-4">
      <div class="col-md-12">
        <div class="card-body table-responsive p-0">
          <table id="salesTable" class="table table-hover">
            <thead>
              <tr>
                <th data-orderable="false">#</th>
                <th>Sale ID</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Sub Total {{ currency()->symbol ?? '' }}</th>
                <th>Discount {{ currency()->symbol ?? '' }}</th>
                <th>Total {{ currency()->symbol ?? '' }}</th>
                <th>Paid {{ currency()->symbol ?? '' }}</th>
                <th>Due {{ currency()->symbol ?? '' }}</th>
                <th>Method</th>
                <th>Status</th>
                <th data-orderable="false">Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

{{-- ── Void modal — rendered outside @section so it is never cut off ── --}}
@if(auth()->user()->hasRole('Admin'))
<div class="modal fade" id="voidModal" tabindex="-1" role="dialog" aria-labelledby="voidModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-danger">

      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="voidModalLabel">
          <i class="fas fa-ban mr-1"></i> Void Sale <span id="voidSaleLabel"></span>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="alert alert-danger py-2 mb-3">
          <i class="fas fa-exclamation-triangle mr-1"></i>
          <strong>This action is irreversible.</strong> On confirmation:
          <ul class="mb-0 mt-1 pl-3 small">
            <li>All GL journal entries for this sale will be reversed</li>
            <li>Product stock quantities will be restored</li>
            <li>Debtor (AR) records linked to this sale will be deleted</li>
            <li>Collection receipts will be removed</li>
            <li>The sale will be permanently marked as <strong>Voided</strong></li>
          </ul>
        </div>
        <div class="form-group mb-0">
          <label for="voidReason">
            Reason for voiding <span class="text-danger">*</span>
          </label>
          <textarea id="voidReason" class="form-control" rows="3"
            placeholder="Enter a clear reason (minimum 5 characters)…"></textarea>
          <small id="voidReasonError" class="text-danger d-none">
            Please enter a reason (at least 5 characters).
          </small>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i> Cancel
        </button>
        <button type="button" id="confirmVoidBtn" class="btn btn-danger">
          <i class="fas fa-ban mr-1"></i> Void Sale
        </button>
      </div>

    </div>
  </div>
</div>
@endif

@push('script')
<script>
(function () {
  'use strict';

  var voidUrl      = "{{ route('backend.admin.orders.void', ['id' => '__ID__']) }}";
  var csrfToken    = "{{ csrf_token() }}";
  var isAdmin      = {{ auth()->user()->hasRole('Admin') ? 'true' : 'false' }};
  var currentVoidId = null;

  // ── DataTable ─────────────────────────────────────────────────
  var table = $('#salesTable').DataTable({
    processing: true,
    serverSide: true,
    ordering:   true,
    order:      [[1, 'desc']],
    ajax: { url: "{{ route('backend.admin.orders.index') }}" },
    columns: [
      { data: 'DT_RowIndex',    name: 'DT_RowIndex',    orderable: false, searchable: false },
      { data: 'saleId',         name: 'saleId' },
      { data: 'customer',       name: 'customer' },
      { data: 'item',           name: 'item' },
      { data: 'sub_total',      name: 'sub_total' },
      { data: 'discount',       name: 'discount' },
      { data: 'total',          name: 'total' },
      { data: 'paid',           name: 'paid' },
      { data: 'due',            name: 'due' },
      { data: 'payment_method', name: 'payment_method' },
      { data: 'status',         name: 'status' },
      { data: 'action',         name: 'action', orderable: false, searchable: false }
    ]
  });

  // ── Open void modal (admin only) ──────────────────────────────
  $(document).on('click', '.void-sale-btn', function () {
    if (!isAdmin) {
      Swal.fire({
        icon:  'error',
        title: 'Access Denied',
        text:  'Only administrators can void a sale.',
      });
      return;
    }

    currentVoidId = $(this).data('id');
    $('#voidSaleLabel').text($(this).data('sale'));
    $('#voidReason').val('').removeClass('is-invalid');
    $('#voidReasonError').addClass('d-none');
    $('#confirmVoidBtn')
      .prop('disabled', false)
      .html('<i class="fas fa-ban mr-1"></i> Void Sale');

    $('#voidModal').modal('show');
    setTimeout(function () { $('#voidReason').focus(); }, 450);
  });

  // ── Confirm void ─────────────────────────────────────────────
  $('#confirmVoidBtn').on('click', function () {
    var reason = $.trim($('#voidReason').val());

    if (reason.length < 5) {
      $('#voidReason').addClass('is-invalid');
      $('#voidReasonError').removeClass('d-none');
      $('#voidReason').focus();
      return;
    }

    $('#voidReason').removeClass('is-invalid');
    $('#voidReasonError').addClass('d-none');

    var btn = $(this)
      .prop('disabled', true)
      .html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing…');

    var url = voidUrl.replace('__ID__', currentVoidId);

    $.ajax({
      url:         url,
      type:        'POST',
      contentType: 'application/json',
      headers:     { 'X-CSRF-TOKEN': csrfToken },
      data:        JSON.stringify({ reason: reason }),
      success: function (res) {
        $('#voidModal').modal('hide');
        Swal.fire({
          icon:             'success',
          title:            'Sale Voided',
          text:             res.message,
          confirmButtonText:'OK',
        }).then(function () {
          table.ajax.reload(null, false);
        });
      },
      error: function (xhr) {
        var msg = 'Could not void sale. Please try again.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        btn.prop('disabled', false)
           .html('<i class="fas fa-ban mr-1"></i> Void Sale');

        // 403 = not admin, 422 = already voided / validation
        var icon = xhr.status === 403 ? 'warning' : 'error';
        Swal.fire({
          icon:  icon,
          title: xhr.status === 403 ? 'Not Allowed' : 'Void Failed',
          text:  msg,
        });
      }
    });
  });

  // Live validation feedback while typing
  $('#voidReason').on('input', function () {
    if ($.trim($(this).val()).length >= 5) {
      $(this).removeClass('is-invalid');
      $('#voidReasonError').addClass('d-none');
    }
  });

  // Reset modal state when dismissed
  $('#voidModal').on('hidden.bs.modal', function () {
    $('#voidReason').val('').removeClass('is-invalid');
    $('#voidReasonError').addClass('d-none');
    $('#confirmVoidBtn')
      .prop('disabled', false)
      .html('<i class="fas fa-ban mr-1"></i> Void Sale');
    currentVoidId = null;
  });

}());
</script>
@endpush
