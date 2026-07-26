@extends('backend.master')
@section('title', 'Chart of Accounts')

@section('content')
<div class="card">
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
                                <th>Normal Balance</th>
                                <th class="text-right">Current Balance</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accounts as $account)
                            <tr>
                                <td><strong>{{ $account->code }}</strong></td>
                                <td>{{ $account->name }}</td>
                                <td><span class="badge bg-info">{{ strtoupper($account->type) }}</span></td>
                                <td><span class="badge bg-secondary">{{ strtoupper($account->normal_balance) }}</span></td>
                                <td class="text-right">
                                    <strong class="{{ $account->balance < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($account->balance, 2) }}
                                    </strong>
                                </td>
                                <td><small class="text-muted">{{ $account->description }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
