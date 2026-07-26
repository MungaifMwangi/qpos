@extends('backend.master')

@section('title', 'Inventory Report')

@section('content')

{{-- Dashboard Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-boxes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total SKUs</span>
                <span class="info-box-number">{{ number_format($productCount) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-cubes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Stock Units</span>
                <span class="info-box-number">{{ number_format($totalCount) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-coins"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Stock Value</span>
                <span class="info-box-number">{{ currency()->symbol ?? '' }} {{ number_format($totalValue, 2) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Inventory Table --}}
<div class="card">
  <div class="card-body p-2 p-md-4 pt-0">
    <div class="row g-4">
      <div class="col-md-12">
        <div class="card-body table-responsive p-0">
          <table id="datatables" class="table table-hover">
            <thead>
              <tr>
                <th data-orderable="false">#</th>
                <th>Name</th>
                <th>SKU</th>
                <th>Price {{ currency()->symbol ?? '' }}</th>
                <th>Stock</th>
                <th class="text-right">Stock Value {{ currency()->symbol ?? '' }}</th>
                <th data-orderable="false">Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Stock Adjustment Modal --}}
<div class="modal fade" id="adjustModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Stock Adjustment</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Adjusting stock for: <strong id="adjustProductName"></strong></p>
                <p class="mb-3">Current quantity: <strong id="adjustCurrentQty"></strong></p>
                <div class="form-group">
                    <label>New Quantity <span class="text-danger">*</span></label>
                    <input type="number" id="adjustNewQty" class="form-control" min="0" placeholder="Enter new quantity">
                </div>
                <div class="form-group">
                    <label>Reason for adjustment</label>
                    <input type="text" id="adjustReason" class="form-control" placeholder="e.g. Physical count, spoilage, receiving...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="confirmAdjustBtn" class="btn btn-warning">
                    <i class="fas fa-save"></i> Save Adjustment
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
  .dataTables_length select {
    margin-right: 6px;
    height: 37px !important;
    border: 1px solid rgba(0,0,0,.3);
  }
  .dataTables_length label { display: flex; align-items: center; }
</style>
@endpush

@push('script')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

<script>
  var adjustUrl = "{{ route('backend.admin.inventory.adjust') }}";
  var csrfToken = "{{ csrf_token() }}";
  var currentAdjustId = null;

  $(function() {
    var table = $('#datatables').DataTable({
      processing: true,
      serverSide: true,
      ordering: true,
      order: [[1, 'asc']],
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
      ajax: { url: "{{ route('backend.admin.inventory.report') }}" },
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex' },
        { data: 'name',        name: 'name' },
        { data: 'sku',         name: 'sku' },
        { data: 'price',       name: 'price' },
        { data: 'quantity',    name: 'quantity' },
        { data: 'stock_value', name: 'stock_value', className: 'text-right' },
        { data: 'action',      name: 'action', orderable: false, searchable: false }
      ],
      dom: 'lBfrtip',
      buttons: [
        { extend: 'excel', text: 'Excel', className: 'btn btn-sm' },
        { extend: 'pdf',   text: 'PDF',   className: 'btn btn-sm' },
        { extend: 'print', text: 'Print', className: 'btn btn-sm' }
      ],
      initComplete: function() {
        $('.dataTables_length label').contents().filter(function() {
          return this.nodeType === 3;
        }).remove();
      }
    });

    // Open adjust modal
    $(document).on('click', '.adjust-stock-btn', function() {
      currentAdjustId = $(this).data('id');
      $('#adjustProductName').text($(this).data('name'));
      $('#adjustCurrentQty').text($(this).data('qty'));
      $('#adjustNewQty').val($(this).data('qty'));
      $('#adjustReason').val('');
      $('#adjustModal').modal('show');
    });

    // Confirm stock adjustment
    $('#confirmAdjustBtn').on('click', function() {
      var newQty = $('#adjustNewQty').val();
      if (newQty === '' || parseInt(newQty) < 0) {
        alert('Please enter a valid quantity (0 or more).');
        return;
      }
      var btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
      $.ajax({
        url: adjustUrl,
        type: 'POST',
        data: {
          _token: csrfToken,
          product_id:   currentAdjustId,
          new_quantity: newQty,
          reason:       $('#adjustReason').val()
        },
        success: function(res) {
          $('#adjustModal').modal('hide');
          Swal.fire('Adjusted!', res.message, 'success');
          table.ajax.reload(null, false);
          btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Adjustment');
        },
        error: function(xhr) {
          var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Adjustment failed.';
          Swal.fire('Error', msg, 'error');
          btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Adjustment');
        }
      });
    });
  });
</script>
@endpush
