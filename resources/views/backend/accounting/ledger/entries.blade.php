@extends('backend.master')
@section('title', ' ')

@section('content')
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#16a085,#1abc9c);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-book"></i>
    </div>
    <div>
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">General Ledger</h2>
        <p style="margin:0;font-size:12px;color:#999">Double-entry journal postings audit trail</p>
    </div>
</div>

<div class="filter-bar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:16px;padding:12px 16px;background:#fff;border:1px solid #e8e8e8;border-radius:12px">
    <label style="font-size:13px;font-weight:600">Status:</label>
    <select id="filterStatus" class="form-control form-control-sm" style="width:160px;border-radius:8px">
        <option value="">All</option>
        <option value="posted">Posted</option>
        <option value="reversed">Reversed</option>
    </select>

    <label style="font-size:13px;font-weight:600">Date From:</label>
    <input type="date" id="filterFrom" class="form-control form-control-sm" style="width:150px;border-radius:8px">

    <label style="font-size:13px;font-weight:600">Date To:</label>
    <input type="date" id="filterTo" class="form-control form-control-sm" style="width:150px;border-radius:8px">

    <button id="applyFilter" class="btn btn-sm" style="background:#2d2d2d;color:#fff;border-radius:8px;padding:6px 16px;font-weight:600">
        Apply
    </button>
    <button id="resetFilter" class="btn btn-sm" style="background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:8px;padding:6px 12px;font-weight:600">
        Reset
    </button>
</div>

<div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;overflow:hidden">
    <div style="padding:16px 20px 8px">
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">Journal Entries</h6>
    </div>
    <div style="padding:0 8px 8px">
        <table id="journalEntriesTable" class="table table-hover" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">#</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Entry Number</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Date</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Narration</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Posted By</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Status</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-center">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- Journal Lines Detail Modal --}}
<div class="modal fade" id="linesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:14px;overflow:hidden">
            <div class="modal-header" style="background:linear-gradient(135deg,#16a085,#1abc9c);color:#fff;padding:14px 20px">
                <h5 class="modal-title" style="font-size:15px;font-weight:700">
                    <i class="fas fa-list mr-1"></i> Journal Entry Lines
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity:0.8"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="padding:20px" id="linesModalBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="mt-2 text-muted" style="font-size:12px">Loading entry lines...</p>
                </div>
            </div>
            <div class="modal-footer" style="padding:12px 20px;border-top:1px solid #f0f0f0">
                <button type="button" class="btn btn-sm" style="background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:8px" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
(function () {
    'use strict';

    var table = $('#journalEntriesTable').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        order: [[2, 'desc']],
        ajax: {
            url: "{{ route('backend.admin.accounting.ledger.entries') }}",
            data: function (d) {
                d.status = $('#filterStatus').val();
                d.from   = $('#filterFrom').val();
                d.to     = $('#filterTo').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'entry_number', name: 'entry_number' },
            { data: 'date', name: 'entry_date' },
            { data: 'narration', name: 'narration' },
            { data: 'posted_by', name: 'posted_by', orderable: false },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ]
    });

    $('#applyFilter').on('click', function () { table.ajax.reload(); });

    $('#resetFilter').on('click', function () {
        $('#filterStatus').val('');
        $('#filterFrom').val('');
        $('#filterTo').val('');
        table.ajax.reload();
    });

    $(document).on('click', '.view-lines-btn', function () {
        var btn = $(this);
        var entryId = btn.data('id');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $('#linesModalBody').html(
            '<div class="text-center py-4">' +
            '<i class="fas fa-spinner fa-spin fa-2x text-muted"></i>' +
            '<p class="mt-2 text-muted" style="font-size:12px">Loading entry lines...</p>' +
            '</div>'
        );
        $('#linesModal').modal('show');

        $.ajax({
            url: "{{ url('admin/accounting/ledger/entry-lines') }}/" + entryId,
            type: 'GET',
            success: function(data) {
                var rows = '';
                var totalDebit = 0, totalCredit = 0;
                $.each(data, function(i, l) {
                    var dr = parseFloat(l.debit) || 0;
                    var cr = parseFloat(l.credit) || 0;
                    totalDebit += dr;
                    totalCredit += cr;
                    rows += '<tr>'
                        + '<td><strong>' + l.account_code + '</strong></td>'
                        + '<td>' + l.account_name + '</td>'
                        + '<td class="text-right text-success">' + (dr > 0 ? dr.toFixed(2) : '-') + '</td>'
                        + '<td class="text-right text-danger">' + (cr > 0 ? cr.toFixed(2) : '-') + '</td>'
                        + '<td><small class="text-muted">' + (l.memo || '') + '</small></td>'
                        + '</tr>';
                });
                rows += '<tr style="background:#f5f5f5">'
                    + '<td colspan="2" class="text-right"><strong>Totals</strong></td>'
                    + '<td class="text-right text-success"><strong>' + totalDebit.toFixed(2) + '</strong></td>'
                    + '<td class="text-right text-danger"><strong>' + totalCredit.toFixed(2) + '</strong></td>'
                    + '<td></td></tr>';
                $('#linesModalBody').html(
                    '<div class="table-responsive">' +
                    '<table class="table table-sm" style="border:1px solid #e8e8e8;border-radius:8px;overflow:hidden;font-size:12px">' +
                    '<thead><tr style="background:#f5f5f5">' +
                    '<th style="padding:8px 12px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px">Code</th>' +
                    '<th style="padding:8px 12px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px">Account</th>' +
                    '<th style="padding:8px 12px;text-align:right;font-weight:700;text-transform:uppercase;letter-spacing:0.3px">Debit</th>' +
                    '<th style="padding:8px 12px;text-align:right;font-weight:700;text-transform:uppercase;letter-spacing:0.3px">Credit</th>' +
                    '<th style="padding:8px 12px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px">Memo</th>' +
                    '</tr></thead><tbody>' + rows + '</tbody></table></div>'
                );
                btn.prop('disabled', false).html('<i class="fas fa-list"></i> View Lines');
            },
            error: function() {
                $('#linesModalBody').html(
                    '<div class="alert alert-danger" style="border-radius:8px">' +
                    '<i class="fas fa-exclamation-triangle mr-1"></i> Could not load entry lines. Please try again.' +
                    '</div>'
                );
                btn.prop('disabled', false).html('<i class="fas fa-list"></i> View Lines');
            }
        });
    });
}());
</script>
@endpush
