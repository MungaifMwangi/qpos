@extends('backend.master')

@section('title', 'Edit Expense')

@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#d35400,#e67e22);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-receipt"></i>
    </div>
    <div style="flex:1">
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Edit Expense</h2>
        <p style="margin:0;font-size:12px;color:#999">Update expense details</p>
    </div>
    <a href="{{ route('backend.admin.expenses.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none">
        <i class="fas fa-arrow-left"></i> Back to Expenses
    </a>
</div>

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:14px 18px;margin-bottom:16px">
    <div style="display:flex;align-items:flex-start;gap:10px">
        <i class="fas fa-exclamation-circle" style="color:#dc2626;margin-top:2px"></i>
        <div>
            <div style="font-size:13px;font-weight:600;color:#dc2626;margin-bottom:4px">Please fix the following errors:</div>
            <ul style="margin:0;padding-left:18px;font-size:12px;color:#991b1b">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif

<form action="{{ route('backend.admin.expenses.update', $expense->id) }}" method="post">
    @csrf
    @method('PUT')

    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;margin-bottom:16px">
        <div style="padding:16px 20px 8px">
            <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">Expense Details</h6>
        </div>
        <div style="padding:0 20px 16px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:12px">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">
                        Title <span style="color:#dc2626">*</span>
                    </label>
                    <input type="text" name="title" class="form-control"
                           placeholder="e.g. Office Rent - July"
                           value="{{ old('title', $expense->title) }}" required
                           style="border-radius:8px;font-size:14px;padding:10px 12px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">
                        Category <span style="color:#dc2626">*</span>
                    </label>
                    <select name="category" class="form-control" required
                            style="border-radius:8px;font-size:14px;padding:10px 12px">
                        <option value="">Select Category</option>
                        @foreach($categories as $key => $value)
                            <option value="{{ $key }}" {{ old('category', $expense->category) == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">
                        Amount (KES) <span style="color:#dc2626">*</span>
                    </label>
                    <input type="number" step="0.01" name="amount" class="form-control"
                           placeholder="0.00"
                           value="{{ old('amount', $expense->amount) }}" required
                           style="border-radius:8px;font-size:14px;padding:10px 12px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">
                        Expense Date <span style="color:#dc2626">*</span>
                    </label>
                    <input type="date" name="expense_date" class="form-control"
                           value="{{ old('expense_date', $expense->expense_date) }}" required
                           style="border-radius:8px;font-size:14px;padding:10px 12px">
                </div>
            </div>
            <div style="margin-top:16px">
                <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Description</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Optional notes about this expense…"
                          style="border-radius:8px;font-size:14px;padding:10px 12px;resize:vertical">{{ old('description', $expense->description) }}</textarea>
            </div>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:10px;margin-bottom:24px">
        <a href="{{ route('backend.admin.expenses.index') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none">
            Cancel
        </a>
        <button type="submit"
                style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:linear-gradient(135deg,#d35400,#e67e22);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer">
            <i class="fas fa-save"></i> Update Expense
        </button>
    </div>
</form>
@endsection
