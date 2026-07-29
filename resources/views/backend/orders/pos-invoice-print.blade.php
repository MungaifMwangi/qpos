<!DOCTYPE html>
<html>
<head>
<title>Print Receipt #{{ $order->id }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    background: #fff;
    font-family: 'Courier New', monospace;
    color: #000;
    font-weight: 600;
    font-size: 13px;
  }
  .receipt-container {
    width: 300px;
    max-width: 300px;
    margin: 0;
    padding: 8px;
    background: #fff;
  }
  .receipt-container * { color: #000 !important; }
  hr { border: none; border-top: 1px dashed #000; margin: 5px 0; }
  .text-center { text-align: center; }
  .flex-between { display: flex; justify-content: space-between; }
  .fw-700 { font-weight: 700; }
  .fs-10 { font-size: 10px; }
  .fs-12 { font-size: 12px; }
  .fs-16 { font-size: 16px; }
  .fs-20 { font-size: 20px; }
  .mb-4 { margin-bottom: 4px; }
  .mb-6 { margin-bottom: 6px; }
  .mt-4 { margin-top: 4px; }
  .py-2 { padding-top: 2px; padding-bottom: 2px; }
  .py-4 { padding-top: 4px; padding-bottom: 4px; }
  .pb-3 { padding-bottom: 3px; }
  .border-bottom-dashed { border-bottom: 1px dashed #000; }
  .d-block { display: block; }
  .mx-auto { margin-left: auto; margin-right: auto; }
  .text-uppercase { text-transform: uppercase; }
  .letter-spacing-1 { letter-spacing: 1px; }
  .overflow-hidden { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .text-right { text-align: right; }

  @page { margin: 0; padding: 0; }
  @media print {
    body { margin: 0; padding: 0; }
    .no-print { display: none !important; }
  }
</style>
</head>
<body>
<div class="receipt-container">
  {{-- Header --}}
  <div class="text-center mb-6">
    @if(readConfig('is_show_logo_invoice'))
    <img src="{{ assetImage(readconfig('site_logo')) }}" height="35" width="80" alt="Logo" class="d-block mx-auto mb-4" style="display:block;margin:0 auto 4px">
    @endif
    @if(readConfig('is_show_site_invoice'))
    <div class="fs-20 fw-700 text-uppercase letter-spacing-1">{{ readConfig('site_name') }}</div>
    @endif
    @if(readConfig('is_show_address_invoice'))<div class="fs-12">{{ readConfig('contact_address') }}</div>@endif
    @if(readConfig('is_show_phone_invoice'))<div class="fs-12">{{ readConfig('contact_phone') }}</div>@endif
    @if(readConfig('is_show_email_invoice'))<div class="fs-12">{{ readConfig('contact_email') }}</div>@endif
  </div>

  <hr>

  {{-- Meta --}}
  <div class="fs-12 mb-4">
    User: {{ auth()->user()->name }}<br>
    Receipt No: #{{ $order->id }}
  </div>

  @if(readConfig('is_show_customer_invoice') && $order->customer)
  <hr style="border-top-color:#ddd">
  <div class="fs-12 mb-4">
    <strong>Name:</strong> {{ $order->customer->name }}<br>
    @if($order->customer->address)<strong>Address:</strong> {{ $order->customer->address }}<br>@endif
    @if($order->customer->phone)<strong>Phone:</strong> {{ $order->customer->phone }}@endif
  </div>
  @endif

  <hr>

  {{-- Date/Time --}}
  <div class="fs-12 mb-4">
    {{ date('d-M-Y') }}<br>
    {{ date('h:i:s A') }}
  </div>

  <hr>

  {{-- Items header --}}
  <div class="fw-700 pb-3 border-bottom-dashed mb-4" style="display:flex;font-size:12px">
    <span style="flex:4;text-align:left">Product</span>
    <span style="flex:1;text-align:center">Qty</span>
    <span style="flex:2;text-align:right">Price</span>
    <span style="flex:2;text-align:right">Total</span>
  </div>

  @foreach ($order->products as $item)
  <div class="py-2" style="display:flex;font-size:12px">
    <span class="overflow-hidden" style="flex:4;padding-right:4px">{{ $item->product->name }}</span>
    <span style="flex:1;text-align:center">{{ $item->quantity }}</span>
    <span style="flex:2;text-align:right;padding-right:4px">{{ number_format($item->discounted_price, 2) }}</span>
    <span class="fw-700" style="flex:2;text-align:right">{{ number_format($item->total, 2) }}</span>
  </div>
  @endforeach

  <hr>

  {{-- Summary --}}
  <div class="flex-between fs-12 py-2">
    <span>Subtotal:</span>
    <span>{{ number_format($order->sub_total, 2) }}</span>
  </div>
  @if($order->discount > 0)
  <div class="flex-between fs-12 py-2">
    <span>Discount:</span>
    <span>-{{ number_format($order->discount, 2) }}</span>
  </div>
  @endif
  @if($order->tax > 0)
  <div class="flex-between fs-12 py-2">
    <span>Tax:</span>
    <span>{{ number_format($order->tax, 2) }}</span>
  </div>
  @endif
  <div class="flex-between fs-16 fw-700 py-4" style="border-top:1px dashed #000;border-bottom:1px dashed #000;margin:4px 0">
    <span>Total:</span>
    <span>{{ currency()->symbol ?? 'KES' }} {{ number_format($order->total, 2) }}</span>
  </div>
  <div class="flex-between fs-12 py-2">
    <span>Paid:</span>
    <span>{{ number_format($order->paid + $order->change_amount, 2) }}</span>
  </div>
  @if($order->change_amount > 0)
  <div class="flex-between fs-12 py-2">
    <span>Change:</span>
    <span>{{ number_format($order->change_amount, 2) }}</span>
  </div>
  @endif
  <div class="flex-between fs-12 py-2">
    <span>Due:</span>
    <span>{{ number_format($order->due, 2) }}</span>
  </div>

  <hr>

  {{-- Footer --}}
  <div class="text-center fs-10 mt-4">
    @if(readConfig('is_show_note_invoice')){{ readConfig('note_to_customer_invoice') }}@endif
  </div>
</div>

<script>
  (function () {
    window.onafterprint = function () { window.close(); };

    var printFn = function () {
      window.print();
    };
    // Wait a tick for rendering, then print
    if (document.readyState === 'complete') {
      setTimeout(printFn, 300);
    } else {
      window.addEventListener('load', function () {
        setTimeout(printFn, 300);
      });
    }
  }());
</script>
</body>
</html>