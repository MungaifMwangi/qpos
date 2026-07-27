@extends('backend.master')

@section('title', 'Import Products')

@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-file-import"></i>
    </div>
    <div style="flex:1">
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Import Products</h2>
        <p style="margin:0;font-size:12px;color:#999">Bulk upload products via Excel spreadsheet</p>
    </div>
    <a href="{{ route('backend.admin.products.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none">
        <i class="fas fa-arrow-left"></i> Back to Products
    </a>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;gap:10px">
        <i class="fas fa-check-circle" style="color:#16a34a"></i>
        <span style="font-size:13px;font-weight:500;color:#166534">{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;color:#16a34a;cursor:pointer;font-size:16px">&times;</button>
    </div>
@endif

@if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;gap:10px">
        <i class="fas fa-exclamation-circle" style="color:#dc2626"></i>
        <span style="font-size:13px;font-weight:500;color:#991b1b">{{ session('error') }}</span>
        <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;color:#dc2626;cursor:pointer;font-size:16px">&times;</button>
    </div>
@endif

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

{{-- How It Works --}}
<div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;margin-bottom:16px">
    <div style="padding:16px 20px 8px">
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">How It Works</h6>
    </div>
    <div style="padding:0 20px 16px">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-top:12px">
            {{-- Step 1 --}}
            <div style="display:flex;gap:12px;align-items:flex-start">
                <div style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#ecfdf5;color:#059669;font-size:13px;font-weight:700;flex-shrink:0">1</div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#303030;margin-bottom:2px">Download Template</div>
                    <div style="font-size:11px;color:#999;line-height:1.4">Get the demo Excel file with the correct column headers and sample data.</div>
                </div>
            </div>
            {{-- Step 2 --}}
            <div style="display:flex;gap:12px;align-items:flex-start">
                <div style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#ecfdf5;color:#059669;font-size:13px;font-weight:700;flex-shrink:0">2</div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#303030;margin-bottom:2px">Fill in Your Data</div>
                    <div style="font-size:11px;color:#999;line-height:1.4">Add your products following the template format. Brand, category, and unit are auto-created if new.</div>
                </div>
            </div>
            {{-- Step 3 --}}
            <div style="display:flex;gap:12px;align-items:flex-start">
                <div style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#ecfdf5;color:#059669;font-size:13px;font-weight:700;flex-shrink:0">3</div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#303030;margin-bottom:2px">Upload & Import</div>
                    <div style="font-size:11px;color:#999;line-height:1.4">Select your completed file and click Import. Products will be created automatically.</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Column Reference --}}
<div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;margin-bottom:16px">
    <div style="padding:16px 20px 8px;display:flex;align-items:center;justify-content:space-between">
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">Required Columns</h6>
        <span style="font-size:11px;color:#999">All columns are required</span>
    </div>
    <div style="padding:0 8px 8px">
        <table class="table" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#666">Column</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#666">Type</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#666">Example</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#666">Notes</th>
                </tr>
            </thead>
            <tbody>
                @php
                $columns = [
                    ['name' => 'name', 'type' => 'Text', 'example' => 'Product A', 'note' => 'Product name'],
                    ['name' => 'sku', 'type' => 'Text', 'example' => 'PROD001', 'note' => 'Unique — duplicates auto-suffixed'],
                    ['name' => 'description', 'type' => 'Text', 'example' => 'A great product', 'note' => 'Can be empty in Excel'],
                    ['name' => 'category', 'type' => 'Text', 'example' => 'Electronics', 'note' => 'Created if it doesn\'t exist'],
                    ['name' => 'brand', 'type' => 'Text', 'example' => 'Brand X', 'note' => 'Created if it doesn\'t exist'],
                    ['name' => 'unit', 'type' => 'Text', 'example' => 'piece', 'note' => 'Created if it doesn\'t exist'],
                    ['name' => 'price', 'type' => 'Number', 'example' => '200.00', 'note' => 'Selling price (KES)'],
                    ['name' => 'discount', 'type' => 'Number', 'example' => '20.00', 'note' => 'Discount amount'],
                    ['name' => 'discount_type', 'type' => 'Text', 'example' => 'fixed / percentage', 'note' => 'Or leave empty'],
                    ['name' => 'purchase_price', 'type' => 'Number', 'example' => '150.00', 'note' => 'Cost price (KES)'],
                    ['name' => 'quantity', 'type' => 'Integer', 'example' => '100', 'note' => 'Initial stock level'],
                    ['name' => 'expire_date', 'type' => 'Date', 'example' => '2025-12-31', 'note' => 'Format: YYYY-MM-DD'],
                    ['name' => 'status', 'type' => '1 or 0', 'example' => '1', 'note' => '1 = Active, 0 = Inactive'],
                ];
                @endphp
                @foreach($columns as $col)
                <tr>
                    <td style="font-size:12px;font-weight:600;color:#303030;padding:6px 8px">
                        <code style="background:#f3f4f6;padding:2px 6px;border-radius:4px;font-size:11px">{{ $col['name'] }}</code>
                    </td>
                    <td style="font-size:11px;color:#666;padding:6px 8px">{{ $col['type'] }}</td>
                    <td style="font-size:11px;color:#999;padding:6px 8px">{{ $col['example'] }}</td>
                    <td style="font-size:11px;color:#999;padding:6px 8px">{{ $col['note'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Upload Form --}}
<div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;margin-bottom:16px">
    <div style="padding:16px 20px 8px">
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">Upload File</h6>
    </div>
    <div style="padding:0 20px 16px">
        <form action="{{ route('backend.admin.products.import') }}" method="post" enctype="multipart/form-data" id="importForm">
            @csrf
            <div style="margin-top:12px">
                {{-- Drop Zone --}}
                <div id="dropZone"
                     style="border:2px dashed #d1d5db;border-radius:12px;padding:40px 24px;text-align:center;cursor:pointer;transition:all 0.2s;background:#fafafa"
                     onclick="document.getElementById('fileInput').click()"
                     onmouseover="this.style.borderColor='#10b981';this.style.background='#f0fdf4'"
                     onmouseout="this.style.borderColor='#d1d5db';this.style.background='#fafafa'">
                    <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" required style="display:none"
                           onchange="handleFileSelect(this)">
                    <div id="dropDefault">
                        <i class="fas fa-cloud-upload-alt" style="font-size:36px;color:#d1d5db;margin-bottom:12px"></i>
                        <div style="font-size:14px;font-weight:600;color:#374151;margin-bottom:4px">
                            Click to browse or drag & drop
                        </div>
                        <div style="font-size:12px;color:#999">
                            Supports .xlsx, .xls, .csv files
                        </div>
                    </div>
                    <div id="dropSelected" style="display:none">
                        <i class="fas fa-file-excel" style="font-size:36px;color:#059669;margin-bottom:12px"></i>
                        <div id="fileName" style="font-size:14px;font-weight:600;color:#374151;margin-bottom:4px"></div>
                        <div id="fileSize" style="font-size:12px;color:#999;margin-bottom:8px"></div>
                        <button type="button" onclick="event.stopPropagation();clearFile()" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:6px;padding:4px 12px;font-size:11px;font-weight:600;cursor:pointer">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                </div>

                {{-- Download Demo --}}
                <div style="margin-top:12px;display:flex;justify-content:center">
                    <a href="{{ route('backend.admin.products.import', ['download-demo' => true]) }}"
                       style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f0fdf4;color:#059669;border:1px solid #bbf7d0;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;transition:all 0.15s"
                       onmouseover="this.style.background='#dcfce7'"
                       onmouseout="this.style.background='#f0fdf4'">
                        <i class="fas fa-download"></i> Download Demo Spreadsheet
                    </a>
                </div>

                {{-- Submit --}}
                <div style="margin-top:16px;display:flex;justify-content:flex-end;gap:10px">
                    <a href="{{ route('backend.admin.products.index') }}"
                       style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none">
                        Cancel
                    </a>
                    <button type="submit" id="importBtn"
                            style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:linear-gradient(135deg,#059669,#10b981);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;opacity:0.5;pointer-events:none;transition:all 0.2s">
                        <i class="fas fa-file-import"></i> Import Products
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
    var fileInput = document.getElementById('fileInput');
    var importBtn = document.getElementById('importBtn');
    var dropZone = document.getElementById('dropZone');
    var dropDefault = document.getElementById('dropDefault');
    var dropSelected = document.getElementById('dropSelected');

    // Drag & drop events
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#059669';
        this.style.background = '#f0fdf4';
    });
    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = '#d1d5db';
        this.style.background = '#fafafa';
    });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#d1d5db';
        this.style.background = '#fafafa';
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect(fileInput);
        }
    });

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            var file = input.files[0];
            var ext = file.name.split('.').pop().toLowerCase();
            if (['xlsx', 'xls', 'csv'].indexOf(ext) === -1) {
                alert('Please select an Excel or CSV file (.xlsx, .xls, .csv)');
                input.value = '';
                return;
            }
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = formatSize(file.size);
            dropDefault.style.display = 'none';
            dropSelected.style.display = 'block';
            importBtn.style.opacity = '1';
            importBtn.style.pointerEvents = 'auto';
        }
    }

    function clearFile() {
        fileInput.value = '';
        dropDefault.style.display = 'block';
        dropSelected.style.display = 'none';
        importBtn.style.opacity = '0.5';
        importBtn.style.pointerEvents = 'none';
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    // Show loading on submit
    document.getElementById('importForm').addEventListener('submit', function() {
        importBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
        importBtn.style.opacity = '0.7';
        importBtn.style.pointerEvents = 'none';
    });
</script>
@endpush
