<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LPO {{ $lpo->lpo_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            background: #fff;
            padding: 30px 40px;
        }

        /* ── Header ── */
        .lpo-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #2d5be3;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .lpo-header .brand { font-size: 22px; font-weight: 700; color: #2d5be3; }
        .lpo-header .brand small { display: block; font-size: 12px; font-weight: 400; color: #555; }
        .lpo-header .lpo-meta { text-align: right; }
        .lpo-header .lpo-meta .lpo-num { font-size: 18px; font-weight: 700; color: #2d5be3; }
        .lpo-header .lpo-meta p { margin: 2px 0; color: #555; }

        /* ── Section title ── */
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #2d5be3;
            border-bottom: 1px solid #dde;
            padding-bottom: 4px;
            margin: 18px 0 10px;
        }

        /* ── Two-col info grid ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 24px;
            margin-bottom: 8px;
        }
        .info-grid .lbl { color: #777; font-size: 11px; }
        .info-grid .val { font-weight: 600; }

        /* ── Items table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        thead th {
            background: #2d5be3;
            color: #fff;
            padding: 7px 10px;
            text-align: left;
            font-size: 12px;
        }
        thead th.r { text-align: right; }
        tbody tr:nth-child(even) { background: #f4f6fb; }
        tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #e8eaf0;
            vertical-align: middle;
        }
        tbody td.r { text-align: right; }
        tfoot td {
            padding: 8px 10px;
            font-weight: 700;
            border-top: 2px solid #2d5be3;
        }
        tfoot td.r { text-align: right; color: #2d5be3; font-size: 14px; }

        /* ── Signatures ── */
        .sig-row {
            display: flex;
            justify-content: space-between;
            margin-top: 48px;
            gap: 32px;
        }
        .sig-box { flex: 1; border-top: 1px solid #aaa; padding-top: 6px; }
        .sig-box .sig-name { font-weight: 600; }
        .sig-box .sig-role { color: #777; font-size: 11px; }

        /* ── Footer ── */
        .lpo-footer {
            margin-top: 32px;
            border-top: 1px solid #dde;
            padding-top: 10px;
            font-size: 11px;
            color: #888;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            background: #e8f0fe;
            color: #2d5be3;
        }

        /* ── Print tweaks ── */
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    {{-- ── Print / Close toolbar (hidden on print) ── --}}
    <div class="no-print" style="margin-bottom:20px; display:flex; gap:10px;">
        <button onclick="window.print()"
                style="padding:8px 20px;background:#2d5be3;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;">
            🖨 Print / Save PDF
        </button>
        <button onclick="window.close()"
                style="padding:8px 16px;background:#6c757d;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;">
            ✕ Close
        </button>
    </div>

    {{-- ── LPO Header ── --}}
    <div class="lpo-header">
        <div class="brand">
            {{ readConfig('site_name') ?? config('app.name') }}
            <small>{{ readConfig('address') ?? '' }}</small>
        </div>
        <div class="lpo-meta">
            <div class="lpo-num">{{ $lpo->lpo_number }}</div>
            <p>Date: {{ optional($lpo->issued_at)->format('d M Y') ?? now()->format('d M Y') }}</p>
            <p>
                <span class="status-badge">{{ strtoupper(str_replace('_', ' ', $lpo->status)) }}</span>
            </p>
        </div>
    </div>

    {{-- ── Supplier info ── --}}
    <div class="section-title">Supplier Details</div>
    <div class="info-grid">
        <div>
            <div class="lbl">Supplier Name</div>
            <div class="val">{{ $lpo->supplier->name }}</div>
        </div>
        @if($lpo->supplier->phone)
        <div>
            <div class="lbl">Phone</div>
            <div class="val">{{ $lpo->supplier->phone }}</div>
        </div>
        @endif
        @if($lpo->supplier->email)
        <div>
            <div class="lbl">Email</div>
            <div class="val">{{ $lpo->supplier->email }}</div>
        </div>
        @endif
        @if($lpo->supplier->address)
        <div>
            <div class="lbl">Address</div>
            <div class="val">{{ $lpo->supplier->address }}</div>
        </div>
        @endif
    </div>

    @if($lpo->notes)
    <div style="margin-top:10px; padding:8px 12px; background:#fffbe6; border-left:3px solid #f0ad00; font-size:12px;">
        <strong>Notes:</strong> {{ $lpo->notes }}
    </div>
    @endif

    {{-- ── Order lines ── --}}
    <div class="section-title">Order Line Items</div>
    <table>
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:42%">Product / Description</th>
                <th style="width:10%" class="r">Unit</th>
                <th style="width:13%" class="r">Qty Ordered</th>
                <th style="width:15%" class="r">Unit Cost</th>
                <th style="width:15%" class="r">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lpo->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td class="r">{{ optional($item->product->unit)->short_name ?? '-' }}</td>
                <td class="r">{{ number_format($item->qty_ordered) }}</td>
                <td class="r">{{ number_format($item->unit_cost, 2) }}</td>
                <td class="r">{{ number_format($item->total_cost, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align:right;">Grand Total</td>
                <td class="r">{{ number_format($lpo->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ── Delivery instructions ── --}}
    <div class="section-title">Delivery Instructions</div>
    <p style="color:#444; font-size:12px; line-height:1.6;">
        Please deliver goods to our premises at the address above.
        All deliveries must be accompanied by a delivery note quoting this LPO number
        <strong>{{ $lpo->lpo_number }}</strong>.
        Goods will only be accepted during business hours (Mon–Fri, 08:00–17:00).
    </p>

    {{-- ── Signature block ── --}}
    <div class="sig-row">
        <div class="sig-box">
            <div class="sig-name">Prepared By</div>
            <div class="sig-role">Procurement / Requisition Officer</div>
            <div style="margin-top:4px; color:#999; font-size:11px;">Date: ___________________</div>
        </div>
        <div class="sig-box">
            <div class="sig-name">Approved By</div>
            <div class="sig-role">Finance / Management</div>
            <div style="margin-top:4px; color:#999; font-size:11px;">Date: ___________________</div>
        </div>
        <div class="sig-box">
            <div class="sig-name">Received By (Supplier)</div>
            <div class="sig-role">Authorised Supplier Representative</div>
            <div style="margin-top:4px; color:#999; font-size:11px;">Date: ___________________</div>
        </div>
    </div>

    {{-- ── Footer ── --}}
    <div class="lpo-footer">
        This is a computer-generated Local Purchase Order.
        &nbsp;|&nbsp; {{ $lpo->lpo_number }}
        &nbsp;|&nbsp; Generated {{ now()->format('d M Y H:i') }}
    </div>

</body>
</html>
