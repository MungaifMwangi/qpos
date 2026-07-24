@extends('backend.master')

@section('title', 'Create Customer')

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('backend.admin.customers.store') }}" method="post" class="accountForm"
      enctype="multipart/form-data">
      @csrf
      <div class="card-body">
        <div class="row">
          <div class="mb-3 col-md-6">
            <label for="title" class="form-label">
              Name
              <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" placeholder="Enter title" name="name"
              value="{{ old('name') }}" required>
          </div>
          <div class="mb-3 col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" placeholder="Enter email" name="email" value="{{ old('email') }}">
          </div>
          <div class="mb-3 col-md-6">
            <label class="form-label">Credit Limit (KES)</label>
            <input type="number" step="0.01" min="0" class="form-control" placeholder="0.00" name="credit_limit" value="{{ old('credit_limit', 0) }}">
          </div>
          <div class="mb-3 col-md-6">
            <label class="form-label">Credit Terms (Days)</label>
            <input type="number" min="0" class="form-control" name="credit_terms_days" value="{{ old('credit_terms_days', 30) }}">
          </div>
          <div class="mb-3 col-md-6">
            <label for="title" class="form-label">
              Phone
              <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" placeholder="Enter phone" name="phone"
              value="{{ old('phone') }}" required>
          </div>
          <div class="mb-3 col-md-6">
            <label for="title" class="form-label">
              Address
            </label>
            <input type="text" class="form-control" placeholder="Enter Address" name="address"
              value="{{ old('Address') }}">
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <button type="submit" class="btn bg-gradient-primary">Create</button>
          </div>
        </div>
      </div>
      <!-- /.card-body -->
    </form>
  </div>
</div>
@endsection
@push('script')
<script>
</script>
@endpush
