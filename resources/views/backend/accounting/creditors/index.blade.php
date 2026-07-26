@extends('backend.master')
@section('title', 'Creditors (Accounts Payable)')

@section('content')
<div class="card">
    <div class="mt-n5 mb-3 d-flex justify-content-end">
        <button class="btn bg-gradient-primary" data-toggle="modal" data-target="#paymentModal">
            <i class="fas fa-money-bill-wave"></i> Record Supplier Payment
        </button>
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
                    <table id="creditorsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th data-orderable="false">#</th>
                                <th>Supplier</th>
                                <th>Current (0–30 days)</th>
                                <th>31–60 Days</th>
                                <th>61–90 Days</th>
                                <th>90+ Days</th>
                                <th>Total Outstanding</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Supplier Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('backend.admin.creditors.payment') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave mr-1"></i> Record Supplier Payment Voucher</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-control select2" required style="width:100%">
                            <option value="">-- Choose Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->phone ?? 'No Phone' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-control" required>
                            <option value="bank">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="mpesa">M-Pesa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes / Voucher Reference</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Cheque number, bank reference, or invoice details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Issue Payment Voucher</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        $('#creditorsTable').DataTable({
            processing: true,
            serverSide: false,
            ordering: true,
            ajax: {
                url: "{{ route('backend.admin.creditors.index') }}",
                dataSrc: 'data'
            },
            columns: [
                { data: 'DT_RowIndex',        name: 'DT_RowIndex',        orderable: false, searchable: false },
                { data: 'supplier_name',       name: 'supplier_name' },
                { data: 'current',             name: 'current' },
                { data: 'days_30',             name: 'days_30' },
                { data: 'days_60',             name: 'days_60' },
                { data: 'days_90_plus',        name: 'days_90_plus' },
                { data: 'total_outstanding',   name: 'total_outstanding' }
            ]
        });

        $(document).on('shown.bs.modal', '#paymentModal', function () {
            $('.select2').select2({ dropdownParent: $('#paymentModal') });
        });
    });
</script>
@endpush
