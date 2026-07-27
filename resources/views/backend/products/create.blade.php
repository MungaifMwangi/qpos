@extends('backend.master')

@section('title', 'Add Product')

@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-box"></i>
    </div>
    <div style="flex:1">
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Add Product</h2>
        <p style="margin:0;font-size:12px;color:#999">Create a new product entry</p>
    </div>
    <a href="{{ route('backend.admin.products.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none">
        <i class="fas fa-arrow-left"></i> Back to Products
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

<form action="{{ route('backend.admin.products.store') }}" method="post" enctype="multipart/form-data">
    @csrf

    {{-- Basic Info --}}
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;margin-bottom:16px">
        <div style="padding:16px 20px 8px">
            <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">Basic Information</h6>
        </div>
        <div style="padding:0 20px 16px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:12px">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Name <span style="color:#dc2626">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Product name"
                           value="{{ old('name') }}" required
                           style="border-radius:8px;font-size:14px;padding:10px 12px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">SKU <span style="color:#dc2626">*</span></label>
                    <input type="text" name="sku" class="form-control" placeholder="e.g. PROD-001"
                           value="{{ old('sku') }}" required
                           style="border-radius:8px;font-size:14px;padding:10px 12px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Brand <span style="color:#dc2626">*</span></label>
                    <select name="brand_id" class="form-control select2" required
                            style="border-radius:8px;font-size:14px;padding:10px 12px">
                        <option value="">Select Brand</option>
                        @foreach($brands as $item)
                            <option value="{{ $item->id }}" {{ old('brand_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Category <span style="color:#dc2626">*</span></label>
                    <select name="category_id" class="form-control select2" required
                            style="border-radius:8px;font-size:14px;padding:10px 12px">
                        <option value="">Select Category</option>
                        @foreach($categories as $item)
                            <option value="{{ $item->id }}" {{ old('category_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Unit <span style="color:#dc2626">*</span></label>
                    <select name="unit_id" class="form-control" required
                            style="border-radius:8px;font-size:14px;padding:10px 12px">
                        <option value="">Select Unit</option>
                        @foreach($units as $item)
                            <option value="{{ $item->id }}" {{ old('unit_id') == $item->id ? 'selected' : '' }}>{{ $item->title . ' (' . $item->short_name . ')' }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;align-items:flex-end;padding-bottom:2px">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:500;color:#374151">
                        <input type="hidden" name="status" value="0">
                        <input type="checkbox" name="status" value="1" checked
                               style="width:16px;height:16px;accent-color:#059669">
                        Active
                    </label>
                </div>
            </div>
            <div style="margin-top:16px">
                <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Optional product description"
                          style="border-radius:8px;font-size:14px;padding:10px 12px;resize:vertical">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Pricing --}}
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;margin-bottom:16px">
        <div style="padding:16px 20px 8px">
            <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">Pricing</h6>
        </div>
        <div style="padding:0 20px 16px">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-top:12px">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Purchase Price (KES) <span style="color:#dc2626">*</span></label>
                    <input type="number" step="0.01" min="0" name="purchase_price" class="form-control"
                           placeholder="0.00" value="{{ old('purchase_price') }}" required
                           style="border-radius:8px;font-size:14px;padding:10px 12px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Selling Price (KES) <span style="color:#dc2626">*</span></label>
                    <input type="number" step="0.01" min="0" name="price" class="form-control"
                           placeholder="0.00" value="{{ old('price') }}" required
                           style="border-radius:8px;font-size:14px;padding:10px 12px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Discount Type</label>
                    <select name="discount_type" class="form-control"
                            style="border-radius:8px;font-size:14px;padding:10px 12px">
                        <option value="">None</option>
                        <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Fixed (KES)</option>
                        <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Discount Amount</label>
                    <input type="number" step="0.01" min="0" name="discount" class="form-control"
                           placeholder="0.00" value="{{ old('discount') }}"
                           style="border-radius:8px;font-size:14px;padding:10px 12px">
                </div>
            </div>
        </div>
    </div>

    {{-- Media & Dates --}}
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;margin-bottom:16px">
        <div style="padding:16px 20px 8px">
            <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">Image & Expiry</h6>
        </div>
        <div style="padding:0 20px 16px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:12px;align-items:start">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Product Image</label>
                    <div class="image-upload-container" id="imageUploadContainer">
                        <input type="file" name="product_image" id="thumbnailInput" accept="image/*" style="display:none">
                        <div class="thumb-preview" id="thumbPreviewContainer" style="border:2px dashed #d1d5db;border-radius:10px;padding:24px;text-align:center;cursor:pointer;transition:border-color 0.2s" onclick="document.getElementById('thumbnailInput').click()" onmouseover="this.style.borderColor='#10b981'" onmouseout="this.style.borderColor='#d1d5db'">
                            <img src="{{ asset('backend/assets/images/blank.png') }}" alt="Preview"
                                 class="img-thumbnail d-none" id="thumbnailPreview"
                                 style="max-width:120px;border-radius:8px">
                            <div class="upload-text" id="uploadText" style="margin-top:8px">
                                <i class="fas fa-cloud-upload-alt" style="font-size:24px;color:#9ca3af"></i>
                                <p style="margin:6px 0 0;font-size:12px;color:#999">Click to upload image</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:6px">Expiry Date</label>
                    <div class="input-group date" id="reservationdate" data-target-input="nearest">
                        <input type="text" placeholder="Select expiry date" class="form-control datetimepicker-input"
                               data-target="#reservationdate" name="expire_date" value="{{ old('expire_date') }}"
                               style="border-radius:8px;font-size:14px;padding:10px 12px" />
                        <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker"
                             style="cursor:pointer">
                            <div class="input-group-text" style="border-radius:0 8px 8px 0;background:#f9fafb;border-color:#d1d5db">
                                <i class="fas fa-calendar-alt" style="color:#6b7280"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:10px;margin-bottom:24px">
        <a href="{{ route('backend.admin.products.index') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none">
            Cancel
        </a>
        <button type="submit"
                style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:linear-gradient(135deg,#059669,#10b981);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer">
            <i class="fas fa-save"></i> Create Product
        </button>
    </div>
</form>
@endsection

@push('style')
<style>
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border-radius: 8px !important;
        border-color: #d1d5db !important;
    }
</style>
@endpush

@push('script')
<script src="{{ asset('js/image-field.js') }}"></script>
<script>
$(function() {
    $('#reservationdate').datetimepicker({ format: 'YYYY-MM-DD' });
});
</script>
@endpush
