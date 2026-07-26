@extends('backend.master')
@section('title', ' ')

@section('content')
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#2c3e50,#34495e);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-file-invoice-dollar"></i>
    </div>
    <div>
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Creditors (Accounts Payable)</h2>
        <p style="margin:0;font-size:12px;color:#999">Outstanding supplier balances and aging analysis</p>
    </div>
    <div style="margin-left:auto">
        @can('creditors_payment_create')
        <button class="btn btn-sm" data-toggle="modal" data-target="#paymentModal" style="background:#2d2d2d;color:#fff;border-radius:8px;padding:6px 16px;font-weight:600">
            <i class="fas fa-money-bill-wave mr-1"></i> Record Payment
        </button>
        @endcan
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="border-radius:10px;margin-bottom:16px">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" style="border-radius:10px;margin-bottom:16px">
        <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;overflow:hidden">
    <div style="padding:16px 20px 8px">
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">Aging Report</h6>
    </div>
    <div style="padding:0 8px 8px">
        <table id="creditorsTable" class="table table-hover" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">#</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Supplier</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Current (0-30)</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">31-60 Days</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">61-90 Days</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">90+ Days</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Total Outstanding</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- Supplier Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('backend.admin.creditors.payment') }}" method="POST">
            @csrf
            <div class="modal-content" style="border-radius:14px;overflow:hidden">
                <div class="modal-header" style="background:linear-gradient(135deg,#2c3e50,#34495e);color:#fff;padding:14px 20px">
                    <h5 class="modal-title" style="font-size:15px;font-weight:700">
                        <i class="fas fa-money-bill-wave mr-1"></i> Record Supplier Payment
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity:0.8"><span>&times;</span></button>
                </div>
                <div class="modal-body" style="padding:20px">
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:600">Select Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-control select2" required style="width:100%">
                            <option value="">-- Choose Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->phone ?? 'No Phone' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:600">Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:600">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-control" required>
                            <option value="bank">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="mpesa">M-Pesa</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label style="font-size:12px;font-weight:600">Notes / Voucher Reference</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Cheque number, bank reference, or invoice details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding:12px 20px;border-top:1px solid #f0f0f0">
                    <button type="button" class="btn btn-sm" style="background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:8px" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm" style="background:#2c3e50;color:#fff;border-radius:8px">
                        <i class="fas fa-check mr-1"></i> Issue Payment
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

    $('#creditorsTable').DataTable({
        processing: true,
        serverSide: false,
        ordering: true,
        order: [[6, 'desc']],
        ajax: {
            url: "{{ route('backend.admin.creditors.index') }}",
            dataSrc: 'data'
        },
        columns: [
            { data: 'DT_RowIndex',        name: 'DT_RowIndex',        orderable: false, searchable: false },
            { data: 'supplier_name',       name: 'supplier_name' },
            { data: 'current',             name: 'current',             className: 'text-right' },
            { data: 'days_30',             name: 'days_30',             className: 'text-right' },
            { data: 'days_60',             name: 'days_60',             className: 'text-right' },
            { data: 'days_90_plus',        name: 'days_90_plus',        className: 'text-right' },
            { data: 'total_outstanding',   name: 'total_outstanding',   className: 'text-right' }
        ]
    });

    $(document).on('shown.bs.modal', '#paymentModal', function () {
        $('.select2').select2({ dropdownParent: $('#paymentModal') });
    });
}());
</script>
@endpush
