<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CinetpayService
{
    protected $baseUrl;
    protected $apiKey;
    protected $siteId;

    public function __construct()
    {
        $this->baseUrl = config('services.cinetpay.base_url', 'https://api-checkout.cinetpay.com/v2');
        $this->apiKey  = config('services.cinetpay.api_key', env('CINETPAY_API_KEY'));
        $this->siteId  = config('services.cinetpay.site_id', env('CINETPAY_SITE_ID'));

        Log::info('CinetPay Service initialized:', [
            'base_url' => $this->baseUrl,
            'api_key_present' => $this->apiKey ? 'Yes' : 'No',
            'site_id_present' => $this->siteId ? 'Yes' : 'No'
        ]);
    }

    public function initierPaiement($amount, $transactionId, $description, $returnUrl, $notifyUrl)
    {
        try {
            // Vérifier les credentials
            if (!$this->apiKey || !$this->siteId) {
                throw new Exception('Configuration CinetPay manquante. Vérifiez CINETPAY_API_KEY et CINETPAY_SITE_ID dans votre .env');
            }

            $data = [
                "apikey"         => $this->apiKey,
                "site_id"        => $this->siteId,
                "transaction_id" => $transactionId,
                "amount"         => $amount,
                "currency"       => "XOF",
                "description"    => $description,
                "return_url"     => $returnUrl,
                "notify_url"     => $notifyUrl,
                "channels"       => "ALL",
                "metadata"       => json_encode([
                    'user_id' => auth()->id(),
                    'timestamp' => now()->toISOString()
                ])
            ];

            Log::info('Sending request to CinetPay:', [
                'url' => $this->baseUrl . '/payment',
                'data' => array_merge($data, ['apikey' => '[HIDDEN]']) // Cache l'API key dans les logs
            ]);

            // Requête HTTP avec timeout et retry
            $response = Http::timeout(25)
                ->retry(2, 1000) // 2 essais avec 1 seconde d'attente
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($this->baseUrl . '/payment', $data);

            Log::info('CinetPay response received:', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body()
            ]);

            if ($response->failed()) {
                Log::error('CinetPay HTTP error:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                throw new Exception('Erreur HTTP CinetPay: ' . $response->status() . ' - ' . $response->body());
            }

            $result = $response->json();

            if (!$result) {
                throw new Exception('Réponse CinetPay vide ou invalide');
            }

            Log::info('CinetPay JSON response:', $result);

            // Vérifier le code de retour CinetPay
            if (isset($result['code']) && $result['code'] !== '201') {
                $errorMessage = $result['message'] ?? 'Erreur inconnue CinetPay';
                Log::error('CinetPay business error:', [
                    'code' => $result['code'],
                    'message' => $errorMessage
                ]);
                throw new Exception('Erreur CinetPay [' . $result['code'] . ']: ' . $errorMessage);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('CinetPay service error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    public function verifierPaiement($transactionId)
    {
        try {
            $data = [
                'apikey' => $this->apiKey,
                'site_id' => $this->siteId,
                'transaction_id' => $transactionId
            ];

            Log::info('Verifying payment:', ['transaction_id' => $transactionId]);

            $response = Http::timeout(10)
                ->post($this->baseUrl . '/payment/check', $data);

            $result = $response->json();

            Log::info('Payment verification response:', $result);

            return $result;

        } catch (Exception $e) {
            Log::error('Payment verification error:', [
                'message' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);
            throw $e;
        }
    }

    // Méthode de test pour vérifier la configuration
    public function testConfiguration()
    {
        return [
            'base_url' => $this->baseUrl,
            'api_key_configured' => !empty($this->apiKey),
            'site_id_configured' => !empty($this->siteId),
            'api_key_length' => $this->apiKey ? strlen($this->apiKey) : 0,
            'site_id_value' => $this->siteId
        ];
    }
}
