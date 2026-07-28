@extends('backend.master')
@section('title', 'Receipt_'.$order->id)
@section('content')

<div class="card">
  <div class="receipt-container mt-0" id="printable-section" style="width:300px;max-width:300px;margin:0;font-size:13px;font-family:'Courier New',monospace;color:#000;padding:8px;font-weight:600;background:#fff">
    {{-- Header --}}
    <div style="text-align:center;margin-bottom:6px">
      @if(readConfig('is_show_logo_invoice'))
      <img src="{{ assetImage(readconfig('site_logo')) }}" height="35" width="80" alt="Logo" style="display:block;margin:0 auto 4px">
      @endif
      @if(readConfig('is_show_site_invoice'))
      <div style="font-size:20px;font-weight:700;color:#000;letter-spacing:1px;text-transform:uppercase">{{ readConfig('site_name') }}</div>
      @endif
      @if(readConfig('is_show_address_invoice'))<div style="font-size:12px;color:#000">{{ readConfig('contact_address') }}</div>@endif
      @if(readConfig('is_show_phone_invoice'))<div style="font-size:12px;color:#000">{{ readConfig('contact_phone') }}</div>@endif
      @if(readConfig('is_show_email_invoice'))<div style="font-size:12px;color:#000">{{ readConfig('contact_email') }}</div>@endif
    </div>

    <hr style="border:none;border-top:1px dashed #000;margin:5px 0">

    {{-- Meta --}}
    <div style="font-size:12px;color:#000;margin-bottom:4px">
      User: {{ auth()->user()->name }}<br>
      Order: #{{ $order->id }}
    </div>

    @if(readConfig('is_show_customer_invoice') && $order->customer)
    <hr style="border:none;border-top:1px dashed #ddd;margin:5px 0">
    <div style="font-size:12px;color:#000;margin-bottom:4px">
      <strong>Name:</strong> {{ $order->customer->name }}<br>
      @if($order->customer->address)<strong>Address:</strong> {{ $order->customer->address }}<br>@endif
      @if($order->customer->phone)<strong>Phone:</strong> {{ $order->customer->phone }}@endif
    </div>
    @endif

    <hr style="border:none;border-top:1px dashed #000;margin:5px 0">

    {{-- Date/Time --}}
    <div style="font-size:12px;color:#000;margin-bottom:4px">
      {{ date('d-M-Y') }}<br>
      {{ date('h:i:s A') }}
    </div>

    <hr style="border:none;border-top:1px dashed #000;margin:5px 0">

    {{-- Items header --}}
    <div style="display:flex;font-size:12px;font-weight:700;color:#000;padding-bottom:3px;border-bottom:1px dashed #000;margin-bottom:3px">
      <span style="flex:4;text-align:left">Product</span>
      <span style="flex:1;text-align:center">Qty</span>
      <span style="flex:2;text-align:right">Price</span>
      <span style="flex:2;text-align:right">Total</span>
    </div>

    @foreach ($order->products as $item)
    <div style="display:flex;font-size:12px;color:#000;padding:2px 0">
      <span style="flex:4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding-right:4px">{{ $item->product->name }}</span>
      <span style="flex:1;text-align:center">{{ $item->quantity }}</span>
      <span style="flex:2;text-align:right;padding-right:4px">{{ number_format($item->discounted_price, 2) }}</span>
      <span style="flex:2;text-align:right;font-weight:600">{{ number_format($item->total, 2) }}</span>
    </div>
    @endforeach

    <hr style="border:none;border-top:1px dashed #000;margin:5px 0">

    {{-- Summary --}}
    <div style="display:flex;justify-content:space-between;font-size:12px;color:#000;padding:2px 0">
      <span>Subtotal:</span>
      <span>{{ number_format($order->sub_total, 2) }}</span>
    </div>
    @if($order->discount > 0)
    <div style="display:flex;justify-content:space-between;font-size:12px;color:#000;padding:2px 0">
      <span>Discount:</span>
      <span>-{{ number_format($order->discount, 2) }}</span>
    </div>
    @endif
    @if($order->tax > 0)
    <div style="display:flex;justify-content:space-between;font-size:12px;color:#000;padding:2px 0">
      <span>Tax:</span>
      <span>{{ number_format($order->tax, 2) }}</span>
    </div>
    @endif
    <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;color:#000;padding:4px 0;border-top:1px dashed #000;border-bottom:1px dashed #000;margin:4px 0">
      <span>Total:</span>
      <span>{{ currency()->symbol ?? 'KES' }} {{ number_format($order->total, 2) }}</span>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:12px;color:#000;padding:2px 0">
      <span>Paid:</span>
      <span>{{ number_format($order->paid + $order->change_amount, 2) }}</span>
    </div>
    @if($order->change_amount > 0)
    <div style="display:flex;justify-content:space-between;font-size:12px;color:#000;padding:2px 0">
      <span>Change:</span>
      <span>{{ number_format($order->change_amount, 2) }}</span>
    </div>
    @endif
    <div style="display:flex;justify-content:space-between;font-size:12px;color:#000;padding:2px 0">
      <span>Due:</span>
      <span>{{ number_format($order->due, 2) }}</span>
    </div>

    <hr style="border:none;border-top:1px dashed #000;margin:5px 0">

    {{-- Footer --}}
    <div style="text-align:center;font-size:10px;color:#000;margin-top:4px">
      @if(readConfig('is_show_note_invoice')){{ readConfig('note_to_customer_invoice') }}@endif
    </div>
  </div>

  <div class="text-center mt-3 no-print pb-3">
    <button type="button" onclick="window.print()" class="btn bg-gradient-primary text-white"><i class="fas fa-print"></i> Print</button>
  </div>
</div>
@endsection

@push('style')
<style>
  .receipt-container * { color: #000 !important; }

  @media print {
    @page { margin: 0; padding: 0; }

    body { background: #fff; margin: 0; padding: 0; }

    .main-sidebar, .main-header, .main-footer, footer, nav, header, aside {
      display: none !important;
    }

    .content-wrapper {
      margin-left: 0 !important;
      padding: 0 !important;
      background: #fff !important;
    }

    .content, .container-fluid {
      padding: 0 !important;
      margin: 0 !important;
    }

    .card {
      border: none !important;
      box-shadow: none !important;
      margin: 0 !important;
      padding: 0 !important;
    }

    .receipt-container {
      width: 300px !important;
      max-width: 300px !important;
      border: none !important;
      padding: 8px !important;
      margin: 0 !important;
      background: #fff !important;
    }

    .no-print { display: none !important; }
  }
</style>
@endpush

@push('script')
<script>
  var posUrl = "{{ route('backend.admin.cart.index') }}";
  var redirected = false;
  var goToPos = function () {
    if (redirected) return;
    redirected = true;
    window.location.href = posUrl;
  };
  window.addEventListener('afterprint', goToPos);
  window.print();
</script>
@endpush
