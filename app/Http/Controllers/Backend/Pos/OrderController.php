<?php

namespace App\Http\Controllers\Backend\Pos;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderTransaction;
use App\Models\PosCart;
use App\Models\Product;
use App\Services\LedgerService;
use App\Services\PosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Exception;

class OrderController extends Controller
{
    protected PosService $posService;
    protected LedgerService $ledgerService;

    public function __construct(PosService $posService, LedgerService $ledgerService)
    {
        $this->posService = $posService;
        $this->ledgerService = $ledgerService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $orders = Order::with('customer')->orderBy('id', 'desc')->get();
            $isAdmin = auth()->user()->hasRole('Admin');

            return DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('saleId', fn($data) => "#" . $data->id)
                ->addColumn('customer', fn($data) => $data->customer->name ?? '-')
                ->addColumn('item', fn($data) => $data->products()->count())
                ->addColumn('sub_total', fn($data) => number_format($data->sub_total, 2, '.', ','))
                ->addColumn('discount', fn($data) => number_format($data->discount, 2, '.', ','))
                ->addColumn('total', fn($data) => number_format($data->total, 2, '.', ','))
                ->addColumn('paid', fn($data) => number_format($data->paid, 2, '.', ','))
                ->addColumn('due', fn($data) => number_format($data->due, 2, '.', ','))
                ->addColumn('payment_method', fn($data) => '<span class="badge bg-info">' . strtoupper($data->payment_method ?? 'CASH') . '</span>')
                ->addColumn('status', function ($data) {
                    if ($data->payment_status === 'voided') {
                        return '<span class="badge bg-dark">Voided</span>';
                    }
                    if ($data->payment_status === 'paid' || $data->status) {
                        return '<span class="badge bg-success">Paid</span>';
                    }
                    if ($data->payment_status === 'pending') {
                        return '<span class="badge bg-warning text-dark">Pending</span>';
                    }
                    return '<span class="badge bg-danger">Failed/Due</span>';
                })
                ->addColumn('action', function ($data) use ($isAdmin) {
                    // Voided orders — no further actions
                    if ($data->payment_status === 'voided') {
                        return '<span class="badge bg-dark p-2"><i class="fas fa-ban mr-1"></i>Voided</span>';
                    }

                    $buttons = '';

                    // Paid orders get a Receipt (POS invoice), pending orders get Invoice
                    if ($data->payment_status === 'paid' || $data->status) {
                        // Paid — show receipt only
                        $buttons .= '<a class="btn btn-success btn-sm m-1" href="'
                            . route('backend.admin.orders.pos-invoice', $data->id)
                            . '" title="Receipt"><i class="fas fa-receipt"></i> Receipt</a>';
                    } else {
                        // Pending / due — show invoice and collection
                        $buttons .= '<a class="btn btn-info btn-sm m-1" href="'
                            . route('backend.admin.orders.invoice', $data->id)
                            . '" title="Invoice"><i class="fas fa-file-invoice"></i> Invoice</a>';

                        $buttons .= '<a class="btn btn-warning btn-sm m-1" href="'
                            . route('backend.admin.due.collection', $data->id)
                            . '" title="Record collection"><i class="fas fa-hand-holding-usd"></i> Collect</a>';
                    }

                    // Void button — admin only, not for already-voided
                    if ($isAdmin) {
                        $buttons .= '<button class="btn btn-danger btn-sm m-1 void-sale-btn"'
                            . ' data-id="' . $data->id . '"'
                            . ' data-sale="#' . $data->id . '"'
                            . ' title="Void this sale">'
                            . '<i class="fas fa-ban"></i> Void</button>';
                    }

                    return $buttons;
                })
                ->rawColumns(['saleId', 'customer', 'item', 'sub_total', 'discount', 'total',
                              'paid', 'due', 'payment_method', 'status', 'action'])
                ->toJson();
        }
        return view('backend.orders.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id|integer',
            'payment_method' => 'nullable|in:cash,stk_push,debtor',
            'tax_mode' => 'nullable|in:inclusive,exclusive',
            'customer_phone' => 'nullable|string',
            'order_discount' => 'nullable|numeric|min:0',
            'paid' => 'nullable|numeric|min:0',
        ]);

        $carts = PosCart::with('product')->where('user_id', auth()->id())->get();
        if ($carts->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 400);
        }

        $cartItems = [];
        $subTotal = 0;

        foreach ($carts as $cart) {
            $itemPrice = $cart->product->discounted_price ?? $cart->product->price;
            $cartItems[] = [
                'id' => $cart->product->id,
                'price' => $itemPrice,
                'qty' => $cart->quantity,
            ];
            $subTotal += ($itemPrice * $cart->quantity);
        }

        $discount = floatval($request->order_discount ?? 0);
        $total = max(0, $subTotal - $discount);
        $paymentMethod = $request->payment_method ?? 'cash';

        try {
            $checkoutResult = $this->posService->processCheckout([
                'customer_id' => $request->customer_id,
                'sub_total' => $subTotal,
                'discount' => $discount,
                'total' => $total,
                'paid' => floatval($request->paid ?? $total),
                'due' => max(0, $total - floatval($request->paid ?? $total)),
                'payment_method' => $paymentMethod,
                'tax_mode' => $request->tax_mode ?? 'inclusive',
                'note' => $request->note ?? null,
            ], $cartItems, $request->customer_phone);

            // Clear cart
            PosCart::where('user_id', auth()->id())->delete();

            return response()->json([
                'message' => $checkoutResult['message'] ?? 'Order completed successfully.',
                'order' => $checkoutResult['order'],
                'status' => $checkoutResult['status'],
                'checkout_request_id' => $checkoutResult['checkout_request_id'] ?? null,
            ], 200);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function invoice($id)
    {
        $order = Order::with(['customer', 'products.product'])->findOrFail($id);
        return view('backend.orders.print-invoice', compact('order'));
    }

    public function collection(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'amount' => 'required|numeric|min:1',
            ]);

            $due = $order->due - $data['amount'];
            $paid = $order->paid + $data['amount'];
            $order->due = round((float)$due, 2);
            $order->paid = round((float)$paid, 2);
            $order->status = round((float)$due, 2) <= 0;
            $order->payment_status = $order->status ? 'paid' : 'pending';
            $order->save();

            $orderTransaction = $order->transactions()->create([
                'amount' => $data['amount'],
                'customer_id' => $order->customer_id,
                'user_id' => auth()->id(),
                'paid_by' => 'cash',
            ]);

            return to_route('backend.admin.collectionInvoice', $orderTransaction->id);
        }
        return view('backend.orders.collection.create', compact('order'));
    }

    public function collectionInvoice($id)
    {
        $transaction = OrderTransaction::findOrFail($id);
        $collection_amount = $transaction->amount;
        $order = $transaction->order;
        return view('backend.orders.collection.invoice', compact('order', 'collection_amount', 'transaction'));
    }

    public function transactions($id)
    {
        $order = Order::with('transactions')->findOrFail($id);
        return view('backend.orders.collection.index', compact('order'));
    }

    public function posInvoice($id)
    {
        $order = Order::with(['customer', 'products.product'])->findOrFail($id);
        $maxWidth = readConfig('receiptMaxwidth') ?? '300px';
        return view('backend.orders.pos-invoice', compact('order', 'maxWidth'));
    }

    /**
     * Void a sale — Admin only.
     * Accepts JSON body: { "reason": "..." }
     * Reverses GL entries, restores stock, nullifies debtor records,
     * deletes collection receipts, marks order as voided.
     */
    public function void(Request $request, int $id)
    {
        // ── Admin-only gate ─────────────────────────────────────────
        if (!auth()->user()->hasRole('Admin')) {
            return response()->json([
                'message' => 'Access denied. Only administrators can void a sale.',
            ], 403);
        }

        // ── Read body — works for both JSON and form-encoded ────────
        // When contentType is application/json, Laravel merges json() automatically
        // but we force-merge to be safe.
        if ($request->isJson()) {
            $request->merge($request->json()->all());
        }

        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $order = Order::with(['products', 'transactions'])->findOrFail($id);

        if ($order->payment_status === 'voided') {
            return response()->json([
                'message' => "Sale #{$id} has already been voided.",
            ], 422);
        }

        $reason   = trim($request->input('reason'));
        $actorName = auth()->user()->name;
        $voidedAt  = now()->toDateTimeString();

        try {
            DB::transaction(function () use ($order, $reason, $actorName, $voidedAt) {

                // 1. Reverse all posted GL journal entries for this order
                $journalEntries = \App\Models\JournalEntry::where('reference_type', 'Order')
                    ->where('reference_id', $order->id)
                    ->where('status', 'posted')
                    ->get();

                foreach ($journalEntries as $entry) {
                    $this->ledgerService->reverseJournalEntry(
                        $entry,
                        "Void of Sale #{$order->id} — {$reason} — By: {$actorName}"
                    );
                }

                // 2. Restore product stock from order line items
                foreach ($order->products as $line) {
                    Product::where('id', $line->product_id)
                           ->increment('quantity', (int) $line->quantity);
                }

                // 3. Delete debtor receipt allocations first (FK constraint),
                //    then the debtor invoice transactions for this order
                $debtorTxIds = DB::table('debtor_transactions')
                    ->where('order_id', $order->id)
                    ->pluck('id');

                if ($debtorTxIds->isNotEmpty()) {
                    DB::table('debtor_receipt_allocations')
                        ->whereIn('invoice_transaction_id', $debtorTxIds)
                        ->delete();

                    DB::table('debtor_transactions')
                        ->where('order_id', $order->id)
                        ->delete();
                }

                // 4. Delete collection receipts (order_transactions)
                $order->transactions()->delete();

                // 5. Mark order as voided
                $order->update([
                    'payment_status' => 'voided',
                    'status'         => 0,
                    'paid'           => 0,
                    'due'            => 0,
                    'note'           => trim(
                        ($order->note ? $order->note . ' | ' : '') .
                        "VOIDED by {$actorName} on {$voidedAt}. Reason: {$reason}"
                    ),
                ]);
            });

            return response()->json([
                'message' => "Sale #{$order->id} voided successfully. Stock restored and all associated records nullified.",
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Void failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
