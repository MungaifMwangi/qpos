@extends('backend.master')

@section('title', 'Profit & Loss Statement')

@section('content')
<div class="card">
  <div class="mt-n5 mb-3 d-flex justify-content-end">
    <div class="form-group">
      <div class="input-group">
        <button type="button" class="btn btn-default float-right" id="daterange-btn">
          <i class="far fa-calendar-alt"></i> <span>Filter by date</span>
          <i class="fas fa-caret-down"></i>
        </button>
      </div>
    </div>
  </div>
  
  <div class="card-body p-2 p-md-4 pt-0">
    <div class="row g-4">
      <div class="col-md-12">
        <div class="card-body p-0">
          <section class="invoice">
            <!-- Title block -->
            <div class="row invoice-info text-center mb-4">
              <div class="col-12">
                <h3><strong>Profit & Loss Statement</strong></h3>
                <h5>For the period: <strong>{{ $start_date }}</strong> to <strong>{{ $end_date }}</strong></h5>
              </div>
            </div>

            <!-- Statement Table -->
            <div class="row justify-content-center">
              <div class="col-md-10 col-12">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped">
                    <thead class="bg-light">
                      <tr>
                        <th>Account Classification / Category</th>
                        <th class="text-right" style="width: 25%;">Amount ({{ currency()->symbol ?? '' }})</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- REVENUE SECTION -->
                      <tr class="font-weight-bold table-secondary">
                        <td colspan="2">Revenue</td>
                      </tr>
                      <tr>
                        <td class="pl-4">Gross Sales</td>
                        <td class="text-right">{{ number_format($gross_sales, 2) }}</td>
                      </tr>
                      <tr>
                        <td class="pl-4 text-danger">Less: Sales Discounts</td>
                        <td class="text-right text-danger">({{ number_format($sales_discount, 2) }})</td>
                      </tr>
                      <tr class="font-weight-bold">
                        <td class="pl-3">Net Sales</td>
                        <td class="text-right" style="border-top: 1px solid #333; border-bottom: 1px solid #333;">
                          {{ number_format($net_sales, 2) }}
                        </td>
                      </tr>

                      <!-- COGS SECTION -->
                      <tr class="font-weight-bold table-secondary">
                        <td colspan="2">Cost of Goods Sold (COGS)</td>
                      </tr>
                      <tr>
                        <td class="pl-4">Cost of Products Sold</td>
                        <td class="text-right text-danger">({{ number_format($cogs, 2) }})</td>
                      </tr>
                      <tr class="font-weight-bold">
                        <td class="pl-3">Total Cost of Goods Sold</td>
                        <td class="text-right text-danger" style="border-top: 1px solid #333; border-bottom: 1px solid #333;">
                          ({{ number_format($cogs, 2) }})
                        </td>
                      </tr>

                      <!-- GROSS PROFIT -->
                      <tr class="font-weight-bold" style="background-color: #e9ecef;">
                        <td>Gross Profit</td>
                        <td class="text-right" style="border-bottom: 2px double #333;">
                          {{ number_format($gross_profit, 2) }}
                        </td>
                      </tr>

                      <!-- OPERATING EXPENSES -->
                      <tr class="font-weight-bold table-secondary">
                        <td colspan="2">Operating & Business Expenses</td>
                      </tr>
                      @foreach($expenseCategories as $key => $name)
                        @php
                          $catAmount = $categoryTotals[$key] ?? 0;
                        @endphp
                        <tr>
                          <td class="pl-4">{{ $name }}</td>
                          <td class="text-right">{{ number_format($catAmount, 2) }}</td>
                        </tr>
                      @endforeach
                      <tr class="font-weight-bold">
                        <td class="pl-3">Total Operating Expenses</td>
                        <td class="text-right text-danger" style="border-top: 1px solid #333; border-bottom: 1px solid #333;">
                          ({{ number_format($total_expenses, 2) }})
                        </td>
                      </tr>

                      <!-- NET PROFIT/LOSS -->
                      @php
                        $netProfitClass = $net_profit >= 0 ? 'text-success font-weight-bold' : 'text-danger font-weight-bold';
                      @endphp
                      <tr class="{{ $netProfitClass }}" style="background-color: #dfedd6; font-size: 1.15rem;">
                        <td><strong>Net Profit / (Loss)</strong></td>
                        <td class="text-right" style="border-bottom: 3px double #333;">
                          <strong>{{ number_format($net_profit, 2) }}</strong>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Print Actions -->
            <div class="row no-print mt-4">
              <div class="col-md-10 mx-auto text-right">
                <button type="button" onclick="window.print()" class="btn btn-success"><i class="fas fa-print"></i> Print Statement</button>
              </div>
            </div>

          </section>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('style')
<style>
  .invoice {
    border: none !important;
  }
  .pl-4 {
    padding-left: 2rem !important;
  }
  .pl-3 {
    padding-left: 1.5rem !important;
  }
</style>
@endpush

@push('script')
<script>
  $(function() {
    // Extract dates from URL params or defaults
    const urlParams = new URLSearchParams(window.location.search);
    const startDate = urlParams.get('start_date') || "{{ $start_date_raw }}";
    const endDate = urlParams.get('end_date') || "{{ $end_date_raw }}";

    // Initialize date range picker
    $('#daterange-btn').daterangepicker({
        ranges: {
          'Today': [moment(), moment()],
          'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Last 7 Days': [moment().subtract(6, 'days'), moment()],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()],
          'This Month': [moment().startOf('month'), moment().endOf('month')],
          'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment(startDate, "YYYY-MM-DD"),
        endDate: moment(endDate, "YYYY-MM-DD")
      },
      function(start, end) {
        // Redirect with selected start and end dates
        window.location.href = '{{ route("backend.admin.profit-loss.index") }}?start_date=' + start.format('YYYY-MM-DD') + '&end_date=' + end.format('YYYY-MM-DD');
      }
    );

    // Show date on button
    $('#daterange-btn span').html(moment(startDate, "YYYY-MM-DD").format('MMMM D, YYYY') + ' - ' + moment(endDate, "YYYY-MM-DD").format('MMMM D, YYYY'));
  });
</script>
@endpush
