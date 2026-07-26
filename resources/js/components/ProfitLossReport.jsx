import React, { useState, useEffect, useCallback } from 'react';

const PERIODS = [
    { value: 'today', label: 'Today' },
    { value: 'yesterday', label: 'Yesterday' },
    { value: 'this_week', label: 'This Week' },
    { value: 'last_week', label: 'Last Week' },
    { value: 'this_month', label: 'This Month' },
    { value: 'last_month', label: 'Last Month' },
    { value: 'this_quarter', label: 'This Quarter' },
    { value: 'last_quarter', label: 'Last Quarter' },
    { value: 'ytd', label: 'Year to Date' },
    { value: 'this_year', label: 'This Year' },
    { value: 'last_year', label: 'Last Year' },
    { value: 'custom', label: 'Custom Range' },
];

const PL_STYLES = `
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
.kpi-purple  { background: linear-gradient(135deg, #6f42c1, #a77bca); }
.kpi-green   { background: linear-gradient(135deg, #43a047, #7fd858); }
.kpi-blue    { background: linear-gradient(135deg, #0288d1, #29b6f6); }
.kpi-orange  { background: linear-gradient(135deg, #ef6c00, #f5a623); }
.kpi-red     { background: linear-gradient(135deg, #c62828, #ef5350); }

.pl-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 24px; }
.pl-controls { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; margin-bottom: 22px; }
.pl-panel { background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,.06); overflow: hidden; margin-bottom: 24px; }
.pl-panel-hdr { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid #eef0f4; }
.pl-panel-hdr h5 { margin: 0; font-size: 15px; font-weight: 600; color: #333; }
.pl-panel-hdr h5 i { color: #8e5fd9; margin-right: 6px; }
.pl-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.pl-table thead th { background: #f8f9fc; color: #6c757d; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: .5px; padding: 12px 16px; border-bottom: 2px solid #eef0f4; }
.pl-table tbody td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; color: #444; vertical-align: middle; }
.pl-table tbody tr:hover { background: #fafafe; }
.pl-net-row { background: #dfedd6 !important; font-size: 1.08rem; }
.pl-net-row td { border-top: 2px solid #333 !important; border-bottom: 2px solid #333 !important; font-weight: 700 !important; }
.pl-toggle { position: relative; display: inline-block; width: 44px; height: 24px; }
.pl-toggle input { opacity: 0; width: 0; height: 0; }
.pl-toggle .slider { position: absolute; inset: 0; background: #ccc; border-radius: 24px; cursor: pointer; transition: .25s; }
.pl-toggle .slider::before { content: ''; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .25s; }
.pl-toggle input:checked + .slider { background: #6f42c1; }
.pl-toggle input:checked + .slider::before { transform: translateX(20px); }
.pl-pct-label { font-size: 12px; font-weight: 600; color: #666; margin-left: 6px; }
.pl-actions { display: flex; gap: 10px; align-items: center; }
.pl-spinner { display: flex; justify-content: center; align-items: center; min-height: 300px; }
.pl-spinner i { font-size: 36px; color: #8e5fd9; animation: plSpin 1s linear infinite; }
@keyframes plSpin { 100% { transform: rotate(360deg); } }
@media (max-width: 991px) { .pl-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 575px) { .pl-grid { grid-template-columns: 1fr; } }
`;

function fmt(val, showPct, revenue, currency) {
    if (showPct) {
        if (revenue === 0) return '0.0%';
        return ((val / revenue) * 100).toFixed(1) + '%';
    }
    return currency + ' ' + Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function KpiCard({ label, value, icon, colorClass, showPct, revenue, currency }) {
    const display = showPct && revenue > 0
        ? ((value / revenue) * 100).toFixed(1) + '%'
        : currency + ' ' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    return (
        <div className={'dash-kpi-card ' + colorClass} style={{ minWidth: 0 }}>
            <div className="kpi-icon"><i className={'fas ' + icon}></i></div>
            <div className="kpi-label">{label}</div>
            <div className="kpi-value" style={{ fontSize: 22 }}>{display}</div>
        </div>
    );
}

function ExpandableRow({ label, total, items, currency, showPct, revenue, defaultOpen, bold, color, bgColor }) {
    const [open, setOpen] = useState(defaultOpen || false);
    const keys = Object.keys(items || {});

    return (
        <React.Fragment>
            <tr
                style={{
                    cursor: keys.length ? 'pointer' : 'default',
                    background: bgColor || undefined,
                    fontWeight: bold ? 600 : undefined,
                }}
                onClick={() => keys.length && setOpen(!open)}
            >
                <td style={{ paddingLeft: '1rem' }}>
                    {keys.length > 0 && (
                        <i
                            className={'fas fa-chevron-' + (open ? 'down' : 'right')}
                            style={{ fontSize: 11, marginRight: 8, color: '#888', transition: 'transform .15s' }}
                        />
                    )}
                    {label}
                </td>
                <td className="text-right" style={{ color: color || '#333', fontWeight: bold ? 700 : 500 }}>
                    {fmt(total, showPct, revenue, currency)}
                </td>
            </tr>
            {open && keys.map((k) => (
                <tr key={k}>
                    <td style={{ paddingLeft: '3rem', color: '#555', fontSize: 13 }}>{k}</td>
                    <td className="text-right" style={{ color: '#555', fontSize: 13 }}>
                        {fmt(items[k], showPct, revenue, currency)}
                    </td>
                </tr>
            ))}
        </React.Fragment>
    );
}

function NetRow({ label, value, currency, showPct, revenue, isProfit }) {
    const color = isProfit && value >= 0 ? '#2e7d32' : (!isProfit || value < 0) ? '#c62828' : '#2e7d32';
    return (
        <tr className="pl-net-row">
            <td style={{ paddingLeft: '1rem' }}><strong>{label}</strong></td>
            <td className="text-right" style={{ color }}>
                <strong>{fmt(value, showPct, revenue, currency)}</strong>
            </td>
        </tr>
    );
}

export default function ProfitLossReport() {
    const [period, setPeriod] = useState('this_month');
    const [customFrom, setCustomFrom] = useState('');
    const [customTo, setCustomTo] = useState('');
    const [showPct, setShowPct] = useState(false);
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchData = useCallback(async () => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ period: period });
            if (period === 'custom' && customFrom && customTo) {
                params.set('from', customFrom);
                params.set('to', customTo);
            }
            const res = await fetch('/admin/profit-loss/api?' + params.toString());
            if (!res.ok) throw new Error('Failed to fetch data');
            const json = await res.json();
            setData(json);
        } catch (e) {
            setError(e.message);
        } finally {
            setLoading(false);
        }
    }, [period, customFrom, customTo]);

    useEffect(function () { fetchData(); }, [fetchData]);

    const currency = data && data.currency ? data.currency : 'KES';
    const revenue = data && data.revenue ? data.revenue.total : 0;
    const cogs = data ? data.cogs : 0;
    const grossProfit = revenue - cogs;
    const opex = data && data.opex ? data.opex.total : 0;
    const netProfit = grossProfit - opex;

    var printStatement = function () { window.print(); };

    if (loading && !data) {
        return (
            <div className="pl-spinner">
                <i className="fas fa-spinner"></i>
            </div>
        );
    }

    if (error) {
        return (
            <div className="alert alert-danger">
                <i className="fas fa-exclamation-triangle mr-2"></i>
                {error}
                <button className="btn btn-sm btn-outline-danger ml-3" onClick={fetchData}>Retry</button>
            </div>
        );
    }

    return (
        <React.Fragment>
            <style>{PL_STYLES}</style>

            {/* Controls bar */}
            <div className="pl-controls">
                <div className="form-group mb-0">
                    <select
                        className="form-control form-control-sm"
                        value={period}
                        onChange={function (e) { setPeriod(e.target.value); }}
                        style={{ minWidth: 160 }}
                    >
                        {PERIODS.map(function (p) {
                            return <option key={p.value} value={p.value}>{p.label}</option>;
                        })}
                    </select>
                </div>

                {period === 'custom' && (
                    <div className="form-group mb-0 d-flex align-items-center gap-2">
                        <input
                            type="date"
                            className="form-control form-control-sm"
                            value={customFrom}
                            onChange={function (e) { setCustomFrom(e.target.value); }}
                        />
                        <span className="text-muted">to</span>
                        <input
                            type="date"
                            className="form-control form-control-sm"
                            value={customTo}
                            onChange={function (e) { setCustomTo(e.target.value); }}
                        />
                    </div>
                )}

                <div className="d-flex align-items-center ml-auto">
                    <span className="pl-pct-label" style={{ marginRight: 6 }}>% of Revenue</span>
                    <label className="pl-toggle mb-0">
                        <input
                            type="checkbox"
                            checked={showPct}
                            onChange={function (e) { setShowPct(e.target.checked); }}
                        />
                        <span className="slider"></span>
                    </label>
                </div>

                <div className="pl-actions">
                    <button className="btn btn-sm btn-outline-secondary" onClick={fetchData} title="Refresh">
                        <i className="fas fa-sync-alt"></i>
                    </button>
                    <button className="btn btn-sm btn-success" onClick={printStatement}>
                        <i className="fas fa-print mr-1"></i> Print
                    </button>
                </div>
            </div>

            {/* KPI Cards */}
            <div className="pl-grid">
                <KpiCard
                    label="Revenue"
                    value={revenue}
                    icon="fa-money-bill-wave"
                    colorClass="kpi-purple"
                    showPct={showPct}
                    revenue={revenue}
                    currency={currency}
                />
                <KpiCard
                    label="Gross Profit"
                    value={grossProfit}
                    icon="fa-chart-line"
                    colorClass="kpi-green"
                    showPct={showPct}
                    revenue={revenue}
                    currency={currency}
                />
                <KpiCard
                    label="Operating Expenses"
                    value={opex}
                    icon="fa-file-invoice"
                    colorClass="kpi-blue"
                    showPct={showPct}
                    revenue={revenue}
                    currency={currency}
                />
                <KpiCard
                    label="Net Profit"
                    value={netProfit}
                    icon="fa-piggy-bank"
                    colorClass={netProfit >= 0 ? 'kpi-green' : 'kpi-orange'}
                    showPct={showPct}
                    revenue={revenue}
                    currency={currency}
                />
            </div>

            {/* Statement body */}
            <div className="pl-panel">
                <div className="pl-panel-hdr">
                    <h5>
                        <i className="fas fa-file-invoice-dollar"></i>
                        Profit &amp; Loss Statement
                    </h5>
                    {data && data.period && (
                        <span style={{ fontSize: 13, color: '#888' }}>
                            {data.period.label}
                        </span>
                    )}
                </div>
                <div style={{ padding: '0 4px' }}>
                    <table className="pl-table">
                        <thead>
                            <tr>
                                <th style={{ width: '65%' }}>Account / Category</th>
                                <th className="text-right" style={{ width: '35%' }}>
                                    {showPct ? '% of Revenue' : 'Amount (' + currency + ')'}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {/* Revenue */}
                            <ExpandableRow
                                label="Revenue"
                                total={revenue}
                                items={data && data.revenue ? data.revenue.byCategory : {}}
                                currency={currency}
                                showPct={showPct}
                                revenue={revenue}
                                bold={true}
                                bgColor="#f8f9fc"
                            />

                            {/* COGS */}
                            <tr className="pl-divider">
                                <td style={{ paddingLeft: '1rem', fontWeight: 600 }}>Cost of Goods Sold</td>
                                <td className="text-right" style={{ color: '#c62828', fontWeight: 600 }}>
                                    {'-' + fmt(cogs, showPct, revenue, currency)}
                                </td>
                            </tr>

                            {/* Gross Profit */}
                            <NetRow
                                label="Gross Profit"
                                value={grossProfit}
                                currency={currency}
                                showPct={showPct}
                                revenue={revenue}
                                isProfit={true}
                            />

                            {/* Operating Expenses */}
                            <ExpandableRow
                                label="Operating Expenses"
                                total={opex}
                                items={data && data.opex ? data.opex.byCategory : {}}
                                currency={currency}
                                showPct={showPct}
                                revenue={revenue}
                                bold={true}
                                bgColor="#f8f9fc"
                            />

                            {/* Net Profit */}
                            <NetRow
                                label="Net Profit / (Loss)"
                                value={netProfit}
                                currency={currency}
                                showPct={showPct}
                                revenue={revenue}
                                isProfit={netProfit >= 0}
                            />
                        </tbody>
                    </table>
                </div>
            </div>
        </React.Fragment>
    );
}
