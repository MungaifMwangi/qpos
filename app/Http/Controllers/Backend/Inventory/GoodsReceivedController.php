<?php

namespace App\Http\Controllers\Backend\Inventory;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceiptNote;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class GoodsReceivedController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('grn_receive'), 403);

        if ($request->ajax()) {
            $query = GoodsReceiptNote::with('lpo', 'purchase', 'supplier');

            if ($request->filled('source')) {
                if ($request->source === 'lpo') {
                    $query->whereNotNull('lpo_id');
                } elseif ($request->source === 'direct') {
                    $query->whereNull('lpo_id');
                }
            }

            if ($request->filled('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }

            if ($request->filled('from')) {
                $query->where('received_date', '>=', $request->from);
            }

            if ($request->filled('to')) {
                $query->where('received_date', '<=', $request->to);
            }

            $grns = $query->latest('received_date')->latest('id');

            return DataTables::of($grns)
                ->addIndexColumn()
                ->addColumn('grn_number', fn($d) => '<strong>' . $d->grn_number . '</strong>')
                ->addColumn('source', function ($d) {
                    if ($d->lpo) {
                        return '<span class="badge badge-info"><i class="fas fa-file-alt mr-1"></i>' . $d->lpo->lpo_number . '</span>';
                    }
                    if ($d->purchase) {
                        return '<span class="badge badge-secondary"><i class="fas fa-shopping-cart mr-1"></i>Purchase #' . $d->purchase->id . '</span>';
                    }
                    return '<span class="badge badge-secondary">N/A</span>';
                })
                ->addColumn('supplier_name', fn($d) => $d->supplier->name ?? '—')
                ->addColumn('received_date', fn($d) => \Carbon\Carbon::parse($d->received_date)->format('d M, Y'))
                ->addColumn('items_count', function ($d) {
                    return $d->items->count() . ' item' . ($d->items->count() !== 1 ? 's' : '');
                })
                ->addColumn('total_value', function ($d) {
                    $total = $d->items->sum('line_total');
                    return number_format($total, 2);
                })
                ->addColumn('received_by_name', function ($d) {
                    if (!$d->received_by) return '—';
                    $user = \App\Models\User::find($d->received_by);
                    return $user ? $user->name : '—';
                })
                ->addColumn('action', function ($d) {
                    $viewUrl = route('backend.admin.inventory.goods-received.view', $d->id);
                    $printUrl = route('backend.admin.inventory.goods-received.print', $d->id);
                    return '<div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-info view-grn-btn" data-url="' . $viewUrl . '" title="View GRN">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="' . $printUrl . '" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print GRN">
                            <i class="fas fa-print"></i>
                        </a>
                    </div>';
                })
                ->rawColumns(['grn_number', 'source', 'action'])
                ->toJson();
        }

        return view('backend.inventory.goods-received');
    }

    public function view($id)
    {
        abort_if(!auth()->user()->can('grn_receive'), 403);

        $grn = GoodsReceiptNote::with([
            'lpo',
            'purchase',
            'supplier',
            'items.product',
        ])->findOrFail($id);

        return response()->json($grn);
    }

    public function print($id)
    {
        abort_if(!auth()->user()->can('grn_receive'), 403);

        $grn = GoodsReceiptNote::with([
            'lpo',
            'purchase',
            'supplier',
            'items.product',
        ])->findOrFail($id);

        $receivedByName = '—';
        if ($grn->received_by) {
            $user = \App\Models\User::find($grn->received_by);
            $receivedByName = $user ? $user->name : '—';
        }

        return view('backend.inventory.grn-print', compact('grn', 'receivedByName'));
    }
}
