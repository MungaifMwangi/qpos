@extends('backend.master')
@section('title', 'LPO ' . $lpo->lpo_number)

@section('content')
<div class="card">

    {{-- ── Top action bar ──────────────────────────────────────── --}}
    <div class="mt-n5 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <a href="{{ route('backend.admin.lpo.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to LPO List
        </a>
        <div>
            <a href="{{ route('backend.admin.lpo.print', $lpo->id) }}"
               target="_blank" class="btn bg-gradient-secondary btn-sm mr-1">
                <i class="fas fa-print"></i> Print LPO
            </a>
            @can('grn_receive')
            @if(in_array($lpo->status, ['issued','goods_received']))
                <button class="btn bg-gradient-warning btn-sm" id="openGrnBtn">
                    <i class="fas fa-truck-loading"></i> Receive Goods (GRN)
                </button>
            @endif
            @endcan
        </div>
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

        {{-- ── Header meta ─────────────────────────────────────── --}}
        <div class="row mb-3">
            <div class="col-sm-6 col-md-3 mb-2">
                <small class="text-muted d-block">LPO Number</small>
                <strong>{{ $lpo->lpo_number }}</strong>
            </div>
            <div class="col-sm-6 col-md-3 mb-2">
                <small class="text-muted d-block">Supplier</small>
                <strong>{{ $lpo->supplier->name }}</strong>
                @if($lpo->supplier->phone)
                    <br><small class="text-muted">{{ $lpo->supplier->phone }}</small>
                @endif
            </div>
            <div class="col-sm-6 col-md-2 mb-2">
                <small class="text-muted d-block">Date Issued</small>
                <strong>{{ optional($lpo->issued_at)->format('d M Y') ?? '-' }}</strong>
            </div>
            <div class="col-sm-6 col-md-2 mb-2">
                <small class="text-muted d-block">Status</small>
                @php
                    $statusMap = [
                        'requisition'     => ['secondary','Requisition'],
                        'issued'          => ['info',     'Issued'],
                        'goods_received'  => ['warning',  'Goods Received'],
                        'invoice_matched' => ['primary',  'Invoice Matched'],
                        'posted'          => ['success',  'Posted to AP'],
                    ];
                    [$sc, $sl] = $statusMap[$lpo->status] ?? ['dark', ucfirst($lpo->status)];
                @endphp
                <span class="badge bg-{{ $sc }} p-2">{{ $sl }}</span>
            </div>
            <div class="col-sm-6 col-md-2 mb-2 text-md-right">
                <small class="text-muted d-block">LPO Total</small>
                <strong class="text-success h5">
                    {{ currency()->symbol ?? 'KES' }} {{ number_format($lpo->total_amount, 2) }}
                </strong>
            </div>
        </div>

        @if($lpo->notes)
        <div class="alert alert-light border mb-3 py-2">
            <i class="fas fa-sticky-note mr-1 text-muted"></i>
            <small>{{ $lpo->notes }}</small>
        </div>
        @endif

        {{-- ── Ordered Items ───────────────────────────────────── --}}
        <h6 class="font-weight-bold mb-2"><i class="fas fa-list mr-1"></i> Ordered Items</h6>
        <div class="table-responsive mb-4">
            <table class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th class="text-right">Qty Ordered</th>
                        <th class="text-right">Unit Cost</th>
                        <th class="text-right">Line Total</th>
                        <th class="text-right">Total Received</th>
                        <th class="text-right">Remaining</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lpo->items as $i => $item)
                    @php
                        $received  = $item->grnItems->sum('qty_received');
                        $remaining = $item->qty_ordered - $received;
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            {{ $item->product->name }}
                            @if(optional($item->product->unit)->short_name)
                                <small class="text-muted">({{ $item->product->unit->short_name }})</small>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($item->qty_ordered) }}</td>
                        <td class="text-right">{{ number_format($item->unit_cost, 2) }}</td>
                        <td class="text-right">{{ number_format($item->total_cost, 2) }}</td>
                        <td class="text-right {{ $received > 0 ? 'text-success' : 'text-muted' }}">
                            {{ number_format($received) }}
                        </td>
                        <td class="text-right {{ $remaining > 0 ? 'text-warning' : 'text-success' }}">
                            {{ $remaining > 0 ? number_format($remaining) : '✓ Complete' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light font-weight-bold">
                    <tr>
                        <td colspan="4" class="text-right">Total</td>
                        <td class="text-right text-success">
                            {{ number_format($lpo->total_amount, 2) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- ── GRN History ─────────────────────────────────────── --}}
        <h6 class="font-weight-bold mb-2">
            <i class="fas fa-truck-loading mr-1"></i>
            Goods Receipt History
            <span class="badge bg-secondary ml-1">{{ $lpo->goodsReceiptNotes->count() }}</span>
        </h6>

        @if($lpo->goodsReceiptNotes->isEmpty())
            <p class="text-muted mb-4"><small>No goods received yet.</small></p>
        @else
            @foreach($lpo->goodsReceiptNotes as $grn)
            <div class="card border mb-3">
                <div class="card-header py-2 d-flex justify-content-between align-items-center bg-light">
                    <span>
                        <i class="fas fa-box-open mr-1 text-warning"></i>
                        <strong>{{ $grn->grn_number }}</strong>
                        &mdash; {{ \Carbon\Carbon::parse($grn->received_date)->format('d M Y') }}
                    </span>
                    <small class="text-muted">
                        Received by: {{ optional(\App\Models\User::find($grn->received_by))->name ?? 'System' }}
                    </small>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-right">Qty Received</th>
                                <th class="text-right">Unit Cost</th>
                                <th class="text-right">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grn->items as $gi)
                            @php $lpoItem = $gi->lpoItem ?? $lpo->items->where('id', $gi->lpo_item_id)->first(); @endphp
                            <tr>
                                <td>{{ $gi->product->name ?? '-' }}</td>
                                <td class="text-right">{{ $gi->qty_received }}</td>
                                <td class="text-right">{{ number_format(optional($lpoItem)->unit_cost, 2) }}</td>
                                <td class="text-right">
                                    {{ number_format($gi->qty_received * (optional($lpoItem)->unit_cost ?? 0), 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($grn->notes)
                        <div class="px-3 py-2 border-top">
                            <small class="text-muted"><i class="fas fa-sticky-note mr-1"></i>{{ $grn->notes }}</small>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        @endif

        {{-- ── Supplier Invoice / 3-Way Match ──────────────────── --}}
        <h6 class="font-weight-bold mb-3"><i class="fas fa-file-invoice-dollar mr-1"></i> Supplier Invoice &amp; 3-Way Match</h6>

        @if($lpo->supplierInvoice)
            {{-- Invoice already recorded --}}
            <div class="alert {{ $lpo->supplierInvoice->status === 'matched' ? 'alert-success' : 'alert-warning' }} mb-0">
                <div class="row">
                    <div class="col-sm-6">
                        <p class="mb-1"><strong>Invoice #:</strong> {{ $lpo->supplierInvoice->invoice_number }}</p>
                        <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($lpo->supplierInvoice->invoice_date)->format('d M Y') }}</p>
                        <p class="mb-1"><strong>Billed Amount:</strong>
                            {{ currency()->symbol ?? 'KES' }} {{ number_format($lpo->supplierInvoice->invoice_amount, 2) }}
                        </p>
                    </div>
                    <div class="col-sm-6 text-sm-right">
                        @if($lpo->supplierInvoice->status === 'matched')
                            <span class="badge bg-success p-2 d-inline-block mb-2">
                                <i class="fas fa-check-double"></i> 3-Way Match — Posted to AP Ledger
                            </span>
                        @else
                            <span class="badge bg-warning p-2 d-inline-block mb-2">
                                <i class="fas fa-exclamation-triangle"></i> Discrepancy — Under Review
                            </span>
                        @endif
                        @if($lpo->supplierInvoice->notes)
                            <p class="mb-0"><small class="text-muted">{{ $lpo->supplierInvoice->notes }}</small></p>
                        @endif
                    </div>
                </div>
            </div>

        @elseif($lpo->status === 'goods_received')
            {{-- Goods received — ready for invoice match --}}
            @can('lpo_invoice_match')
            <form action="{{ route('backend.admin.lpo.store-invoice', $lpo->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Supplier Invoice Number <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_number" class="form-control"
                                   placeholder="e.g. SINV-0099" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control"
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Billed Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="invoice_amount" class="form-control"
                                   value="{{ $lpo->total_amount }}" required>
                            <small class="form-text text-muted">
                                LPO total: {{ number_format($lpo->total_amount, 2) }}
                            </small>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn bg-gradient-success w-100">
                                <i class="fas fa-check-double"></i> Match &amp; Post
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Notes <small class="text-muted">(optional)</small></label>
                    <input type="text" name="notes" class="form-control"
                           placeholder="Reference, discrepancy notes…">
                </div>
            </form>
            @endcan

        @else
            <p class="text-muted mb-0">
                <small><i class="fas fa-lock mr-1"></i>
                Invoice matching is available once goods have been received against this LPO.
                </small>
            </p>
        @endif

    </div>{{-- /card-body --}}
</div>{{-- /card --}}

{{-- ── GRN Modal (same logic as index, scoped to this LPO) ────── --}}
@can('grn_receive')
@if(in_array($lpo->status, ['issued','goods_received']))
<div class="modal fade" id="grnModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <form id="grnForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-truck-loading mr-1"></i>
                        Receive Goods — {{ $lpo->lpo_number }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Enter the quantity received per line. Set to <strong>0</strong> to skip.
                        Partial deliveries are supported.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-right">Ordered</th>
                                    <th class="text-right">Received</th>
                                    <th class="text-right">Remaining</th>
                                    <th style="width:130px">Qty Receiving <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lpo->items as $i => $item)
                                @php
                                    $recv = $item->grnItems->sum('qty_received');
                                    $rem  = $item->qty_ordered - $recv;
                                @endphp
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td class="text-right">{{ $item->qty_ordered }}</td>
                                    <td class="text-right {{ $recv > 0 ? 'text-success' : 'text-muted' }}">{{ $recv }}</td>
                                    <td class="text-right {{ $rem > 0 ? 'text-warning font-weight-bold' : 'text-muted' }}">
                                        {{ $rem }}
                                    </td>
                                    <td>
                                        <input type="hidden"
                                               name="grn_items[{{ $i }}][lpo_item_id]"
                                               value="{{ $item->id }}">
                                        <input type="number"
                                               name="grn_items[{{ $i }}][qty_received]"
                                               class="form-control form-control-sm"
                                               value="{{ $rem > 0 ? $rem : 0 }}"
                                               min="0" max="{{ $rem }}"
                                               {{ $rem <= 0 ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group mb-0 mt-2">
                        <label>Delivery Notes <small class="text-muted">(optional)</small></label>
                        <input type="text" name="grn_notes" class="form-control"
                               placeholder="e.g. Partial delivery, batch ref, driver name…">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="grnSubmitBtn">
                        <i class="fas fa-box-open"></i> Log GRN &amp; Update Stock
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endcan
@endsection

@push('script')
<script>
(function () {
    'use strict';

    // ── Open GRN modal ────────────────────────────────────────────
    $('#openGrnBtn').on('click', function () {
        $('#grnModal').modal('show');
    });

    // ── Submit GRN via AJAX so we stay on the same page ───────────
    $('#grnForm').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#grnSubmitBtn').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Saving…');

        $.ajax({
            url:  "{{ route('backend.admin.lpo.store-grn', $lpo->id) }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#grnModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'GRN Recorded',
                    text:  res.message,
                    timer: 2500,
                    showConfirmButton: false,
                }).then(function () {
                    window.location.reload();
                });
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Could not record GRN.';
                Swal.fire('Error', msg, 'error');
                btn.prop('disabled', false)
                   .html('<i class="fas fa-box-open"></i> Log GRN &amp; Update Stock');
            }
        });
    });
}());
</script>
@endpush
