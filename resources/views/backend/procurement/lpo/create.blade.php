@extends('backend.master')
@section('title', ' ')

@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#d35400,#e67e22);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-file-contract"></i>
    </div>
    <div style="flex:1">
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">New LPO Requisition</h2>
        <p style="margin:0;font-size:12px;color:#999">Create a Local Purchase Order to request stock from a supplier</p>
    </div>
    <a href="{{ route('backend.admin.lpo.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none">
        <i class="fas fa-arrow-left"></i> Back to LPO List
    </a>
</div>

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:14px 18px;margin-bottom:16px">
    <div style="display:flex;align-items:flex-start;gap:10px">
        <i class="fas fa-exclamation-circle" style="color:#dc2626;margin-top:2px"></i>
        <div>
            <div style="font-size:13px;font-weight:600;color:#dc2626;margin-bottom:4px">Please fix the following errors:</div>
            <ul style="margin:0;padding-left:18px;font-size:12px;color:#991b1b">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif

<form action="{{ route('backend.admin.lpo.store') }}" method="POST" id="lpoForm">
    @csrf

    {{-- ── Header Info ────────────────────────────────────────── --}}
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;margin-bottom:16px">
        <div style="padding:16px 20px 8px">
            <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">Requisition Details</h6>
        </div>
        <div style="padding:0 20px 16px">
            <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:16px;align-items:end">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">
                        Supplier <span style="color:#dc2626">*</span>
                    </label>
                    <select name="supplier_id" class="form-control select2" required style="width:100%;border-radius:8px;font-size:13px">
                        <option value="">— Select Supplier —</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}{{ $s->phone ? ' · ' . $s->phone : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Notes / Justification</label>
                    <input type="text" name="notes" class="form-control"
                           placeholder="Internal requisition notes…"
                           value="{{ old('notes') }}"
                           style="border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Date</label>
                    <input type="text" class="form-control" value="{{ now()->format('d M Y') }}" readonly
                           style="border-radius:8px;font-size:13px;width:140px;background:#f9f9f9">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Line Items ───────────────────────────────────────── --}}
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;overflow:visible;margin-bottom:16px">
        <div style="padding:16px 20px 8px;display:flex;justify-content:space-between;align-items:center">
            <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">
                <i class="fas fa-list" style="margin-right:4px"></i> Order Line Items
            </h6>
            <button type="button" id="addRowBtn"
                    style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:linear-gradient(135deg,#0ea5e9,#38bdf8);color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer">
                <i class="fas fa-plus"></i> Add Row
            </button>
        </div>
        <div style="padding:0 8px 8px">
            <div style="overflow-x:auto">
                <table class="table table-hover" style="margin:0" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;width:42%">Product <span style="color:#dc2626">*</span></th>
                            <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;width:14%">Qty <span style="color:#dc2626">*</span></th>
                            <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;width:18%">Unit Cost <span style="color:#dc2626">*</span></th>
                            <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;width:18%" class="text-right">Line Total</th>
                            <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;width:8%" class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="padding:12px;border-top:1px solid #e8e8e8;text-align:right;font-weight:700;color:#303030;font-size:14px">Grand Total</td>
                            <td style="padding:12px;border-top:1px solid #e8e8e8;text-align:right;font-weight:800;font-size:16px;color:#d35400" id="grandTotal">KES 0.00</td>
                            <td style="border-top:1px solid #e8e8e8"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Footer Actions ───────────────────────────────────── --}}
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-bottom:24px">
        <a href="{{ route('backend.admin.lpo.index') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none">
            Cancel
        </a>
        <button type="submit"
                style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:linear-gradient(135deg,#d35400,#e67e22);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer">
            <i class="fas fa-paper-plane"></i> Issue LPO Requisition
        </button>
    </div>
</form>
@endsection

@push('script')
<script>
(function () {
    'use strict';

    var products = @json($productData);
    var rowIndex = 0;

    var productOptions = '<option value="">— Select Product —</option>';
    products.forEach(function (p) {
        productOptions += '<option value="' + p.id + '" data-cost="' + p.purchase_price + '">'
                        + p.name + ' (Last cost: ' + parseFloat(p.purchase_price).toFixed(2) + ')'
                        + '</option>';
    });

    function makeRow(idx) {
        return '<tr data-row="' + idx + '">'
            + '<td style="padding:8px 12px">'
            +   '<select name="items[' + idx + '][product_id]" class="form-control product-select" required style="border-radius:8px;font-size:13px">'
            +   productOptions
            +   '</select>'
            + '</td>'
            + '<td style="padding:8px 12px">'
            +   '<input type="number" name="items[' + idx + '][qty_ordered]"'
            +   ' class="form-control qty-input" value="1" min="1" required style="width:80px;border-radius:8px;font-size:13px;text-align:center;display:inline-block">'
            + '</td>'
            + '<td style="padding:8px 12px">'
            +   '<input type="number" name="items[' + idx + '][unit_cost]"'
            +   ' class="form-control cost-input" step="0.01" min="0.01" placeholder="0.00" required style="width:120px;border-radius:8px;font-size:13px;text-align:right;display:inline-block">'
            + '</td>'
            + '<td style="padding:8px 12px;text-align:right;font-weight:700;color:#d35400" class="line-total">0.00</td>'
            + '<td style="padding:8px 12px;text-align:center">'
            +   '<button type="button" class="remove-row" title="Remove" style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;background:#fee2e2;color:#dc2626;border:none;cursor:pointer;font-size:11px"><i class="fas fa-trash-alt"></i></button>'
            + '</td>'
            + '</tr>';
    }

    function addRow() {
        $('#itemsBody').append(makeRow(rowIndex++));
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
        $('#grandTotal').text('KES ' + grand.toFixed(2));
    }

    $(document).on('change', '.product-select', function () {
        var selected = $(this).find('option:selected');
        var cost = parseFloat(selected.data('cost')) || 0;
        $(this).closest('tr').find('.cost-input').val(cost > 0 ? cost.toFixed(2) : '');
        recalc();
    });

    $(document).on('input', '.qty-input, .cost-input', recalc);

    $(document).on('click', '.remove-row', function () {
        if ($('#itemsBody tr').length > 1) {
            $(this).closest('tr').remove();
            recalc();
        } else {
            toastr.warning('At least one item line is required.');
        }
    });

    $('#addRowBtn').on('click', addRow);

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

    $(function () {
        addRow();
    });
}());
</script>
@endpush
