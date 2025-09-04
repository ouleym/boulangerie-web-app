<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MockCinetpayService
{
    protected $baseUrl;
    protected $apiKey;
    protected $siteId;

    public function __construct()
    {
        $this->baseUrl = 'https://api-checkout.cinetpay.com/v2';
        $this->apiKey  = 'mock_api_key';
        $this->siteId  = 'mock_site_id';

        Log::info('Mock CinetPay Service initialized');
    }

    public function initierPaiement($amount, $transactionId, $description, $returnUrl, $notifyUrl)
    {
        Log::info('=== MOCK CINETPAY PAYMENT INIT ===', [
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'description' => $description,
            'return_url' => $returnUrl,
            'notify_url' => $notifyUrl
        ]);

        // Simuler un petit délai comme une vraie API
        sleep(1);

        // Retourner une réponse mock similaire à CinetPay
        $mockResponse = [
            'code' => '201',
            'message' => 'CREATED',
            'description' => 'Transaction created successfully (MOCK)',
            'data' => [
                'transaction_id' => $transactionId,
                'payment_method' => 'ALL',
                'payment_url' => "https://mock-checkout.cinetpay.com/payment/{$transactionId}",
                'amount' => $amount,
                'currency' => 'XOF',
                'description' => $description,
                'return_url' => $returnUrl,
                'notify_url' => $notifyUrl,
                'created_at' => now()->toISOString()
            ]
        ];

        Log::info('Mock CinetPay response generated:', $mockResponse);

        return $mockResponse;
    }

    public function verifierPaiement($transactionId)
    {
        Log::info('Mock payment verification:', ['transaction_id' => $transactionId]);

        return [
            'code' => '00',
            'message' => 'SUCCESSFUL',
            'data' => [
                'transaction_id' => $transactionId,
                'status' => 'ACCEPTED',
                'amount' => 1000,
                'currency' => 'XOF'
            ]
        ];
    }

    public function testConfiguration()
    {
        return [
            'service_type' => 'MOCK',
            'base_url' => $this->baseUrl,
            'api_key_configured' => true,
            'site_id_configured' => true,
            'api_key_length' => strlen($this->apiKey),
            'site_id_value' => $this->siteId,
            'status' => 'MOCK SERVICE READY'
        ];
    }
}
