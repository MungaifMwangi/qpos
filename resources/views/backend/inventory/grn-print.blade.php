<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $grn->grn_number }} — Print</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #333; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 3px solid #5b5ea6; padding-bottom: 16px; }
        .header h1 { font-size: 22px; color: #5b5ea6; margin-bottom: 4px; }
        .header .subtitle { font-size: 11px; color: #999; }
        .grn-title { text-align: right; }
        .grn-title .number { font-size: 18px; font-weight: 700; color: #303030; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; background: #f8f8fa; padding: 14px 18px; border-radius: 8px; border: 1px solid #e8e8e8; }
        .info-item label { display: block; font-size: 10px; color: #999; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 2px; }
        .info-item span { font-size: 13px; font-weight: 600; color: #303030; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th { background: #5b5ea6; color: #fff; padding: 10px 14px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        table th:last-child, table td:last-child { text-align: right; }
        table th:nth-child(3), table td:nth-child(3) { text-align: right; }
        table td { padding: 10px 14px; border-bottom: 1px solid #eee; font-size: 12px; }
        table tr:last-child td { border-bottom: none; }
        .totals { display: flex; justify-content: flex-end; margin-bottom: 24px; }
        .totals-box { background: #f8f8fa; border: 1px solid #e8e8e8; border-radius: 8px; padding: 12px 20px; min-width: 250px; }
        .totals-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; }
        .totals-row.grand { border-top: 2px solid #5b5ea6; margin-top: 6px; padding-top: 8px; font-size: 15px; font-weight: 700; color: #5b5ea6; }
        .footer { margin-top: 30px; padding-top: 14px; border-top: 1px solid #e8e8e8; display: flex; justify-content: space-between; font-size: 11px; color: #999; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; }
        .sig-box { border-top: 1px solid #ccc; padding-top: 6px; text-align: center; font-size: 11px; color: #666; }
        @media print {
            body { padding: 15px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:right;margin-bottom:12px">
        <button onclick="window.print()" style="background:#5b5ea6;color:#fff;border:none;padding:8px 20px;border-radius:6px;cursor:pointer;font-weight:600">
            Print
        </button>
    </div>

    <div class="header">
        <div>
            <h1>Goods Received Note</h1>
            <div class="subtitle">CashUP PoS — Inventory</div>
        </div>
        <div class="grn-title">
            <div class="number">{{ $grn->grn_number }}</div>
            <div class="subtitle">Received: {{ \Carbon\Carbon::parse($grn->received_date)->format('d M Y') }}</div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <label>Supplier</label>
            <span>{{ $grn->supplier->name ?? '—' }}</span>
        </div>
        <div class="info-item">
            <label>Source</label>
            <span>
                @if($grn->lpo)
                    LPO: {{ $grn->lpo->lpo_number }}
                @elseif($grn->purchase)
                    Direct Purchase #{{ $grn->purchase->id }}
                @else
                    —
                @endif
            </span>
        </div>
        <div class="info-item">
            <label>Received By</label>
            <span>{{ $receivedByName }}</span>
        </div>
        <div class="info-item">
            <label>Total Items</label>
            <span>{{ $grn->items->count() }}</span>
        </div>
    </div>

    @if($grn->notes)
    <div style="background:#fff8e1;border:1px solid #ffecb3;border-radius:8px;padding:10px 14px;margin-bottom:20px;font-size:12px">
        <strong style="color:#f57f17">Notes:</strong> {{ $grn->notes }}
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Product</th>
                <th>Qty Received</th>
                <th>Unit Cost</th>
                <th>Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grn->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product->name ?? '—' }}</td>
                <td style="text-align:right">{{ $item->qty_received }}</td>
                <td style="text-align:right">{{ number_format($item->unit_cost, 2) }}</td>
                <td style="text-align:right;font-weight:600">{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-box">
            <div class="totals-row grand">
                <span>Total Value</span>
                <span>{{ currency()->symbol ?? 'KES' }} {{ number_format($grn->items->sum('line_total'), 2) }}</span>
            </div>
        </div>
    </div>

    <div class="signatures">
        <div class="sig-box">Received by (Signature & Stamp)</div>
        <div class="sig-box">Delivered by (Signature & Stamp)</div>
    </div>

    <div class="footer">
        <span>Generated {{ now()->format('d M Y, H:i') }}</span>
        <span>CashUP PoS</span>
    </div>
</body>
</html>
