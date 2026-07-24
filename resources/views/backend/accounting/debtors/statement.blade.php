@extends('backend.master')
@section('title', 'Customer Statement')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-file-invoice-dollar"></i> Customer Statement</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.admin.debtors.index') }}">Debtors</a></li>
                        <li class="breadcrumb-item active">Statement</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Statement of Account: <strong>{{ $statement['customer']->name }}</strong></h3>
                    <button class="btn btn-secondary btn-sm ml-auto" onclick="window.print()"><i class="fas fa-print"></i> Print Statement</button>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Customer Info:</h5>
                            <p>
                                <strong>Name:</strong> {{ $statement['customer']->name }}<br>
                                <strong>Phone:</strong> {{ $statement['customer']->phone ?? 'N/A' }}<br>
                                <strong>Credit Limit:</strong> KES {{ number_format($statement['customer']->credit_limit, 2) }}
                            </p>
                        </div>
                        <div class="col-md-6 text-right">
                            <h5>Current Balance Due:</h5>
                            <h2 class="{{ $statement['current_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                KES {{ number_format($statement['current_balance'], 2) }}
                            </h2>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Type</th>
                                    <th>Notes</th>
                                    <th class="text-right">Debit (Charge)</th>
                                    <th class="text-right">Credit (Payment)</th>
                                    <th class="text-right">Running Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($statement['transactions'] as $tx)
                                <tr>
                                    <td>{{ $tx['date'] }}</td>
                                    <td><strong>{{ $tx['reference'] }}</strong></td>
                                    <td><span class="badge bg-{{ $tx['type'] === 'Invoice' ? 'primary' : 'success' }}">{{ $tx['type'] }}</span></td>
                                    <td>{{ $tx['notes'] }}</td>
                                    <td class="text-right">{{ $tx['debit'] > 0 ? number_format($tx['debit'], 2) : '-' }}</td>
                                    <td class="text-right">{{ $tx['credit'] > 0 ? number_format($tx['credit'], 2) : '-' }}</td>
                                    <td class="text-right font-weight-bold">KES {{ number_format($tx['balance'], 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No transactions recorded for this customer.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
