@extends('backend.master')
@section('title', 'Trial Balance')

@section('content')
<div class="card">
    <div class="mt-n5 mb-3 d-flex justify-content-end">
        <button class="btn bg-gradient-secondary" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
    <div class="card-body p-2 p-md-4 pt-0">
        <div class="row g-4">
            <div class="col-md-12">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Account Name</th>
                                <th>Type</th>
                                <th class="text-right">Debit {{ currency()->symbol ?? '' }}</th>
                                <th class="text-right">Credit {{ currency()->symbol ?? '' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report as $row)
                            @if($row['debit'] > 0 || $row['credit'] > 0)
                            <tr>
                                <td><strong>{{ $row['code'] }}</strong></td>
                                <td>{{ $row['name'] }}</td>
                                <td><span class="badge bg-secondary">{{ $row['type'] }}</span></td>
                                <td class="text-right text-success">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '-' }}</td>
                                <td class="text-right text-danger">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '-' }}</td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="3" class="text-right"><strong>TOTALS</strong></td>
                                <td class="text-right text-success"><strong>{{ number_format($totalDebit, 2) }}</strong></td>
                                <td class="text-right text-danger"><strong>{{ number_format($totalCredit, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-center py-2">
                                    @if(abs($totalDebit - $totalCredit) < 0.01)
                                        <span class="badge bg-success p-2">
                                            <i class="fas fa-check-circle"></i> TRIAL BALANCE IS BALANCED (Debits = Credits)
                                        </span>
                                    @else
                                        <span class="badge bg-danger p-2">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            UNBALANCED — Difference: {{ number_format(abs($totalDebit - $totalCredit), 2) }}
                                        </span>
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
@endsection
