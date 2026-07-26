@extends('backend.master')
@section('title', ' ')

@section('content')
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#d35400,#e67e22);color:#fff;font-size:18px;flex-shrink:0">
        <i class="fas fa-shopping-bag"></i>
    </div>
    <div style="flex:1">
        <h2 style="margin:0;font-size:20px;font-weight:700;color:#303030">Purchase #{{ $id }}</h2>
        <p style="margin:0;font-size:12px;color:#999">Purchase details and items for <strong>{{ $purchase->supplier->name }}</strong></p>
    </div>
    <a href="{{ route('backend.admin.purchase.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f5f5f5;color:#666;border:1px solid #e0e0e0;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none">
        <i class="fas fa-arrow-left"></i> Back to Purchases
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 20px">
        <h6 style="margin:0 0 8px;font-size:11px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:0.5px">Supplier</h6>
        <div style="font-size:15px;font-weight:700;color:#303030">{{ $purchase->supplier->name }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 20px">
        <h6 style="margin:0 0 8px;font-size:11px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:0.5px">Purchase Date</h6>
        <div style="font-size:15px;font-weight:700;color:#303030">{{ \Carbon\Carbon::parse($purchase->date)->format('d M, Y') }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 20px">
        <h6 style="margin:0 0 8px;font-size:11px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:0.5px">Grand Total</h6>
        <div style="font-size:18px;font-weight:800;color:#d35400">KES {{ number_format($purchase->grand_total, 2) }}</div>
    </div>
</div>

<div style="background:#fff;border:1px solid #e8e8e8;border-radius:14px;overflow:hidden">
    <div style="padding:16px 20px 8px;display:flex;justify-content:space-between;align-items:center">
        <h6 style="margin:0;font-weight:700;color:#303030;font-size:13px">Purchase Items</h6>
        <button type="button" onclick="window.print()" class="btn btn-sm" style="background:#2d2d2d;color:#fff;border-radius:8px;padding:6px 14px;font-weight:600">
            <i class="fas fa-print mr-1"></i> Print
        </button>
    </div>
    <div style="padding:0 8px 8px">
        <table class="table table-hover" style="margin:0">
            <thead>
                <tr>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">#</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Product</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Purchase Price</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Quantity</th>
                    <th style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px" class="text-right">Sub Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchase->items as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td style="font-weight:600">{{ $item->product->name }}</td>
                    <td class="text-right">KES {{ number_format($item->purchase_price, 2) }}</td>
                    <td class="text-right" style="font-weight:600">{{ $item->quantity }} {{ optional($item->product->unit)->short_name }}</td>
                    <td class="text-right" style="font-weight:700">KES {{ number_format($item->purchase_price * $item->quantity, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding:24px;color:#999">
                        <i class="fas fa-inbox" style="font-size:24px;margin-bottom:8px;display:block"></i>
                        No items found for this purchase.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:20px">
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 20px">
        <h6 style="margin:0 0 8px;font-size:11px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:0.5px">Notes</h6>
        <div style="font-size:13px;color:#666;min-height:40px">{{ $purchase->note ?? 'No notes added.' }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e8e8e8;border-radius:12px;padding:16px 20px">
        <h6 style="margin:0 0 10px;font-size:11px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:0.5px">Summary</h6>
        <table style="width:100%;font-size:13px">
            <tr>
                <td style="padding:4px 0;color:#999">Subtotal</td>
                <td class="text-right" style="padding:4px 0;font-weight:600">KES {{ number_format($purchase->sub_total, 2) }}</td>
            </tr>
            <tr>
                <td style="padding:4px 0;color:#999">Tax</td>
                <td class="text-right" style="padding:4px 0;font-weight:600">KES {{ number_format($purchase->tax, 2) }}</td>
            </tr>
            <tr>
                <td style="padding:4px 0;color:#999">Discount</td>
                <td class="text-right" style="padding:4px 0;font-weight:600">KES {{ number_format($purchase->discount_value, 2) }}</td>
            </tr>
            <tr>
                <td style="padding:4px 0;color:#999">Shipping</td>
                <td class="text-right" style="padding:4px 0;font-weight:600">KES {{ number_format($purchase->shipping, 2) }}</td>
            </tr>
            <tr style="border-top:1px solid #e8e8e8">
                <td style="padding:8px 0 0;font-weight:700;color:#303030">Total</td>
                <td class="text-right" style="padding:8px 0 0;font-weight:800;color:#d35400;font-size:15px">KES {{ number_format($purchase->grand_total, 2) }}</td>
            </tr>
        </table>
    </div>
</div>
@endsection
