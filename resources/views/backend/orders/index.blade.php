@extends('backend.master')

@section('title', 'Sales')

@section('content')
<div class="card">
  <div class="card-body p-2 p-md-4 pt-0">
    <div class="row g-4">
      <div class="col-md-12">
        <div class="card-body table-responsive p-0" id="table_data">
          <table id="datatables" class="table table-hover">
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

@push('script')
<script>
  var voidUrl = "{{ url('admin/orders/void') }}";

  $(function() {
    var table = $('#datatables').DataTable({
      processing: true,
      serverSide: true,
      ordering: true,
      order: [[1, 'desc']],
      ajax: {
        url: "{{ route('backend.admin.orders.index') }}"
      },
      columns: [
        { data: 'DT_RowIndex',    name: 'DT_RowIndex' },
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
        { data: 'action',         name: 'action' }
      ]
    });

    // Void sale handler
    $(document).on('click', '.void-sale-btn', function () {
      var id   = $(this).data('id');
      var sale = $(this).data('sale');

      Swal.fire({
        title: 'Void Sale ' + sale + '?',
        html: '<span class="text-danger">This will reverse all GL journal entries, restore stock, and delete associated transactions. This action cannot be undone.</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, void it',
        cancelButtonText: 'Cancel'
      }).then(function(result) {
        if (result.isConfirmed) {
          $.ajax({
            url: voidUrl + '/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
              Swal.fire('Voided!', res.message, 'success');
              table.ajax.reload();
            },
            error: function(xhr) {
              var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Could not void sale.';
              Swal.fire('Error', msg, 'error');
            }
          });
        }
      });
    });
  });
</script>
@endpush
