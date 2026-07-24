@extends('backend.master')

@section('title', 'Create Expense')

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('backend.admin.expenses.store') }}" method="post" class="accountForm">
      @csrf
      <div class="card-body">
        <div class="row">
          <div class="mb-3 col-md-6">
            <label for="title" class="form-label">
              Title
              <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" placeholder="Enter title" name="title"
              value="{{ old('title') }}" required>
          </div>
          <div class="mb-3 col-md-6">
            <label for="category" class="form-label">
              Category
              <span class="text-danger">*</span>
            </label>
            <select class="form-control" name="category" required>
              <option value="">Select Category</option>
              @foreach($categories as $key => $value)
                <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $value }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3 col-md-6">
            <label for="amount" class="form-label">
              Amount ({{ currency()->symbol ?? '' }})
              <span class="text-danger">*</span>
            </label>
            <input type="number" step="0.01" class="form-control" placeholder="0.00" name="amount"
              value="{{ old('amount') }}" required>
          </div>
          <div class="mb-3 col-md-6">
            <label for="expense_date" class="form-label">
              Expense Date
              <span class="text-danger">*</span>
            </label>
            <input type="date" class="form-control" name="expense_date"
              value="{{ old('expense_date', date('Y-m-d')) }}" required>
          </div>
          <div class="mb-3 col-md-12">
            <label for="description" class="form-label">
              Description
            </label>
            <textarea class="form-control" placeholder="Enter description" name="description">{{ old('description') }}</textarea>
          </div>
        </div>
        <div class="row pt-3">
          <div class="col-md-6">
            <button type="submit" class="btn bg-gradient-primary">Create</button>
            <a href="{{ route('backend.admin.expenses.index') }}" class="btn btn-default">Cancel</a>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
