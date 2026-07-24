@extends('backend.master')
@section('title', 'Debtors Management (Accounts Receivable)')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-users-cog"></i> Debtors Management (AR)</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Debtors</li>
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
                    <h3 class="card-title">Accounts Receivable (AR) Aging Summary</h3>
                    <button class="btn btn-success btn-sm ml-auto" data-toggle="modal" data-target="#receiptModal">
                        <i class="fas fa-receipt"></i> Record Customer Payment Receipt
                    </button>
                </div>
                <div class="card-body">
                    <table id="debtorsTable" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer Name</th>
                                <th>Current (0-30 days)</th>
                                <th>31-60 Days</th>
                                <th>61-90 Days</th>
                                <th>90+ Days</th>
                                <th>Total Outstanding</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('backend.admin.debtors.receipt') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-receipt"></i> Record Customer Payment Receipt</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Choose Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone ?? 'No Phone' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount (KES) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-control" required>
                            <option value="cash">Cash (1010)</option>
                            <option value="bank">Bank (1030)</option>
                            <option value="mpesa">M-Pesa Clearing (1020)</option>
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

@push('js')
<script>
    $(document).ready(function() {
        $('#debtorsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('backend.admin.debtors.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'current', name: 'current' },
                { data: 'days_30', name: 'days_30' },
                { data: 'days_60', name: 'days_60' },
                { data: 'days_90_plus', name: 'days_90_plus' },
                { data: 'total_outstanding', name: 'total_outstanding' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endpush
