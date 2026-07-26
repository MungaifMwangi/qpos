@extends('backend.master')
@section('title', 'General Ledger Journal Entries')

@section('content')
<div class="card">
    <div class="card-body p-2 p-md-4 pt-0">
        <div class="row g-4">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title"><i class="fas fa-book mr-1"></i> Double-Entry Journal Postings Audit Trail</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table id="journalEntriesTable" class="table table-hover table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Entry Number</th>
                                    <th>Date</th>
                                    <th>Narration</th>
                                    <th>Posted By</th>
                                    <th>Status</th>
                                    <th data-orderable="false">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Journal Lines Detail Modal --}}
<div class="modal fade" id="linesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-list mr-1"></i> Journal Entry Lines</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="linesModalBody">
                <div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        $('#journalEntriesTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            order: [[1, 'desc']],
            ajax: {
                url: "{{ route('backend.admin.accounting.ledger.entries') }}"
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'entry_number', name: 'entry_number' },
                { data: 'date', name: 'entry_date' },
                { data: 'narration', name: 'narration' },
                { data: 'posted_by', name: 'posted_by', orderable: false },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // View journal entry lines in modal
        $(document).on('click', '.view-lines-btn', function () {
            var btn = $(this);
            var entryId = btn.data('id');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            $('#linesModalBody').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
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
                    rows += '<tr class="bg-light font-weight-bold">'
                        + '<td colspan="2" class="text-right">Totals</td>'
                        + '<td class="text-right text-success">' + totalDebit.toFixed(2) + '</td>'
                        + '<td class="text-right text-danger">' + totalCredit.toFixed(2) + '</td>'
                        + '<td></td></tr>';
                    $('#linesModalBody').html(
                        '<div class="table-responsive">'
                        + '<table class="table table-sm table-bordered table-striped">'
                        + '<thead class="thead-dark"><tr>'
                        + '<th>Code</th><th>Account</th>'
                        + '<th class="text-right">Debit (KES)</th>'
                        + '<th class="text-right">Credit (KES)</th>'
                        + '<th>Memo</th></tr></thead>'
                        + '<tbody>' + rows + '</tbody>'
                        + '</table></div>'
                    );
                    btn.prop('disabled', false).html('<i class="fas fa-list"></i> View Lines');
                },
                error: function() {
                    $('#linesModalBody').html('<div class="alert alert-danger">Could not load entry lines. Please try again.</div>');
                    btn.prop('disabled', false).html('<i class="fas fa-list"></i> View Lines');
                }
            });
        });
    });
</script>
@endpush
