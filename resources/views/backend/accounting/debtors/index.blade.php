@extends('backend.master')
@section('title', 'Debtors (Accounts Receivable)')

@section('content')
<div class="card">
    <div class="mt-n5 mb-3 d-flex justify-content-end">
        @can('debtors_receipt_create')
        <button class="btn bg-gradient-success" data-toggle="modal" data-target="#receiptModal">
            <i class="fas fa-receipt"></i> Record Customer Receipt
        </button>
        @endcan
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
                    <table id="debtorsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th data-orderable="false">#</th>
                                <th>Customer</th>
                                <th>Current (0–30 days)</th>
                                <th>31–60 Days</th>
                                <th>61–90 Days</th>
                                <th>90+ Days</th>
                                <th>Total Outstanding</th>
                                <th data-orderable="false">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Customer Receipt Modal --}}
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('backend.admin.debtors.receipt') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-receipt mr-1"></i> Record Customer Payment Receipt</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-control select2" required style="width:100%">
                            <option value="">-- Choose Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone ?? 'No Phone' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="mpesa">M-Pesa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes / Reference</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Cheque number, M-Pesa ref, or notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Post Receipt to Ledger</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        $('#debtorsTable').DataTable({
            processing: true,
            serverSide: false,
            ordering: true,
            ajax: {
                url: "{{ route('backend.admin.debtors.index') }}",
                dataSrc: 'data'
            },
            columns: [
                { data: 'DT_RowIndex',       name: 'DT_RowIndex',       orderable: false, searchable: false },
                { data: 'customer_name',     name: 'customer_name' },
                { data: 'current',           name: 'current' },
                { data: 'days_30',           name: 'days_30' },
                { data: 'days_60',           name: 'days_60' },
                { data: 'days_90_plus',      name: 'days_90_plus' },
                { data: 'total_outstanding', name: 'total_outstanding' },
                { data: 'action',            name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('shown.bs.modal', '#receiptModal', function () {
            $('.select2').select2({ dropdownParent: $('#receiptModal') });
        });
    });
</script>
@endpush
