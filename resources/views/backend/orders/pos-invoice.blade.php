@extends('backend.master')
@section('title', 'Receipt_'.$order->id)
@section('content')

<div class="card">
  <!-- Main content -->
  <div class="receipt-container mt-0" id="printable-section" style="max-width: {{ $maxWidth}}; font-size: 14px; font-family: 'Arial', 'Helvetica', sans-serif;">
    <div class="text-center">
      @if(readConfig('is_show_logo_invoice'))
      <img src="{{ assetImage(readconfig('site_logo')) }}" height="30" width="70" alt="Logo">
      @endif
      @if(readConfig('is_show_site_invoice'))
      <h3 style="font-size:18px;margin:4px 0">{{ readConfig('site_name') }}</h3>
      @endif
      @if(readConfig('is_show_address_invoice'))<span style="font-size:12px">{{ readConfig('contact_address') }}</span><br>@endif
      @if(readConfig('is_show_phone_invoice'))<span style="font-size:12px">{{ readConfig('contact_phone') }}</span><br>@endif
      @if(readConfig('is_show_email_invoice'))<span style="font-size:12px">{{ readConfig('contact_email') }}</span><br>@endif
    </div>
    <div style="font-size:13px">
      {{ 'User: '.auth()->user()->name}}<br>
      {{ 'Order: #'.$order->id}}<br>
    </div>
    <hr>
    <div class="row justify-content-between mx-auto">
      <div class="text-left">
        @if(readConfig('is_show_customer_invoice'))
        <address style="font-size:12px">
          Name: {{ $order->customer->name ?? 'N/A' }}<br>
          Address: {{ $order->customer->address ?? 'N/A' }}<br>
          Phone: {{ $order->customer->phone ?? 'N/A' }}
        </address>
        @endif
      </div>
      <div class="text-right">
        <address class="text-right" style="font-size:12px">
          <p>{{ date('d-M-Y') }}</p>
          <p>{{ date('h:i:s A') }}</p>
        </address>
      </div>
    </div>
    <hr>
    <table style="width: 100%;">
      <thead>
        <tr>
          <th style="text-align: left;font-size:13px">Product</th>
          <th style="text-align: right;font-size:13px"></th>
          <th style="text-align: right;font-size:13px">Total {{ currency()->symbol}}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($order->products as $item)
        <tr>
          <td style="font-size:13px">{{ $item->product->name }}</td>
          <td class="text-right" style="font-size:13px">{{ $item->quantity }}*{{ $item->discounted_price}}</td>
          <td class="text-right" style="font-size:13px">{{ $item->total }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <hr>
    <div class="summary">
      <table style="width: 100%;">
        <tr>
          <td style="font-size:13px">Subtotal:</td>
          <td class="text-right" style="font-size:13px">{{number_format($order->sub_total, 2) }}</td>
        </tr>
        <tr>
          <td style="font-size:13px">Discount:</td>
          <td class="text-right" style="font-size:13px">{{number_format($order->discount, 2) }}</td>
        </tr>
        <tr>
          <td style="font-size:15px"><strong>Total:</strong></td>
          <td class="text-right" style="font-size:15px"><strong>{{number_format($order->total, 2) }}</strong></td>
        </tr>
        <tr>
          <td style="font-size:13px">Paid:</td>
          <td class="text-right" style="font-size:13px">{{number_format($order->paid + $order->change_amount, 2) }}</td>
        </tr>
        @if($order->change_amount > 0)
        <tr>
          <td style="font-size:13px">Change:</td>
          <td class="text-right" style="font-size:13px">{{number_format($order->change_amount, 2) }}</td>
        </tr>
        @endif
        <tr>
          <td style="font-size:13px">Due:</td>
          <td class="text-right" style="font-size:13px">{{number_format($order->due, 2) }}</td>
        </tr>
      </table>
    </div>
    <hr>
    <div class="text-center">
      <p class="text-muted" style="font-size: 11px;">@if(readConfig('is_show_note_invoice')){{ readConfig('note_to_customer_invoice') }}@endif</p>
    </div>
  </div>

  <!-- Print Button -->
  <div class="text-center mt-3 no-print pb-3">
    <button type="button" onclick="window.print()" class="btn bg-gradient-primary text-white"><i class="fas fa-print"></i> Print</button>
  </div>
</div>
@endsection

@push('style')
<style>
  .receipt-container {
    border: 1px dotted #000;
    padding: 8px;
    font-weight: 600;
  
  }

  hr {
    border: none;
    border-top: 1px dashed #000;
    margin: 5px 0;
  }

  table {
    width: 100%;
  }

  td {
    padding: 2px 0;
    font-weight: 600;
  }
  th {
    padding: 2px 0;
    font-weight: 700;
  }

  .text-right {
    text-align: right;
  }

  @media print {
    @page {
      margin-top: 5px !important;
      margin-left: 0px !important;
      padding-left: 0px !important;
    }

    footer {
      display: none !important;
    }
  }
</style>
@endpush

@push('script')
<script>
  // Once the print dialog is closed (printed or cancelled), send the cashier
  // back to the POS page to start the next sale instead of leaving them on
  // the receipt. Guard so the redirect only fires once.
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
