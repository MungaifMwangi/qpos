@extends('backend.master')
@section('title', 'New LPO Requisition')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-plus-circle"></i> New Local Purchase Order Requisition</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.admin.lpo.index') }}">LPO</a></li>
                        <li class="breadcrumb-item active">New LPO</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <form action="{{ route('backend.admin.lpo.store') }}" method="POST">
                @csrf
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Requisition Header & Supplier</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Supplier <span class="text-danger">*</span></label>
                                    <select name="supplier_id" class="form-control select2" required>
                                        <option value="">-- Choose Supplier --</option>
                                        @foreach($suppliers as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->phone ?? 'No Phone' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Notes / Justification</label>
                                    <input type="text" name="notes" class="form-control" placeholder="Internal requisition notes...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-secondary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Order Line Items</h3>
                        <button type="button" id="addRow" class="btn btn-light btn-sm ml-auto"><i class="fas fa-plus"></i> Add Item Row</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Product <span class="text-danger">*</span></th>
                                        <th>Quantity <span class="text-danger">*</span></th>
                                        <th>Unit Cost (KES) <span class="text-danger">*</span></th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="items[0][product_id]" class="form-control select2" required>
                                                <option value="">-- Select Product --</option>
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}">{{ $p->name }} (Cost: KES {{ $p->purchase_price }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" min="1" name="items[0][qty_ordered]" class="form-control" value="1" required></td>
                                        <td><input type="number" step="0.01" min="0" name="items[0][unit_cost]" class="form-control" placeholder="0.00" required></td>
                                        <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('backend.admin.lpo.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Issue LPO Requisition</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    let rowIndex = 1;
    $('#addRow').click(function() {
        let html = `
            <tr>
                <td>
                    <select name="items[${rowIndex}][product_id]" class="form-control" required>
                        <option value="">-- Select Product --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Cost: KES {{ $p->purchase_price }})</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" min="1" name="items[${rowIndex}][qty_ordered]" class="form-control" value="1" required></td>
                <td><input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_cost]" class="form-control" placeholder="0.00" required></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        $('#itemsTable tbody').append(html);
        rowIndex++;
    });

    $(document).on('click', '.remove-row', function() {
        if ($('#itemsTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
        }
    });
</script>
@endpush
