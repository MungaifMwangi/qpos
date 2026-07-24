@extends('backend.master')
@section('title', 'Chart of Accounts')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-sitemap"></i> Chart of Accounts</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Chart of Accounts</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">General Ledger Chart of Accounts</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Account Name</th>
                                    <th>Type</th>
                                    <th>Normal Balance</th>
                                    <th>Current Balance</th>
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
                                    <td>
                                        <strong class="{{ $account->balance < 0 ? 'text-danger' : 'text-success' }}">
                                            KES {{ number_format($account->balance, 2) }}
                                        </strong>
                                    </td>
                                    <td>{{ $account->description }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
