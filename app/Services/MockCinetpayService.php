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
        $this->baseUrl = config('services.cinetpay.base_url', 'https://api-checkout.cinetpay.com/v2');
        $this->apiKey = config('services.cinetpay.api_key', env('CINETPAY_API_KEY'));
        $this->siteId = config('services.cinetpay.site_id', env('CINETPAY_SITE_ID'));

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
            'description' => 'Transaction created successfully',
            'data' => [
                'transaction_id' => $transactionId,
                'payment_method' => 'ALL',
                'payment_url' => "https://checkout.cinetpay.com/payment/{$transactionId}",
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
            'api_key_configured' => !empty($this->apiKey),
            'site_id_configured' => !empty($this->siteId),
            'api_key_length' => $this->apiKey ? strlen($this->apiKey) : 0,
            'site_id_value' => $this->siteId,
            'status' => 'MOCK SERVICE READY'
        ];
    }
}
