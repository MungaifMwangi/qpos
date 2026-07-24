@extends('backend.master')

@section('title', 'Expenses')

@section('content')
<div class="card">

  @can('expense_create')
  <div class="mt-n5 mb-3 d-flex justify-content-end">
    <a href="{{ route('backend.admin.expenses.create') }}" class="btn bg-gradient-primary">
      <i class="fas fa-plus-circle"></i>
      Add New
    </a>
  </div>
  @endcan
  <div class="card-body p-2 p-md-4 pt-0">
    <div class="row g-4">
      <div class="col-md-12">
        <div class="card-body table-responsive p-0" id="table_data">
          <table id="datatables" class="table table-hover">
            <thead>
              <tr>
                <th data-orderable="false">#</th>
                <th>Title</th>
                <th>Category</th>
                <th>Amount ({{ currency()->symbol ?? '' }})</th>
                <th>Date</th>
                <th>Created By</th>
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

<script type="text/javascript">
  $(function() {
    let table = $('#datatables').DataTable({
      processing: true,
      serverSide: true,
      ordering: true,
      order: [
        [4, 'desc']
      ],
      ajax: {
        url: "{{ route('backend.admin.expenses.index') }}"
      },

      columns: [{
          data: 'DT_RowIndex',
          name: 'DT_RowIndex'
        },
        {
          data: 'title',
          name: 'title'
        },
        {
          data: 'category',
          name: 'category'
        },
        {
          data: 'amount',
          name: 'amount'
        },
        {
          data: 'expense_date',
          name: 'expense_date'
        },
        {
          data: 'creator',
          name: 'creator'
        },
        {
          data: 'action',
          name: 'action'
        },
      ]
    });
  });
</script>
@endpush
