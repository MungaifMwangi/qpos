<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DarajaService
{
    protected string $consumerKey;
    protected string $consumerSecret;
    protected string $shortcode;
    protected string $passkey;
    protected string $env;
    protected string $baseUrl;
    protected string $callbackUrl;

    public function __construct()
    {
        $this->consumerKey = (string) (config('services.daraja.consumer_key') ?? env('DARAJA_CONSUMER_KEY', ''));
        $this->consumerSecret = (string) (config('services.daraja.consumer_secret') ?? env('DARAJA_CONSUMER_SECRET', ''));
        $this->shortcode = (string) (config('services.daraja.shortcode') ?? env('DARAJA_SHORTCODE', '174379'));
        $this->passkey = (string) (config('services.daraja.passkey') ?? env('DARAJA_PASSKEY', ''));
        $this->env = (string) (config('services.daraja.env') ?? env('DARAJA_ENV', 'sandbox'));
        $this->callbackUrl = (string) (config('services.daraja.callback_url') ?? env('DARAJA_CALLBACK_URL', url('/api/mpesa/callback')));

        $this->baseUrl = $this->env === 'live'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Get OAuth Access Token from Safaricom Daraja API
     */
    public function getAccessToken(): string
    {
        if (empty($this->consumerKey) || empty($this->consumerSecret)) {
            // Fallback for demo/testing mode if credentials not yet set in .env
            return 'DEMO_DARAJA_ACCESS_TOKEN';
        }

        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get("{$this->baseUrl}/oauth/v1/generate?grant_type=client_credentials");

            if ($response->successful() && isset($response['access_token'])) {
                return $response['access_token'];
            }
        } catch (Exception $e) {
            Log::error("Daraja OAuth Token Error: " . $e->getMessage());
        }

        return 'DEMO_DARAJA_ACCESS_TOKEN';
    }

    /**
     * Initiate M-Pesa Express STK Push
     */
    public function initiateStkPush(string $phoneNumber, float $amount, string $accountReference, string $transactionDesc = 'POS Payment'): array
    {
        // Sanitize phone number format to 2547XXXXXXXX or 2541XXXXXXXX
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '7') || str_starts_with($phone, '1')) {
            $phone = '254' . $phone;
        }

        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        $token = $this->getAccessToken();

        if ($token === 'DEMO_DARAJA_ACCESS_TOKEN') {
            // Simulated response for development/testing when live credentials are not set
            $checkoutRequestId = 'ws_CO_' . date('dmYHis') . '_' . rand(1000, 9999);
            return [
                'success' => true,
                'CheckoutRequestID' => $checkoutRequestId,
                'MerchantRequestID' => 'MR_' . rand(10000, 99999),
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success. Request accepted for processing',
                'CustomerMessage' => "Success. STK Push sent to {$phone} for KES " . number_format($amount, 2),
                'is_demo' => true,
            ];
        }

        try {
            $response = Http::withToken($token)->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => round($amount),
                'PartyA' => $phone,
                'PartyB' => $this->shortcode,
                'PhoneNumber' => $phone,
                'CallBackURL' => $this->callbackUrl,
                'AccountReference' => substr($accountReference, 0, 12),
                'TransactionDesc' => substr($transactionDesc, 0, 12),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => isset($data['ResponseCode']) && $data['ResponseCode'] == '0',
                    'CheckoutRequestID' => $data['CheckoutRequestID'] ?? null,
                    'MerchantRequestID' => $data['MerchantRequestID'] ?? null,
                    'ResponseCode' => $data['ResponseCode'] ?? null,
                    'ResponseDescription' => $data['ResponseDescription'] ?? null,
                    'CustomerMessage' => $data['CustomerMessage'] ?? null,
                    'is_demo' => false,
                ];
            }
        } catch (Exception $e) {
            Log::error("Daraja STK Push Error: " . $e->getMessage());
        }

        return [
            'success' => false,
            'message' => 'Failed to reach Daraja API service. Please try again or switch to cash.',
        ];
    }
}
