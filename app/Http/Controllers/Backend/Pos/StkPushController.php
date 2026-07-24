<?php

namespace App\Http\Controllers\Backend\Pos;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DarajaService;
use App\Services\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StkPushController extends Controller
{
    protected DarajaService $darajaService;
    protected PosService $posService;

    public function __construct(DarajaService $darajaService, PosService $posService)
    {
        $this->darajaService = $darajaService;
        $this->posService = $posService;
    }

    /**
     * Initiate M-Pesa STK Push
     */
    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'phone' => 'required|string',
        ]);

        $order = Order::findOrFail($request->order_id);
        $result = $this->darajaService->initiateStkPush(
            $request->phone,
            $order->total,
            "POS-{$order->id}",
            "Order #{$order->id}"
        );

        if ($result['success'] ?? false) {
            $order->update([
                'daraja_checkout_request_id' => $result['CheckoutRequestID'],
                'payment_method' => 'stk_push',
                'payment_status' => 'pending',
            ]);

            // Auto-confirm in demo mode
            if ($result['is_demo'] ?? false) {
                $this->posService->confirmStkPushPayment($result['CheckoutRequestID'], true);
            }

            return response()->json([
                'success' => true,
                'message' => $result['CustomerMessage'] ?? 'STK Push initiated successfully.',
                'checkout_request_id' => $result['CheckoutRequestID'],
                'is_demo' => $result['is_demo'] ?? false,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to send STK push.',
        ], 400);
    }

    /**
     * Webhook Callback endpoint for Daraja API
     */
    public function callback(Request $request): JsonResponse
    {
        Log::info("Daraja Callback Received: " . json_encode($request->all()));

        $callback = $request->input('Body.stkCallback', []);
        $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;
        $resultCode = $callback['ResultCode'] ?? null;

        if (!$checkoutRequestId) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Missing CheckoutRequestID'], 400);
        }

        $isSuccess = ($resultCode === 0);
        $mpesaReceipt = null;

        if ($isSuccess && isset($callback['CallbackMetadata']['Item'])) {
            foreach ($callback['CallbackMetadata']['Item'] as $item) {
                if ($item['Name'] === 'MpesaReceiptNumber') {
                    $mpesaReceipt = $item['Value'];
                    break;
                }
            }
        }

        $this->posService->confirmStkPushPayment($checkoutRequestId, $isSuccess, $mpesaReceipt);

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Callback Accepted',
        ]);
    }

    /**
     * Poll order status
     */
    public function status(int $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);

        return response()->json([
            'order_id' => $order->id,
            'payment_status' => $order->payment_status,
            'paid' => $order->payment_status === 'paid',
        ]);
    }
}
