@extends('backend.master')

@section('title', 'Sales Summary')

@section('content')

    {{-- Controls --}}
    <div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
        <button type="button" class="btn btn-default" id="daterange-btn">
            <i class="far fa-calendar-alt"></i> <span>Filter by date</span>
            <i class="fas fa-caret-down"></i>
        </button>
    </div>

<style>
    .ss-kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 24px; }
    .ss-kpi-card {
        border-radius: 10px; padding: 22px 20px 16px; color: #fff; position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,.12); overflow: hidden; min-height: 130px;
    }
    .ss-kpi-card .kpi-icon {
        position: absolute; top: 18px; right: 18px; width: 44px; height: 44px;
        border-radius: 50%; background: rgba(255,255,255,.22); display: flex;
        align-items: center; justify-content: center; font-size: 20px;
    }
    .ss-kpi-card .kpi-label {
        text-transform: uppercase; font-size: 11px; letter-spacing: .8px; opacity: .85; font-weight: 600;
    }
    .ss-kpi-card .kpi-value { font-size: 28px; font-weight: 700; margin: 6px 0 12px; }
    .ss-kpi-card .kpi-divider { border-top: 1px solid rgba(255,255,255,.3); margin-bottom: 8px; }
    .ss-kpi-card .kpi-footer { font-size: 12px; opacity: .9; }
    .kpi-purple  { background: linear-gradient(135deg, #6f42c1, #a77bca); }
    .kpi-green   { background: linear-gradient(135deg, #43a047, #7fd858); }
    .kpi-blue    { background: linear-gradient(135deg, #0288d1, #29b6f6); }
    .kpi-orange  { background: linear-gradient(135deg, #ef6c00, #f5a623); }

    .ss-panel {
        background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,.06);
        overflow: hidden; margin-bottom: 24px;
    }
    .ss-panel-hdr {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px; border-bottom: 1px solid #eef0f4;
    }
    .ss-panel-hdr h5 { margin: 0; font-size: 15px; font-weight: 600; color: #333; }
    .ss-panel-hdr h5 i { color: #8e5fd9; margin-right: 6px; }
    .ss-panel-body { padding: 20px; }

    .ss-charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }

    .ss-summary-table { width: 100%; border-collapse: collapse; }
    .ss-summary-table th {
        background: #f8f9fc; color: #6c757d; font-weight: 600; text-transform: uppercase;
        font-size: 11px; letter-spacing: .5px; padding: 12px 16px; border-bottom: 2px solid #eef0f4;
    }
    .ss-summary-table td {
        padding: 12px 16px; border-bottom: 1px solid #f3f4f6; color: #444; vertical-align: middle;
    }
    .ss-summary-table tbody tr:hover { background: #fafafe; }

    @media (max-width: 991px) {
        .ss-kpi-row { grid-template-columns: repeat(2, 1fr); }
        .ss-charts-row { grid-template-columns: 1fr; }
    }
    @media (max-width: 575px) {
        .ss-kpi-row { grid-template-columns: 1fr; }
    }
</style>

    {{-- KPI Cards --}}
    <div class="ss-kpi-row">
        <div class="ss-kpi-card kpi-purple">
            <div class="kpi-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="kpi-label">Subtotal</div>
            <div class="kpi-value">{{ currency()->symbol ?? 'KES' }} {{ number_format($sub_total, 2) }}</div>
            <div class="kpi-divider"></div>
            <div class="kpi-footer">Gross sales before discounts</div>
        </div>
        <div class="ss-kpi-card kpi-green">
            <div class="kpi-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="kpi-label">Total Sold</div>
            <div class="kpi-value">{{ currency()->symbol ?? 'KES' }} {{ number_format($total, 2) }}</div>
            <div class="kpi-divider"></div>
            <div class="kpi-footer">{{ $order_count }} orders &middot; Avg {{ currency()->symbol ?? 'KES' }} {{ number_format($avg_order, 2) }}</div>
        </div>
        <div class="ss-kpi-card kpi-blue">
            <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
            <div class="kpi-label">Customer Paid</div>
            <div class="kpi-value">{{ currency()->symbol ?? 'KES' }} {{ number_format($paid, 2) }}</div>
            <div class="kpi-divider"></div>
            <div class="kpi-footer">Payments collected</div>
        </div>
        <div class="ss-kpi-card kpi-orange">
            <div class="kpi-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="kpi-label">Customer Due</div>
            <div class="kpi-value">{{ currency()->symbol ?? 'KES' }} {{ number_format($due, 2) }}</div>
            <div class="kpi-divider"></div>
            <div class="kpi-footer">Outstanding balance</div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="ss-charts-row">
        <div class="ss-panel">
            <div class="ss-panel-hdr">
                <h5><i class="fas fa-chart-pie"></i> Sales by Category</h5>
                <span style="font-size:12px;color:#888;">{{ $start_date }} &mdash; {{ $end_date }}</span>
            </div>
            <div class="ss-panel-body" style="display:flex;justify-content:center;align-items:center;min-height:300px;">
                @if(count($categoryLabels) > 0)
                    <canvas id="categoryDonutChart" style="max-height:300px;"></canvas>
                @else
                    <p style="color:#aaa;">No category data for this period.</p>
                @endif
            </div>
        </div>
        <div class="ss-panel">
            <div class="ss-panel-hdr">
                <h5><i class="fas fa-chart-line"></i> Sales Trend</h5>
                <span style="font-size:12px;color:#888;">{{ $start_date }} &mdash; {{ $end_date }}</span>
            </div>
            <div class="ss-panel-body" style="min-height:300px;">
                @if(count($chartLabels) > 0)
                    <canvas id="salesTrendChart" height="260"></canvas>
                @else
                    <p style="color:#aaa;text-align:center;padding-top:60px;">No sales data for this period.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Summary Table --}}
    <div class="ss-panel">
        <div class="ss-panel-hdr">
            <h5><i class="fas fa-receipt"></i> Summary Details</h5>
            <div>
                <button type="button" class="btn btn-sm btn-success" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
        <div style="padding:0;">
            <table class="ss-summary-table">
                <thead>
                    <tr>
                        <th style="width:65%;">Metric</th>
                        <th class="text-right" style="width:35%;">Amount ({{ currency()->symbol ?? '' }})</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding-left:1rem;font-weight:600;">Subtotal</td>
                        <td class="text-right">{{ number_format($sub_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left:1rem;color:#c62828;">Less: Total Discount</td>
                        <td class="text-right" style="color:#c62828;">({{ number_format($discount, 2) }})</td>
                    </tr>
                    <tr style="background:#f8f9fc;">
                        <td style="padding-left:1rem;font-weight:700;">Total Sold</td>
                        <td class="text-right" style="font-weight:700;border-top:1px solid #333;border-bottom:1px solid #333;">
                            {{ number_format($total, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left:1rem;font-weight:600;">Customer Paid</td>
                        <td class="text-right" style="color:#2e7d32;font-weight:600;">
                            {{ number_format($paid, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left:1rem;font-weight:600;">Customer Due</td>
                        <td class="text-right" style="color:#ef6c00;font-weight:600;">
                            {{ number_format($due, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(function() {
        // ── Date range picker ──
        var urlParams = new URLSearchParams(window.location.search);
        var startDate = urlParams.get('start_date') || "{{ $start_date_raw }}";
        var endDate = urlParams.get('end_date') || "{{ $end_date_raw }}";

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
        }, function(start, end) {
            window.location.href = '{{ route("backend.admin.sale.summery") }}?start_date=' + start.format('YYYY-MM-DD') + '&end_date=' + end.format('YYYY-MM-DD');
        });

        $('#daterange-btn span').html(
            moment(startDate, "YYYY-MM-DD").format('MMMM D, YYYY') + ' - ' + moment(endDate, "YYYY-MM-DD").format('MMMM D, YYYY')
        );

        // ── Donut Chart: Sales by Category ──
        var categoryLabels = @json($categoryLabels);
        var categoryTotals = @json($categoryTotals);

        if (categoryLabels.length > 0) {
            var donutCtx = document.getElementById('categoryDonutChart').getContext('2d');
            var donutColors = [
                '#8e5fd9', '#43a047', '#0288d1', '#ef6c00', '#c62828',
                '#00897b', '#5c6bc0', '#f4511e', '#6d4c41', '#78909c'
            ];

            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryTotals,
                        backgroundColor: donutColors.slice(0, categoryLabels.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '55%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 16,
                                usePointStyle: true,
                                pointStyleWidth: 10,
                                font: { size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#333',
                            titleFont: { size: 13 },
                            bodyFont: { size: 13 },
                            padding: 10,
                            callbacks: {
                                label: function(ctx) {
                                    var val = ctx.parsed;
                                    var total = ctx.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                    var pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return '{{ currency()->symbol ?? "KES" }} ' + val.toLocaleString(undefined, {minimumFractionDigits:2}) + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // ── Line Chart: Sales Trend ──
        var chartLabels = @json($chartLabels);
        var chartData = @json($chartData);

        if (chartLabels.length > 0) {
            var lineCtx = document.getElementById('salesTrendChart').getContext('2d');
            var gradient = lineCtx.createLinearGradient(0, 0, 0, 260);
            gradient.addColorStop(0, 'rgba(142,95,217,0.35)');
            gradient.addColorStop(1, 'rgba(142,95,217,0.02)');

            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Sales ({{ currency()->symbol ?? "KES" }})',
                        data: chartData,
                        borderColor: '#8e5fd9',
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2.5,
                        pointBackgroundColor: '#8e5fd9',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#333',
                            titleFont: { size: 13 },
                            bodyFont: { size: 13 },
                            padding: 10,
                            callbacks: {
                                label: function(ctx) {
                                    return '{{ currency()->symbol ?? "KES" }} ' + ctx.parsed.y.toLocaleString(undefined, {minimumFractionDigits:2});
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#888', font: { size: 11 }, maxRotation: 45 } },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f0f0f0' },
                            ticks: {
                                color: '#888',
                                font: { size: 11 },
                                callback: function(v) { return '{{ currency()->symbol ?? "KES" }} ' + v.toLocaleString(); }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
