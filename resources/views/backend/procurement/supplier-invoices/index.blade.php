@extends('backend.master')

@section('title', 'Supplier Invoices')

@section('content')
<style>
    .si-kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
    .si-kpi-card {
        background: #fff; border-radius: 8px; padding: 20px 20px 18px; position: relative;
        display: flex; align-items: center; justify-content: space-between;
        border: 1.5px solid transparent; box-shadow: 0 1px 6px rgba(0,0,0,.05);
    }
    .si-kpi-card .kpi-left .kpi-label { text-transform: uppercase; font-size: 11px; letter-spacing: .6px; color: #888; font-weight: 600; margin-bottom: 4px; }
    .si-kpi-card .kpi-left .kpi-value { font-size: 24px; font-weight: 700; color: #333; }
    .si-kpi-card .kpi-icon-box { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .si-blue  { border-color: #42a5f5; }
    .si-blue  .kpi-icon-box { background: #e3f2fd; color: #1e88e5; }
    .si-cyan  { border-color: #26c6da; }
    .si-cyan  .kpi-icon-box { background: #e0f7fa; color: #00acc1; }
    .si-green { border-color: #66bb6a; }
    .si-green .kpi-icon-box { background: #e8f5e9; color: #43a047; }
    .si-red   { border-color: #ef5350; }
    .si-red   .kpi-icon-box { background: #fce4ec; color: #e53935; }
    .si-red   .kpi-left .kpi-value { color: #e53935; }

    .si-filter-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,.06); padding: 16px 20px; margin-bottom: 24px; }
    .si-filter-row { display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap; }
    .si-filter-group label { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; color: #888; letter-spacing: .5px; margin-bottom: 4px; }
    .si-filter-group select, .si-filter-group input[type="date"] {
        height: 36px; border: 1px solid #ddd; border-radius: 6px; padding: 0 10px; font-size: 13px; min-width: 160px;
    }
    .si-filter-actions { display: flex; gap: 8px; }

    .si-panel { background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,.06); overflow: hidden; }
    .si-panel-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-bottom: 1px solid #eef0f4; }
    .si-panel-header h5 { margin: 0; font-size: 15px; font-weight: 600; color: #333; }
    .si-panel-header h5 i { margin-right: 8px; }

    .btn-view-grn { width: 32px; height: 32px; border-radius: 50%; border: none; cursor: pointer; background: #00bcd4; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; transition: .2s; }
    .btn-view-grn:hover { background: #0097a7; transform: scale(1.1); }
    .btn-pay { width: 32px; height: 32px; border-radius: 50%; border: none; cursor: pointer; background: #43a047; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; transition: .2s; margin-left: 6px; }
    .btn-pay:hover { background: #2e7d32; transform: scale(1.1); }

    .badge-unpaid { background: #fce4ec; color: #c62828; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .badge-partial { background: #fff3e0; color: #e65100; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .badge-paid { background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }

    @media (max-width: 991px) { .si-kpi-row { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .si-kpi-row { grid-template-columns: 1fr; } .si-filter-row { flex-direction: column; } }
</style>

    {{-- ── Page Header ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:8px;background:#37474f;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h4 style="margin:0;font-weight:700;color:#222;">Supplier Invoices</h4>
                <p style="margin:2px 0 0;font-size:13px;color:#888;">Track supplier invoices and payment status.</p>
            </div>
        </div>
        <a href="{{ route('backend.admin.suppliers.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius:6px;">
            <i class="fas fa-arrow-left mr-1"></i> Back to Suppliers
        </a>
    </div>
    <hr style="border:none;border-top:1px solid #e0e0e0;margin-bottom:24px;">

    {{-- ── KPI Cards ── --}}
    <div class="si-kpi-row">
        <div class="si-kpi-card si-blue">
            <div class="kpi-left">
                <div class="kpi-label">Total Invoices</div>
                <div class="kpi-value" id="kpiTotalInvoices">-</div>
            </div>
            <div class="kpi-icon-box"><i class="fas fa-file-alt"></i></div>
        </div>
        <div class="si-kpi-card si-cyan">
            <div class="kpi-left">
                <div class="kpi-label">Total Amount</div>
                <div class="kpi-value" id="kpiTotalAmount">-</div>
            </div>
            <div class="kpi-icon-box"><i class="fas fa-calculator"></i></div>
        </div>
        <div class="si-kpi-card si-green">
            <div class="kpi-left">
                <div class="kpi-label">Amount Paid</div>
                <div class="kpi-value" id="kpiAmountPaid">-</div>
            </div>
            <div class="kpi-icon-box"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="si-kpi-card si-red">
            <div class="kpi-left">
                <div class="kpi-label">Outstanding</div>
                <div class="kpi-value" id="kpiOutstanding">-</div>
            </div>
            <div class="kpi-icon-box"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <div class="si-filter-card">
        <div class="si-filter-row">
            <div class="si-filter-group">
                <label>Supplier</label>
                <select id="filterSupplier">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="si-filter-group">
                <label>Payment Status</label>
                <select id="filterStatus">
                    <option value="">All Status</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partially Paid</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
            <div class="si-filter-group">
                <label>Date From</label>
                <input type="date" id="filterFrom">
            </div>
            <div class="si-filter-group">
                <label>Date To</label>
                <input type="date" id="filterTo">
            </div>
            <div class="si-filter-actions">
                <button class="btn btn-sm" style="background:#6f42c1;color:#fff;border-radius:6px;padding:6px 16px;" id="btnApply">
                    <i class="fas fa-filter mr-1"></i> Apply
                </button>
                <button class="btn btn-sm btn-secondary" style="border-radius:6px;padding:6px 16px;" id="btnReset">
                    <i class="fas fa-sync-alt mr-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    {{-- ── Table Panel ── --}}
    <div class="si-panel">
        <div class="si-panel-header">
            <h5><i class="fas fa-list" style="color:#6f42c1;"></i> Supplier Invoices</h5>
            <div>
                <label style="font-size:11px;color:#888;font-weight:600;text-transform:uppercase;margin-right:6px;">Search</label>
                <input type="text" id="tableSearch" placeholder="Search..." style="height:32px;border:1px solid #ddd;border-radius:6px;padding:0 10px;font-size:13px;width:200px;">
            </div>
        </div>
        <div style="padding:0;overflow-x:auto;">
            <table class="table" style="margin:0;font-size:13px;">
                <thead>
                    <tr style="background:#f8f9fc;">
                        <th style="font-size:11px;font-weight:600;text-transform:uppercase;color:#888;border-bottom:2px solid #eef0f4;">GRN #</th>
                        <th style="font-size:11px;font-weight:600;text-transform:uppercase;color:#888;border-bottom:2px solid #eef0f4;">DATE</th>
                        <th style="font-size:11px;font-weight:600;text-transform:uppercase;color:#888;border-bottom:2px solid #eef0f4;">SUPPLIER</th>
                        <th style="font-size:11px;font-weight:600;text-transform:uppercase;color:#888;border-bottom:2px solid #eef0f4;">INVOICE #</th>
                        <th style="font-size:11px;font-weight:600;text-transform:uppercase;color:#888;border-bottom:2px solid #eef0f4;text-align:right;">INVOICE AMOUNT</th>
                        <th style="font-size:11px;font-weight:600;text-transform:uppercase;color:#888;border-bottom:2px solid #eef0f4;text-align:right;">PAID</th>
                        <th style="font-size:11px;font-weight:600;text-transform:uppercase;color:#888;border-bottom:2px solid #eef0f4;text-align:right;">BALANCE</th>
                        <th style="font-size:11px;font-weight:600;text-transform:uppercase;color:#888;border-bottom:2px solid #eef0f4;">PAYMENT STATUS</th>
                        <th style="font-size:11px;font-weight:600;text-transform:uppercase;color:#888;border-bottom:2px solid #eef0f4;text-align:center;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="invoiceTableBody">
                    <tr><td colspan="9" class="text-center text-muted" style="padding:30px;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 20px;border-top:1px solid #eef0f4;font-size:13px;color:#888;">
            <span id="tableInfo">Showing 0 entries</span>
            <div id="tablePagination" style="display:flex;gap:4px;"></div>
        </div>
    </div>

    {{-- ── GRN Detail Modal ── --}}
    <div class="modal fade" id="grnModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:10px;">
                <div class="modal-header" style="background:#37474f;color:#fff;border-radius:10px 10px 0 0;">
                    <h6 class="modal-title"><i class="fas fa-truck mr-2"></i> GRN Details</h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="grnModalBody">
                    <p class="text-muted">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Payment Modal ── --}}
    <div class="modal fade" id="payModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:10px;">
                <div class="modal-header" style="background:#43a047;color:#fff;border-radius:10px 10px 0 0;">
                    <h6 class="modal-title"><i class="fas fa-dollar-sign mr-2"></i> Settle Invoice</h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom:4px;">Supplier: <strong id="paySupplierName"></strong></p>
                    <p style="margin-bottom:12px;">Outstanding Balance: <strong id="payBalance" style="color:#e53935;"></strong></p>
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:600;">Payment Amount (KES) <span class="text-danger">*</span></label>
                        <input type="number" id="payAmount" class="form-control" min="1" step="0.01" placeholder="Enter amount">
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:600;">Payment Method <span class="text-danger">*</span></label>
                        <select id="payMethod" class="form-control">
                            <option value="bank">Bank Transfer</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:600;">Notes</label>
                        <input type="text" id="payNotes" class="form-control" placeholder="Optional notes">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn" style="background:#43a047;color:#fff;" id="btnConfirmPay">
                        <i class="fas fa-check mr-1"></i> Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
<script>
var allInvoices = [];
var currentPage = 1;
var perPage = 10;
var searchQuery = '';
var filterSupplier = '';
var filterStatus = '';

function loadKPIs() {
    fetch('{{ route("backend.admin.supplier-invoices.kpis") }}')
        .then(function(r) { return r.json(); })
        .then(function(d) {
            document.getElementById('kpiTotalInvoices').textContent = d.total_invoices;
            document.getElementById('kpiTotalAmount').textContent = 'KES ' + d.total_amount;
            document.getElementById('kpiAmountPaid').textContent = 'KES ' + d.amount_paid;
            document.getElementById('kpiOutstanding').textContent = 'KES ' + d.outstanding;
        });
}

function loadTable() {
    var url = '{{ route("backend.admin.supplier-invoices.data") }}?';
    if (filterSupplier) url += 'supplier_id=' + filterSupplier + '&';
    if (document.getElementById('filterFrom').value) url += 'from=' + document.getElementById('filterFrom').value + '&';
    if (document.getElementById('filterTo').value) url += 'to=' + document.getElementById('filterTo').value + '&';

    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(json) {
            allInvoices = json.data || [];
            applyFilters();
        });
}

function applyFilters() {
    var rows = allInvoices;

    if (searchQuery) {
        var q = searchQuery.toLowerCase();
        rows = rows.filter(function(r) {
            return (r.grn_number || '').toLowerCase().includes(q)
                || (r.supplier_name || '').toLowerCase().includes(q)
                || (r.invoice_number || '').toLowerCase().includes(q);
        });
    }

    if (filterStatus) {
        rows = rows.filter(function(r) {
            var statusText = (r.payment_status || '').toLowerCase();
            return statusText.includes(filterStatus);
        });
    }

    var totalPages = Math.ceil(rows.length / perPage) || 1;
    if (currentPage > totalPages) currentPage = totalPages;
    var start = (currentPage - 1) * perPage;
    var pageRows = rows.slice(start, start + perPage);

    var tbody = document.getElementById('invoiceTableBody');
    if (pageRows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted" style="padding:30px;">No invoices found.</td></tr>';
    } else {
        tbody.innerHTML = pageRows.map(function(r) {
            var actionHtml = '<button class="btn-view-grn" data-grn-id="' + r.grn_id + '" data-lpo="' + r.lpo_number + '" title="View GRN"><i class="fas fa-eye"></i></button>';
            if (r.payment_status && r.payment_status.includes('Unpaid')) {
                actionHtml += ' <button class="btn-pay" data-supplier-id="' + r.supplier_id + '" data-supplier="' + r.supplier_name + '" data-balance="' + r.balance.replace(/,/g, '') + '" title="Pay Now"><i class="fas fa-dollar-sign"></i></button>';
            } else if (r.payment_status && r.payment_status.includes('Partial')) {
                actionHtml += ' <button class="btn-pay" data-supplier-id="' + r.supplier_id + '" data-supplier="' + r.supplier_name + '" data-balance="' + r.balance.replace(/,/g, '') + '" title="Pay Now"><i class="fas fa-dollar-sign"></i></button>';
            }
            return '<tr>'
                + '<td><strong>' + r.grn_number + '</strong></td>'
                + '<td>' + r.date + '</td>'
                + '<td>' + r.supplier_name + '</td>'
                + '<td>' + r.invoice_number + '</td>'
                + '<td style="text-align:right;font-weight:600;">KES ' + r.invoice_amount + '</td>'
                + '<td style="text-align:right;">KES ' + r.paid + '</td>'
                + '<td style="text-align:right;">' + (parseFloat(r.balance.replace(/,/g, '')) > 0 ? 'KES ' + r.balance : '&ndash;') + '</td>'
                + '<td>' + r.payment_status + '</td>'
                + '<td style="text-align:center;white-space:nowrap;">' + actionHtml + '</td>'
                + '</tr>';
        }).join('');
    }

    document.getElementById('tableInfo').textContent = 'Showing ' + (rows.length === 0 ? 0 : start + 1) + ' to ' + Math.min(start + perPage, rows.length) + ' of ' + rows.length + ' entries';
    renderPagination(totalPages);
}

function renderPagination(total) {
    var el = document.getElementById('tablePagination');
    if (total <= 1) { el.innerHTML = ''; return; }
    var html = '<button class="btn btn-sm btn-outline-secondary" ' + (currentPage === 1 ? 'disabled' : '') + ' onclick="goPage(' + (currentPage - 1) + ')">Previous</button>';
    for (var i = 1; i <= total; i++) {
        html += '<button class="btn btn-sm ' + (i === currentPage ? 'btn-primary' : 'btn-outline-secondary') + '" style="min-width:32px;" onclick="goPage(' + i + ')">' + i + '</button>';
    }
    html += '<button class="btn btn-sm btn-outline-secondary" ' + (currentPage === total ? 'disabled' : '') + ' onclick="goPage(' + (currentPage + 1) + ')">Next</button>';
    el.innerHTML = html;
}

function goPage(p) { currentPage = p; applyFilters(); }

document.getElementById('btnApply').addEventListener('click', function() {
    filterSupplier = document.getElementById('filterSupplier').value;
    filterStatus = document.getElementById('filterStatus').value.toLowerCase();
    currentPage = 1;
    loadTable();
});

document.getElementById('btnReset').addEventListener('click', function() {
    document.getElementById('filterSupplier').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterFrom').value = '';
    document.getElementById('filterTo').value = '';
    filterSupplier = '';
    filterStatus = '';
    searchQuery = '';
    document.getElementById('tableSearch').value = '';
    currentPage = 1;
    loadTable();
});

document.getElementById('tableSearch').addEventListener('input', function(e) {
    searchQuery = e.target.value;
    currentPage = 1;
    applyFilters();
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-view-grn')) {
        var btn = e.target.closest('.btn-view-grn');
        var grnId = btn.getAttribute('data-grn-id');
        fetch('{{ url("admin/supplier-invoices") }}/' + grnId + '/view-grn')
            .then(function(r) { return r.json(); })
            .then(function(d) {
                var html = '<table class="table table-sm" style="font-size:13px;">'
                    + '<tr><td style="font-weight:600;width:120px;">GRN #</td><td>' + d.grn_number + '</td></tr>'
                    + '<tr><td style="font-weight:600;">Date</td><td>' + d.received_date + '</td></tr>'
                    + '<tr><td style="font-weight:600;">Supplier</td><td>' + d.supplier + '</td></tr>'
                    + '<tr><td style="font-weight:600;">LPO #</td><td>' + d.lpo_number + '</td></tr>'
                    + '<tr><td style="font-weight:600;">Invoice #</td><td>' + d.invoice_number + '</td></tr>'
                    + '<tr><td style="font-weight:600;">Invoice Amount</td><td><strong>KES ' + d.invoice_amount + '</strong></td></tr>'
                    + '</table>'
                    + '<h6 style="font-weight:600;margin-top:10px;">Items Received</h6>'
                    + '<table class="table table-sm" style="font-size:13px;"><thead><tr><th>Product</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Unit Cost</th><th style="text-align:right;">Total</th></tr></thead><tbody>';
                d.items.forEach(function(item) {
                    html += '<tr><td>' + item.product + '</td><td style="text-align:right;">' + item.qty_received + '</td><td style="text-align:right;">KES ' + item.unit_cost + '</td><td style="text-align:right;">KES ' + item.line_total + '</td></tr>';
                });
                html += '</tbody></table>';
                document.getElementById('grnModalBody').innerHTML = html;
                $('#grnModal').modal('show');
            });
    }

    if (e.target.closest('.btn-pay')) {
        var btn = e.target.closest('.btn-pay');
        var supplierName = btn.getAttribute('data-supplier');
        var balance = btn.getAttribute('data-balance');
        document.getElementById('paySupplierName').textContent = supplierName;
        document.getElementById('payBalance').textContent = 'KES ' + parseFloat(balance).toLocaleString(undefined, {minimumFractionDigits:2});
        document.getElementById('payAmount').value = '';
        document.getElementById('payAmount').max = balance;
        document.getElementById('payNotes').value = '';
        document.getElementById('btnConfirmPay').setAttribute('data-supplier-id', btn.getAttribute('data-supplier-id'));
        $('#payModal').modal('show');
    }
});

document.getElementById('btnConfirmPay').addEventListener('click', function() {
    var amount = document.getElementById('payAmount').value;
    var method = document.getElementById('payMethod').value;
    var notes = document.getElementById('payNotes').value;
    var btn = this;
    if (!amount || parseFloat(amount) <= 0) {
        Swal.fire('Error', 'Please enter a valid amount.', 'error');
        return;
    }
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    fetch('{{ route("backend.admin.supplier-invoices.pay") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            supplier_id: btn.getAttribute('data-supplier-id'),
            amount: parseFloat(amount),
            payment_mode: method,
            notes: notes
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Confirm Payment';
        if (d.message && !d.message.includes('Error')) {
            $('#payModal').modal('hide');
            Swal.fire('Success', d.message, 'success');
            loadKPIs();
            loadTable();
        } else {
            Swal.fire('Error', d.message || 'Payment failed.', 'error');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Confirm Payment';
        Swal.fire('Error', 'Network error. Please try again.', 'error');
    });
});

loadKPIs();
loadTable();
</script>
@endpush
