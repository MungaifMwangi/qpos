@extends('backend.master')
@section('title', 'General Ledger Journal Entries')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-book"></i> General Ledger Journal Entries</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">General Ledger Entries</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Double-Entry Journal Postings Audit Trail</h3>
                </div>
                <div class="card-body">
                    <table id="journalEntriesTable" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Entry Number</th>
                                <th>Date</th>
                                <th>Narration</th>
                                <th>Posted By</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('#journalEntriesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('backend.admin.accounting.ledger.entries') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'entry_number', name: 'entry_number' },
                { data: 'date', name: 'entry_date' },
                { data: 'narration', name: 'narration' },
                { data: 'posted_by', name: 'user.name' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endpush
