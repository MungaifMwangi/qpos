@extends('backend.master')
@section('title', 'Trial Balance')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-balance-scale"></i> Trial Balance Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Trial Balance</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">General Ledger Trial Balance</h3>
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Account Code</th>
                                    <th>Account Name</th>
                                    <th>Type</th>
                                    <th class="text-right">Debit (KES)</th>
                                    <th class="text-right">Credit (KES)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report as $row)
                                <tr>
                                    <td><strong>{{ $row['code'] }}</strong></td>
                                    <td>{{ $row['name'] }}</td>
                                    <td><span class="badge bg-secondary">{{ $row['type'] }}</span></td>
                                    <td class="text-right">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '-' }}</td>
                                    <td class="text-right">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="3" class="text-right"><strong>TOTALS</strong></td>
                                    <td class="text-right text-success"><strong>{{ number_format($totalDebit, 2) }}</strong></td>
                                    <td class="text-right text-success"><strong>{{ number_format($totalCredit, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        @if(abs($totalDebit - $totalCredit) < 0.01)
                                            <span class="badge bg-success p-2"><i class="fas fa-check-circle"></i> TRIAL BALANCE IS BALANCED (Debits = Credits)</span>
                                        @else
                                            <span class="badge bg-danger p-2"><i class="fas fa-exclamation-triangle"></i> UNBALANCED (Difference: KES {{ number_format(abs($totalDebit - $totalCredit), 2) }})</span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
