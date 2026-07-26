@extends('backend.master')

@section('title', 'Inventory Overview')

@section('content')
<style>
    .inv-kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
    .inv-kpi-card {
        border-radius: 10px; padding: 22px 20px 16px; color: #fff; position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,.12); overflow: hidden; min-height: 130px;
    }
    .inv-kpi-card .kpi-icon {
        position: absolute; top: 18px; right: 18px; width: 44px; height: 44px;
        border-radius: 50%; background: rgba(255,255,255,.22); display: flex;
        align-items: center; justify-content: center; font-size: 20px;
    }
    .inv-kpi-card .kpi-label { text-transform: uppercase; font-size: 11px; letter-spacing: .8px; opacity: .85; font-weight: 600; }
    .inv-kpi-card .kpi-value { font-size: 28px; font-weight: 700; margin: 6px 0 12px; }
    .inv-kpi-card .kpi-divider { border-top: 1px solid rgba(255,255,255,.3); margin-bottom: 8px; }
    .inv-kpi-card .kpi-footer { font-size: 12px; opacity: .9; }
    .kpi-blue  { background: linear-gradient(135deg, #0288d1, #29b6f6); }
    .kpi-green { background: linear-gradient(135deg, #43a047, #7fd858); }
    .kpi-cyan  { background: linear-gradient(135deg, #00838f, #26c6da); }
    .kpi-red   { background: linear-gradient(135deg, #c62828, #ef5350); }

    .inv-panel { background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,.06); overflow: hidden; }
    .inv-panel-header { display: flex; align-items: center; padding: 14px 20px; border-bottom: 1px solid #eef0f4; }
    .inv-panel-header h5 { margin: 0; font-size: 15px; font-weight: 600; color: #333; }
    .inv-panel-header h5 i { margin-right: 8px; }
    .inv-panel-body { padding: 20px; }

    .inv-charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }

    .stock-mini-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
    .stock-mini-card { border-radius: 8px; padding: 16px 12px; text-align: center; }
    .stock-mini-card .smc-value { font-size: 28px; font-weight: 700; }
    .stock-mini-card .smc-label { font-size: 12px; color: #888; margin-top: 2px; font-weight: 500; }
    .smc-green  { background: #e8f5e9; }
    .smc-green  .smc-value { color: #2e7d32; }
    .smc-amber  { background: #fff8e1; }
    .smc-amber  .smc-value { color: #f57f17; }
    .smc-red    { background: #fce4ec; }
    .smc-red    .smc-value { color: #c62828; }

    .inv-legend { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px 12px; margin-top: 12px; }
    .inv-legend-item { display: flex; align-items: center; font-size: 12px; color: #555; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .inv-legend-swatch { width: 10px; height: 10px; border-radius: 3px; margin-right: 6px; flex-shrink: 0; }

    .inv-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .inv-table thead th {
        color: #888; font-weight: 600; text-transform: uppercase; font-size: 11px;
        letter-spacing: .4px; padding: 10px 12px; border-bottom: 2px solid #eef0f4;
        background: #f8f9fc; position: sticky; top: 0; z-index: 1;
    }
    .inv-table thead th.text-right { text-align: right; }
    .inv-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #444; }
    .inv-table tbody td.text-right { text-align: right; }
    .inv-table tbody tr:hover { background: #fafafe; }

    .inv-scroll { max-height: 600px; overflow-y: auto; }
    .inv-scroll::-webkit-scrollbar { width: 6px; }
    .inv-scroll::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }

    .badge-low { background: #fff3e0; color: #e65100; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-out { background: #fce4ec; color: #c62828; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-ok  { background: #e8f5e9; color: #2e7d32; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }

    @media (max-width: 991px) {
        .inv-kpi-row { grid-template-columns: repeat(2, 1fr); }
        .inv-charts-row { grid-template-columns: 1fr; }
    }
    @media (max-width: 575px) {
        .inv-kpi-row { grid-template-columns: 1fr; }
    }
</style>

    <div class="inv-kpi-row">
        <div class="inv-kpi-card kpi-blue">
            <div class="kpi-icon"><i class="fas fa-box"></i></div>
            <div class="kpi-label">Total Products</div>
            <div class="kpi-value">{{ number_format($totalProducts) }}</div>
        </div>
        <div class="inv-kpi-card kpi-green">
            <div class="kpi-icon"><i class="fas fa-cubes"></i></div>
            <div class="kpi-label">Total Quantity</div>
            <div class="kpi-value">{{ number_format($totalQuantity) }}</div>
        </div>
        <div class="inv-kpi-card kpi-cyan">
            <div class="kpi-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="kpi-label">Inventory Value</div>
            <div class="kpi-value">{{ $currencySymbol }} {{ number_format($inventoryValue, 2) }}</div>
        </div>
        <div class="inv-kpi-card kpi-red">
            <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="kpi-label">Out of Stock</div>
            <div class="kpi-value">{{ number_format($outOfStock) }}</div>
            <div class="kpi-divider"></div>
            <div class="kpi-footer">Low: {{ $lowStock }}</div>
        </div>
    </div>

    <div class="inv-charts-row">
        {{-- ── Line: Category Trends ── --}}
        <div class="inv-panel">
            <div class="inv-panel-header">
                <h5><i class="fas fa-chart-bar" style="color:#8e5fd9;"></i> Category Trends (Last 7 Days)</h5>
            </div>
            <div class="inv-panel-body" style="padding:20px;height:380px;display:flex;flex-direction:column;">
                <div style="flex:1;position:relative;min-height:0;">
                    <canvas id="invCategoryTrend" style="position:absolute;top:0;left:0;width:100%;height:100%;"></canvas>
                </div>
                <div class="inv-legend" id="invLegend" style="margin-top:10px;flex-shrink:0;"></div>
            </div>
        </div>

        {{-- ── Stock Status Breakdown ── --}}
        <div class="inv-panel">
            <div class="inv-panel-header">
                <h5><i class="fas fa-chart-pie" style="color:#0288d1;"></i> Stock Status Breakdown</h5>
            </div>
            <div class="inv-panel-body" style="height:380px;display:flex;flex-direction:column;">
                <div class="stock-mini-row">
                    <div class="stock-mini-card smc-green">
                        <div class="smc-value">{{ $inStock }}</div>
                        <div class="smc-label">In Stock</div>
                    </div>
                    <div class="stock-mini-card smc-amber">
                        <div class="smc-value">{{ $lowStock }}</div>
                        <div class="smc-label">Low Stock</div>
                    </div>
                    <div class="stock-mini-card smc-red">
                        <div class="smc-value">{{ $outOfStock }}</div>
                        <div class="smc-label">Out of Stock</div>
                    </div>
                </div>
                <hr style="border:none;border-top:1px solid #eef0f4;margin:4px 0 12px;flex-shrink:0;">
                <div class="table-responsive" style="flex:1;overflow-y:auto;min-height:0;">
                    <table class="inv-table">
                        <thead>
                            <tr>
                                <th>CATEGORY</th>
                                <th>ITEMS</th>
                                <th>QTY</th>
                                <th class="text-right">VALUE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categoryData as $cat)
                            <tr>
                                <td>{{ $cat['name'] }}</td>
                                <td>{{ $cat['items'] }}</td>
                                <td>{{ number_format($cat['qty']) }}</td>
                                <td class="text-right">{{ $currencySymbol }} {{ number_format($cat['value'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No categories found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Top 20 Items ── --}}
    <div class="inv-panel">
        <div class="inv-panel-header">
            <h5><i class="fas fa-list-alt" style="color:#43a047;"></i> Items in Stock</h5>
        </div>
        <div class="inv-panel-body" style="padding:0;">
            <div class="inv-scroll">
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>PRODUCT</th>
                            <th>CATEGORY</th>
                            <th>SKU</th>
                            <th>QTY</th>
                            <th class="text-right">UNIT COST</th>
                            <th class="text-right">STOCK VALUE</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $i => $p)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $p->name }}</strong></td>
                            <td>{{ $p->category->name ?? '-' }}</td>
                            <td>{{ $p->sku ?? '-' }}</td>
                            <td>{{ number_format($p->quantity) }}</td>
                            <td class="text-right">{{ $currencySymbol }} {{ number_format($p->purchase_price, 2) }}</td>
                            <td class="text-right">{{ $currencySymbol }} {{ number_format($p->quantity * $p->purchase_price, 2) }}</td>
                            <td>
                                @if($p->quantity == 0)
                                    <span class="badge-out">Out of Stock</span>
                                @elseif($p->quantity <= 10)
                                    <span class="badge-low">Low Stock</span>
                                @else
                                    <span class="badge-ok">In Stock</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No products found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
    var labels = {!! json_encode($chartLabels) !!};
    var datasets = {!! json_encode($chartDatasets) !!};

    var palette = [
        '#42a5f5','#66bb6a','#fdd835','#ef5350','#26c6da',
        '#ab47bc','#37474f','#ff8a65','#78909c','#8d6e63'
    ];

    var canvas = document.getElementById('invCategoryTrend');
    if (!canvas) return;

    var ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets.map(function(ds, i) {
                return {
                    label: ds.label,
                    data: ds.data,
                    backgroundColor: palette[i % palette.length] + 'cc',
                    borderColor: palette[i % palette.length],
                    borderWidth: 1,
                    borderRadius: 3,
                    barPercentage: 0.7,
                    categoryPercentage: 0.8
                };
            })
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: {
                    beginAtZero: true,
                    stacked: false,
                    ticks: { callback: function(v) { return 'KES ' + v.toLocaleString(); } },
                    grid: { color: '#f3f4f6' }
                },
                x: { stacked: false, grid: { display: false } }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ' ' + ctx.dataset.label + ': KES ' + ctx.parsed.y.toLocaleString(undefined, {minimumFractionDigits:2});
                        }
                    }
                }
            }
        }
    });

    var legendEl = document.getElementById('invLegend');
    datasets.forEach(function(ds, i) {
        var item = document.createElement('div');
        item.className = 'inv-legend-item';
        item.innerHTML = '<span class="inv-legend-swatch" style="background:' + palette[i % palette.length] + '"></span>' + ds.label;
        legendEl.appendChild(item);
    });
})();
</script>
@endpush
