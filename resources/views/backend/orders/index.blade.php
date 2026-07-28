@extends('backend.master')
@section('title', ' ')

@section('content')
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#1a7a4e,#28a745);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-shopping-cart"></i>
    </div>
    <div>
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Sales</h2>
        <p style="margin:0;font-size:12px;color:#999">View and manage all sales transactions</p>
    </div>
</div>

<div class="filter-bar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:16px;padding:12px 16px;background:#fff;border:1px solid #e8e8e8;border-radius:12px">
    <label style="font-size:13px;font-weight:600">Status:</label>
    <select id="filterStatus" class="form-control form-control-sm" style="width:160px;border-radius:8px">
        <option value="">All</option>
        <option value="paid">Paid</option>
        <option value="pending">Pending</option>
        <option value="voided">Voided</option>
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
        <table id="salesTable" class="table table-hover" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">#</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Sale ID</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Customer</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Items</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Date</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Total</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Paid</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Due</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Method</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Status</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-center">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- Void confirmation modal --}}
@if(auth()->user()->can('sale_void'))
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

@endsection

@push('script')
<script src="{{ asset('plugins/sweetalert2/sweetalert2.all.js') }}"></script>
<script>
(function () {
  'use strict';

  var voidUrl      = "{{ route('backend.admin.orders.void', ['id' => '__ID__']) }}";
  var csrfToken    = "{{ csrf_token() }}";
  var canVoid      = {{ auth()->user()->can('sale_void') ? 'true' : 'false' }};
  var currentVoidId = null;

  var table = $('#salesTable').DataTable({
    processing: true,
    serverSide: true,
    ordering:   true,
    order:      [[4, 'desc']],
    ajax: {
      url: "{{ route('backend.admin.orders.index') }}",
      data: function (d) {
        d.status = $('#filterStatus').val();
        d.from   = $('#filterFrom').val();
        d.to     = $('#filterTo').val();
      }
    },
    columns: [
      { data: 'DT_RowIndex',    name: 'DT_RowIndex',    orderable: false, searchable: false },
      { data: 'saleId',         name: 'saleId' },
      { data: 'customer',       name: 'customer.name' },
      { data: 'item',           name: 'item',            orderable: false, searchable: false },
      { data: 'date',           name: 'created_at' },
      { data: 'total',          name: 'total',           className: 'text-right' },
      { data: 'paid',           name: 'paid',            className: 'text-right' },
      { data: 'due',            name: 'due',             className: 'text-right' },
      { data: 'payment_method', name: 'payment_method' },
      { data: 'status',         name: 'payment_status' },
      { data: 'action',         name: 'action',          orderable: false, searchable: false, className: 'text-center' }
    ]
  });

  $('#applyFilter').on('click', function () { table.ajax.reload(); });

  $('#resetFilter').on('click', function () {
    $('#filterStatus').val('');
    $('#filterFrom').val('');
    $('#filterTo').val('');
    table.ajax.reload();
  });

  function showAlert(opts) {
    if (typeof Swal !== 'undefined') {
      return Swal.fire(opts);
    }
    alert((opts.title ? opts.title + '\n\n' : '') + (opts.text || ''));
    return Promise.resolve();
  }

  $(document).on('click', '.void-sale-btn', function () {
    if (!canVoid) {
      showAlert({
        icon:  'error',
        title: 'Access Denied',
        text:  'You do not have permission to void a sale.',
      });
      return;
    }

    if (!$('#voidModal').length) {
      showAlert({
        icon:  'error',
        title: 'Void Unavailable',
        text:  'The void dialog could not be loaded. Please refresh the page and try again.',
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
        showAlert({
          icon:              'success',
          title:             'Sale Voided',
          text:              res.message || 'The sale was voided successfully.',
          confirmButtonText: 'OK',
        }).then(function () {
          table.ajax.reload(null, false);
        });
      },
      error: function (xhr) {
        var msg = 'Could not void sale. Please try again.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        } else if (xhr.status === 403) {
          msg = 'Access denied. You do not have permission to void a sale.';
        } else if (xhr.status === 422) {
          msg = xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.reason
            ? xhr.responseJSON.errors.reason[0]
            : msg;
        }
        btn.prop('disabled', false)
           .html('<i class="fas fa-ban mr-1"></i> Void Sale');

        var icon = xhr.status === 403 ? 'warning' : 'error';
        showAlert({
          icon:  icon,
          title: xhr.status === 403 ? 'Not Allowed' : 'Void Failed',
          text:  msg,
        });
      }
    });
  });

  $('#voidReason').on('input', function () {
    if ($.trim($(this).val()).length >= 5) {
      $(this).removeClass('is-invalid');
      $('#voidReasonError').addClass('d-none');
    }
  });

  $('#voidModal').on('hidden.bs.modal', function () {
    $('#voidReason').val('').removeClass('is-invalid');
    $('#voidReasonError').addClass('d-none');
    $('#confirmVoidBtn')
      .prop('disabled', false)
      .html('<i class="fas fa-ban mr-1"></i> Void Sale');
    currentVoidId = null;
  });

  // ── Direct receipt printing via hidden iframe ──────────────
  var printReceiptUrl = "{{ route('backend.admin.orders.print-receipt', '') }}";

  $(document).on('click', '.btn-print-receipt', function () {
    var id = $(this).data('id');
    var url = printReceiptUrl + '/' + id;

    var iframe = document.createElement('iframe');
    iframe.style.position = 'absolute';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    document.body.appendChild(iframe);
    iframe.src = url;

    // Clean up iframe after a reasonable timeout
    setTimeout(function () {
      if (iframe.parentNode) {
        iframe.parentNode.removeChild(iframe);
      }
    }, 30000);
  });

}());
</script>
@endpush
