@extends('backend.master')

@section('title', 'Products')

@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-box"></i>
    </div>
    <div style="flex:1">
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Products</h2>
        <p style="margin:0;font-size:12px;color:#999">Manage your product catalog</p>
    </div>
    @can('product_create')
    <a href="{{ route('backend.admin.products.create') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:linear-gradient(135deg,#059669,#10b981);color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none">
        <i class="fas fa-plus-circle"></i> Add Product
    </a>
    @endcan
</div>

@if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;gap:10px">
        <i class="fas fa-check-circle" style="color:#16a34a"></i>
        <span style="font-size:13px;font-weight:500;color:#166534">{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;color:#16a34a;cursor:pointer;font-size:16px">&times;</button>
    </div>
@endif

<div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;overflow:hidden">
    <div style="padding:16px 20px 8px">
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">All Products</h6>
    </div>
    <div style="padding:0 8px 8px">
        <table id="datatables" class="table table-hover" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">#</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Image</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Name</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Price</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Stock</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Created</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Status</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;text-align:center">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('style')
<style>
    .prod-thumb {
        width: 48px; height: 48px; object-fit: cover; border-radius: 8px;
        border: 1px solid #e8e8e8; background: #f9f9f9;
    }
    .prod-badge-active { display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#dcfce7;color:#166534; }
    .prod-badge-inactive { display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#fef2f2;color:#991b1b; }
    .prod-action-edit {
        display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:11px;font-weight:600;color:#374151;background:#fff;text-decoration:none;transition:all 0.15s;
    }
    .prod-action-edit:hover { background:#f3f4f6;border-color:#9ca3af;text-decoration:none;color:#111827; }
    .prod-action-purchase {
        display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:11px;font-weight:600;color:#374151;background:#fff;text-decoration:none;transition:all 0.15s;
    }
    .prod-action-purchase:hover { background:#fff7ed;border-color:#fb923c;color:#c2410c;text-decoration:none; }
</style>
@endpush

@push('script')
<script>
$(function() {
    var table = $('#datatables').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        order: [[5, 'desc']],
        ajax: {
            url: "{{ route('backend.admin.products.index') }}"
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'price', name: 'price', className: 'text-right' },
            { data: 'quantity', name: 'quantity' },
            { data: 'created_at', name: 'created_at' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ]
    });
});
</script>
@endpush
