@extends('backend.master')
@section('title', 'New LPO Requisition')

@section('content')
<div class="card">
    <div class="mt-n5 mb-3 d-flex justify-content-between align-items-center">
        <a href="{{ route('backend.admin.lpo.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to LPO List
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mx-3">
            <i class="fas fa-exclamation-triangle"></i>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <form action="{{ route('backend.admin.lpo.store') }}" method="POST" id="lpoForm">
        @csrf

        {{-- ── Header ─────────────────────────────────────────── --}}
        <div class="card-body p-2 p-md-4 pt-2">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-control select2" required style="width:100%">
                            <option value="">— Select Supplier —</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}{{ $s->phone ? ' · ' . $s->phone : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Notes / Justification</label>
                        <input type="text" name="notes" class="form-control"
                               placeholder="Internal requisition notes…"
                               value="{{ old('notes') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group w-100">
                        <label>Date</label>
                        <input type="text" class="form-control" value="{{ now()->format('d M Y') }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Line Items ───────────────────────────────────────── --}}
        <div class="card-body p-2 p-md-4 pt-0">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-list mr-1"></i> Order Line Items</h6>
                <button type="button" id="addRowBtn" class="btn bg-gradient-info btn-sm">
                    <i class="fas fa-plus"></i> Add Row
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="itemsTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:42%">Product <span class="text-danger">*</span></th>
                            <th style="width:14%">Qty <span class="text-danger">*</span></th>
                            <th style="width:18%">Unit Cost <span class="text-danger">*</span></th>
                            <th style="width:18%" class="text-right">Line Total</th>
                            <th style="width:8%" class="text-center">Remove</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        {{-- initial row injected by JS on page load --}}
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <td colspan="3" class="text-right font-weight-bold">Grand Total</td>
                            <td class="text-right font-weight-bold text-success" id="grandTotal">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ── Footer Actions ───────────────────────────────────── --}}
        <div class="card-footer d-flex justify-content-end">
            <a href="{{ route('backend.admin.lpo.index') }}" class="btn btn-secondary mr-2">Cancel</a>
            <button type="submit" class="btn bg-gradient-primary">
                <i class="fas fa-paper-plane"></i> Issue LPO Requisition
            </button>
        </div>
    </form>
</div>
@endsection

@push('script')
<script>
(function () {
    'use strict';

    // ── Product data from server ──────────────────────────────────
    var products = @json($productData);

    var rowIndex = 0;

    // Build one <option> list string (reused for every new row)
    var productOptions = '<option value="">— Select Product —</option>';
    products.forEach(function (p) {
        productOptions += '<option value="' + p.id + '" data-cost="' + p.purchase_price + '">'
                        + p.name + ' (Last cost: ' + parseFloat(p.purchase_price).toFixed(2) + ')'
                        + '</option>';
    });

    function makeRow(idx) {
        return '<tr data-row="' + idx + '">'
            + '<td>'
            +   '<select name="items[' + idx + '][product_id]" class="form-control form-control-sm product-select" required>'
            +   productOptions
            +   '</select>'
            + '</td>'
            + '<td>'
            +   '<input type="number" name="items[' + idx + '][qty_ordered]"'
            +   ' class="form-control form-control-sm qty-input" value="1" min="1" required>'
            + '</td>'
            + '<td>'
            +   '<input type="number" name="items[' + idx + '][unit_cost]"'
            +   ' class="form-control form-control-sm cost-input" step="0.01" min="0.01" placeholder="0.00" required>'
            + '</td>'
            + '<td class="text-right align-middle line-total font-weight-bold text-success">0.00</td>'
            + '<td class="text-center align-middle">'
            +   '<button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>'
            + '</td>'
            + '</tr>';
    }

    function addRow() {
        $('#itemsBody').append(makeRow(rowIndex++));
        // Reinitialise select2 on the new row's select
        $('#itemsBody tr:last-child .product-select').select2({ width: '100%' });
    }

    function recalc() {
        var grand = 0;
        $('#itemsBody tr').each(function () {
            var qty  = parseFloat($(this).find('.qty-input').val())  || 0;
            var cost = parseFloat($(this).find('.cost-input').val()) || 0;
            var line = qty * cost;
            grand += line;
            $(this).find('.line-total').text(line.toFixed(2));
        });
        $('#grandTotal').text(grand.toFixed(2));
    }

    // ── Auto-fill unit cost when product selected ─────────────────
    $(document).on('change', '.product-select', function () {
        var selected = $(this).find('option:selected');
        var cost = parseFloat(selected.data('cost')) || 0;
        $(this).closest('tr').find('.cost-input').val(cost > 0 ? cost.toFixed(2) : '');
        recalc();
    });

    // ── Recalculate on qty / cost change ─────────────────────────
    $(document).on('input', '.qty-input, .cost-input', recalc);

    // ── Remove row ────────────────────────────────────────────────
    $(document).on('click', '.remove-row', function () {
        if ($('#itemsBody tr').length > 1) {
            $(this).closest('tr').remove();
            recalc();
        } else {
            toastr.warning('At least one item line is required.');
        }
    });

    // ── Add row button ────────────────────────────────────────────
    $('#addRowBtn').on('click', addRow);

    // ── Form submit guard ─────────────────────────────────────────
    $('#lpoForm').on('submit', function () {
        var valid = true;
        $('#itemsBody tr').each(function () {
            var prod = $(this).find('.product-select').val();
            var qty  = parseFloat($(this).find('.qty-input').val());
            var cost = parseFloat($(this).find('.cost-input').val());
            if (!prod || qty < 1 || cost <= 0) {
                valid = false;
            }
        });
        if (!valid) {
            toastr.error('Please complete all item rows before submitting.');
            return false;
        }
    });

    // ── Boot: one initial row ─────────────────────────────────────
    $(function () {
        addRow();
    });
}());
</script>
@endpush
