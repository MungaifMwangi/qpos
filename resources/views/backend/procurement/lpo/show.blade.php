@extends('backend.master')
@section('title', 'LPO Details & 3-Way Match')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-file-invoice"></i> LPO #{{ $lpo->lpo_number }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.admin.lpo.index') }}">LPO</a></li>
                        <li class="breadcrumb-item active">Details</li>
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

            <div class="row">
                <!-- Left: LPO Info & Items -->
                <div class="col-md-7">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Order Overview</h3>
                            <div class="card-tools">
                                <span class="badge bg-info p-2">{{ strtoupper($lpo->status) }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p><strong>Supplier:</strong> {{ $lpo->supplier->name }} ({{ $lpo->supplier->phone ?? 'N/A' }})</p>
                            <p><strong>Issued At:</strong> {{ $lpo->issued_at }}</p>
                            <p><strong>Total Amount:</strong> <strong class="text-success">KES {{ number_format($lpo->total_amount, 2) }}</strong></p>

                            <h5 class="mt-4">Ordered Items</h5>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty Ordered</th>
                                        <th>Unit Cost</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lpo->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->qty_ordered }}</td>
                                        <td>KES {{ number_format($item->unit_cost, 2) }}</td>
                                        <td>KES {{ number_format($item->total_cost, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right: GRN Receipt & 3-Way Invoice Match -->
                <div class="col-md-5">
                    <!-- Stage 3: Log Goods Received Note (GRN) -->
                    @if(in_array($lpo->status, ['issued', 'goods_received']))
                    <div class="card card-warning card-outline mb-4">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-truck-loading"></i> Goods Receipt Note (GRN)</h3>
                        </div>
                        <form action="{{ route('backend.admin.lpo.store-grn', $lpo->id) }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <p class="text-muted">Record physical goods received into inventory stock.</p>
                                @foreach($lpo->items as $item)
                                <div class="form-group">
                                    <label>{{ $item->product->name }} (Ordered: {{ $item->qty_ordered }})</label>
                                    <input type="hidden" name="grn_items[{{ $loop->index }}][lpo_item_id]" value="{{ $item->id }}">
                                    <input type="number" min="0" max="{{ $item->qty_ordered }}" name="grn_items[{{ $loop->index }}][qty_received]" class="form-control" value="{{ $item->qty_ordered }}" required>
                                </div>
                                @endforeach
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning w-100"><i class="fas fa-box-open"></i> Log Goods Received & Update Stock</button>
                            </div>
                        </form>
                    </div>
                    @endif

                    <!-- Stage 4 & 5: 3-Way Match & Post to Creditors AP -->
                    @if($lpo->status === 'goods_received' || $lpo->status === 'invoice_matched' || $lpo->status === 'posted')
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-file-signature"></i> 3-Way Supplier Invoice Match</h3>
                        </div>
                        @if($lpo->supplierInvoice)
                            <div class="card-body">
                                <div class="alert alert-success">
                                    <h5><i class="fas fa-check-circle"></i> Invoice Matched</h5>
                                    <p>
                                        <strong>Invoice #:</strong> {{ $lpo->supplierInvoice->invoice_number }}<br>
                                        <strong>Date:</strong> {{ $lpo->supplierInvoice->invoice_date }}<br>
                                        <strong>Amount:</strong> KES {{ number_format($lpo->supplierInvoice->invoice_amount, 2) }}<br>
                                        <strong>Status:</strong> <span class="badge bg-success">{{ strtoupper($lpo->supplierInvoice->status) }}</span>
                                    </p>
                                </div>
                            </div>
                        @else
                            <form action="{{ route('backend.admin.lpo.store-invoice', $lpo->id) }}" method="POST">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Supplier Invoice Number <span class="text-danger">*</span></label>
                                        <input type="text" name="invoice_number" class="form-control" required placeholder="e.g. SINV-9988">
                                    </div>
                                    <div class="form-group">
                                        <label>Invoice Date <span class="text-danger">*</span></label>
                                        <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Billed Amount (KES) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="invoice_amount" class="form-control" value="{{ $lpo->total_amount }}" required>
                                        <small class="form-text text-muted">LPO Total: KES {{ number_format($lpo->total_amount, 2) }}</small>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-check-double"></i> Perform 3-Way Match & Post to AP Ledger</button>
                                </div>
                            </form>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
