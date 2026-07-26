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
                ->addColumn('status', fn($data) => ($data->payment_status === 'paid' || $data->status)
                    ? '<span class="badge bg-success">Paid</span>'
                    : ($data->payment_status === 'pending' ? '<span class="badge bg-warning">Pending</span>' : '<span class="badge bg-danger">Failed/Due</span>'))
                ->addColumn('action', function ($data) {
                    $buttons = '';
                    $buttons .= '<a class="btn btn-success btn-sm m-1" href="' . route('backend.admin.orders.invoice', $data->id) . '"><i class="fas fa-file-invoice"></i> Invoice</a>';
                    $buttons .= '<a class="btn btn-secondary btn-sm m-1" href="' . route('backend.admin.orders.pos-invoice', $data->id) . '"><i class="fas fa-file-invoice"></i> POS Invoice</a>';
                    if (!$data->status && $data->payment_status !== 'paid') {
                        $buttons .= '<a class="btn btn-warning btn-sm m-1" href="' . route('backend.admin.due.collection', $data->id) . '"><i class="fas fa-receipt"></i> Collection</a>';
                    }
                    $buttons .= '<a class="btn btn-primary btn-sm m-1" href="' . route('backend.admin.orders.transactions', $data->id) . '"><i class="fas fa-exchange-alt"></i> Ledger Tx</a>';
                    $buttons .= '<button class="btn btn-danger btn-sm m-1 void-sale-btn" data-id="' . $data->id . '" data-sale="#' . $data->id . '"><i class="fas fa-ban"></i> Void</button>';
                    return $buttons;
                })
                ->rawColumns(['saleId', 'customer', 'item', 'sub_total', 'discount', 'total', 'paid', 'due', 'payment_method', 'status', 'action'])
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
     * Void a sale: reverse all GL entries, restore stock, delete transactions.
     */
    public function void(Request $request, int $id)
    {
        $order = Order::with(['products.product'])->findOrFail($id);

        if ($order->payment_status === 'voided') {
            return response()->json(['message' => 'This sale has already been voided.'], 422);
        }

        try {
            DB::transaction(function () use ($order) {
                // 1. Reverse all journal entries linked to this order
                $journalEntries = \App\Models\JournalEntry::where('reference_type', 'Order')
                    ->where('reference_id', $order->id)
                    ->where('status', 'posted')
                    ->get();

                foreach ($journalEntries as $entry) {
                    $this->ledgerService->reverseJournalEntry($entry, 'Sale voided by ' . auth()->user()->name);
                }

                // 2. Restore product stock
                foreach ($order->products as $line) {
                    if ($line->product) {
                        $line->product->increment('quantity', $line->quantity);
                    }
                }

                // 3. Remove any debtor transactions linked to this order
                DB::table('debtor_transactions')->where('order_id', $order->id)->delete();

                // 4. Delete order transactions (collection receipts)
                $order->transactions()->delete();

                // 5. Mark the order as voided
                $order->update([
                    'payment_status' => 'voided',
                    'status'         => 0,
                    'note'           => ($order->note ? $order->note . ' | ' : '') . 'VOIDED by ' . auth()->user()->name . ' on ' . now()->toDateTimeString(),
                ]);
            });

            return response()->json(['message' => 'Sale #' . $order->id . ' has been voided and all associated transactions reversed.']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Void failed: ' . $e->getMessage()], 500);
        }
    }
}
