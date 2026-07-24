@extends('backend.master')
@section('title', 'Creditors Management (Accounts Payable)')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-truck-loading"></i> Creditors Management (AP)</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Creditors</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
            @endif

            <div class="card card-primary card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Accounts Payable (AP) Aging Summary</h3>
                    <button class="btn btn-primary btn-sm ml-auto" data-toggle="modal" data-target="#paymentModal">
                        <i class="fas fa-money-bill-wave"></i> Record Supplier Payment Voucher
                    </button>
                </div>
                <div class="card-body">
                    <table id="creditorsTable" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Supplier Name</th>
                                <th>Current (0-30 days)</th>
                                <th>31-60 Days</th>
                                <th>61-90 Days</th>
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

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('backend.admin.creditors.payment') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave"></i> Record Supplier Payment Voucher</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Choose Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->phone ?? 'No Phone' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Payment Amount (KES) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-control" required>
                            <option value="bank">Bank Transfer (1030)</option>
                            <option value="cash">Cash (1010)</option>
                            <option value="mpesa">M-Pesa (1020)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes / Voucher Details</label>
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

@push('js')
<script>
    $(document).ready(function() {
        $('#creditorsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('backend.admin.creditors.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'supplier_name', name: 'supplier_name' },
                { data: 'current', name: 'current' },
                { data: 'days_30', name: 'days_30' },
                { data: 'days_60', name: 'days_60' },
                { data: 'days_90_plus', name: 'days_90_plus' },
                { data: 'total_outstanding', name: 'total_outstanding' }
            ]
        });
    });
</script>
@endpush
