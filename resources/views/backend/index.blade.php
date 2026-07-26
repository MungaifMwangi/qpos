@extends('backend.master')

@section('title', 'Dashboard')

@section('content')
<style>
    .dash-kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
    .dash-kpi-card {
        border-radius: 10px; padding: 22px 20px 16px; color: #fff; position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,.12); overflow: hidden; min-height: 130px;
    }
    .dash-kpi-card .kpi-icon {
        position: absolute; top: 18px; right: 18px; width: 44px; height: 44px;
        border-radius: 50%; background: rgba(255,255,255,.22); display: flex;
        align-items: center; justify-content: center; font-size: 20px;
    }
    .dash-kpi-card .kpi-label {
        text-transform: uppercase; font-size: 11px; letter-spacing: .8px; opacity: .85; font-weight: 600;
    }
    .dash-kpi-card .kpi-value { font-size: 28px; font-weight: 700; margin: 6px 0 12px; }
    .dash-kpi-card .kpi-divider { border-top: 1px solid rgba(255,255,255,.3); margin-bottom: 8px; }
    .dash-kpi-card .kpi-footer { font-size: 12px; opacity: .9; }
    .dash-kpi-card .kpi-footer .up { color: #c3fcb3; }
    .dash-kpi-card .kpi-footer a { color: #fff; text-decoration: underline; }
    .kpi-purple  { background: linear-gradient(135deg, #6f42c1, #a77bca); }
    .kpi-green   { background: linear-gradient(135deg, #43a047, #7fd858); }
    .kpi-blue    { background: linear-gradient(135deg, #0288d1, #29b6f6); }
    .kpi-orange  { background: linear-gradient(135deg, #ef6c00, #f5a623); }

    .dash-panel {
        background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,.06);
        padding: 0; margin-bottom: 24px; overflow: hidden;
    }
    .dash-panel-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px; border-bottom: 1px solid #eef0f4;
    }
    .dash-panel-header h5 { margin: 0; font-size: 15px; font-weight: 600; color: #333; }
    .dash-panel-header h5 i { color: #8e5fd9; margin-right: 6px; }
    .dash-panel-body { padding: 20px; }
    .dash-bot-row { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
    .dash-charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    .dash-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .dash-table thead th { background: #f8f9fc; color: #6c757d; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: .5px; padding: 10px 12px; border-bottom: 2px solid #eef0f4; }
    .dash-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #444; vertical-align: middle; }
    .dash-table tbody tr:hover { background: #fafafe; }
    .badge-paid { background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .badge-pending { background: #fff3e0; color: #e65100; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .badge-failed { background: #fce4ec; color: #c62828; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .btn-view-order {
        width: 32px; height: 32px; border-radius: 50%; border: none; cursor: pointer;
        background: #00bcd4; color: #fff; display: inline-flex; align-items: center;
        justify-content: center; font-size: 14px; transition: .2s;
    }
    .btn-view-order:hover { background: #0097a7; transform: scale(1.1); }

    .top-product-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 0; border-bottom: 1px solid #f3f4f6;
    }
    .top-product-row:last-child { border-bottom: none; }
    .top-product-name { font-weight: 600; color: #333; font-size: 14px; }
    .top-product-qty { font-size: 12px; color: #888; margin-top: 2px; }
    .top-product-price {
        background: #e3f2fd; color: #1565c0; padding: 4px 14px; border-radius: 20px;
        font-size: 13px; font-weight: 600; white-space: nowrap;
    }
    .btn-view-all {
        background: #6f42c1; color: #fff; border: none; padding: 5px 16px;
        border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;
    }
    .btn-view-all:hover { background: #5a34a1; }

    @media (max-width: 991px) {
        .dash-kpi-row { grid-template-columns: repeat(2, 1fr); }
        .dash-bot-row { grid-template-columns: 1fr; }
        .dash-charts-row { grid-template-columns: 1fr; }
    }
    @media (max-width: 575px) {
        .dash-kpi-row { grid-template-columns: 1fr; }
    }
</style>

    @can('dashboard_view')
        <div class="dash-kpi-row">
            <div class="dash-kpi-card kpi-purple">
                <div class="kpi-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="kpi-label">Total Revenue</div>
                <div class="kpi-value">{{ currency()->symbol ?? 'KES' }} {{ $totalRevenue }}</div>
                <div class="kpi-divider"></div>
                <div class="kpi-footer">
                    @if($revenueChange > 0)
                        <span class="up">&#8593;</span> {{ $revenueChange }}% increase from last month
                    @elseif($revenueChange < 0)
                        <span style="color:#ffab91;">&#8595;</span> {{ abs($revenueChange) }}% decrease from last month
                    @else
                        0% change from last month
                    @endif
                </div>
            </div>
            <div class="dash-kpi-card kpi-green">
                <div class="kpi-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="kpi-label">Total Sales</div>
                <div class="kpi-value">{{ $totalSales }}</div>
                <div class="kpi-divider"></div>
                <div class="kpi-footer">
                    @if($salesChange > 0)
                        <span class="up">&#8593;</span> {{ $salesChange }}% increase from last month
                    @elseif($salesChange < 0)
                        <span style="color:#ffab91;">&#8595;</span> {{ abs($salesChange) }}% decrease from last month
                    @else
                        0% change from last month
                    @endif
                </div>
            </div>
            <div class="dash-kpi-card kpi-blue">
                <div class="kpi-icon"><i class="fas fa-users"></i></div>
                <div class="kpi-label">Total Customers</div>
                <div class="kpi-value">{{ $totalCustomers }}</div>
                <div class="kpi-divider"></div>
                <div class="kpi-footer">&#8593; {{ $newCustomersThisMonth }} new this month</div>
            </div>
            <div class="dash-kpi-card kpi-orange">
                <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="kpi-label">Low Stock Items</div>
                <div class="kpi-value">{{ $lowStockCount }}</div>
                <div class="kpi-divider"></div>
                <div class="kpi-footer">
                    <a href="{{ route('backend.admin.inventory.report') }}">&#128065; View Details</a>
                </div>
            </div>
        </div>

        <div class="dash-panel" style="margin-bottom:24px;">
            <div class="dash-charts-row">
                <div>
                    <div class="dash-panel-header">
                        <h5><i class="fas fa-chart-line"></i> Sales Overview (Last 7 Days)</h5>
                    </div>
                    <div class="dash-panel-body">
                        <canvas id="salesOverviewChart" height="260"></canvas>
                    </div>
                </div>
                <div>
                    <div class="dash-panel-header">
                        <h5><i class="fas fa-chart-pie"></i> Sales by Category (Last 7 Days)</h5>
                    </div>
                    <div class="dash-panel-body" style="display:flex;justify-content:center;align-items:center;min-height:292px;">
                        @if(count($categoryLabels) > 0)
                            <canvas id="categoryDonutChart" style="max-height:260px;"></canvas>
                        @else
                            <p style="color:#aaa;">No category data yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-bot-row">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h5><i class="fas fa-receipt"></i> Recent Sales</h5>
                    <a href="{{ route('backend.admin.orders.index') }}" class="btn-view-all">View All</a>
                </div>
                <div class="dash-panel-body" style="padding:0;">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $order)
                            <tr>
                                <td><strong>#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>{{ $order->customer->name ?? '-' }}</td>
                                <td>{{ $order->created_at->format('d M Y') }}</td>
                                <td><strong>{{ currency()->symbol ?? 'KES' }} {{ number_format($order->total, 2) }}</strong></td>
                                <td>
                                    @if($order->payment_status === 'paid')
                                        <span class="badge-paid">Paid</span>
                                    @elseif($order->payment_status === 'pending')
                                        <span class="badge-pending">Pending</span>
                                    @else
                                        <span class="badge-failed">{{ ucfirst($order->payment_status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('backend.admin.orders.pos-invoice', $order->id) }}" class="btn-view-order" title="View Order">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center" style="padding:30px;color:#aaa;">No recent sales found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h5><i class="fas fa-star" style="color:#f5a623;"></i> Top 5 Movers</h5>
                </div>
                <div class="dash-panel-body">
                    @forelse($topProducts as $product)
                    <div class="top-product-row">
                        <div>
                            <div class="top-product-name">{{ $product['name'] }}</div>
                            <div class="top-product-qty">{{ $product['quantity_sold'] }} units sold</div>
                        </div>
                        <span class="top-product-price">{{ currency()->symbol ?? 'KES' }} {{ $product['revenue'] }}</span>
                    </div>
                    @empty
                    <p class="text-center" style="padding:30px;color:#aaa;">No product data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const lineCtx = document.getElementById('salesOverviewChart').getContext('2d');
    const gradient = lineCtx.createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, 'rgba(142,95,217,0.35)');
    gradient.addColorStop(1, 'rgba(142,95,217,0.02)');

    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Sales (KES)',
                data: @json($chartData),
                borderColor: '#8e5fd9',
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                borderWidth: 2.5,
                pointBackgroundColor: '#8e5fd9',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
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
                        label: ctx => 'KES ' + ctx.parsed.y.toLocaleString(undefined, {minimumFractionDigits:2})
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#888', font: { size: 12 } } },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0' },
                    ticks: { color: '#888', font: { size: 12 }, callback: v => 'KES ' + v.toLocaleString() }
                }
            }
        }
    });

    // ── Donut Chart: Sales by Category (Last 7 Days) ──
    var catLabels = @json($categoryLabels);
    var catTotals = @json($categoryTotals);

    if (catLabels.length > 0) {
        var donutCtx = document.getElementById('categoryDonutChart').getContext('2d');
        var donutColors = [
            '#8e5fd9', '#43a047', '#0288d1', '#ef6c00', '#c62828',
            '#00897b', '#5c6bc0', '#f4511e', '#6d4c41', '#78909c'
        ];

        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catTotals,
                    backgroundColor: donutColors.slice(0, catLabels.length),
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
                        labels: { padding: 14, usePointStyle: true, pointStyleWidth: 10, font: { size: 12 } }
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
                                return 'KES ' + val.toLocaleString(undefined, {minimumFractionDigits:2}) + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
