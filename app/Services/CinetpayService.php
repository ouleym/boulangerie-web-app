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
        // ✅ URL officielle selon la documentation CinetPay
        $this->baseUrl = 'https://api-checkout.cinetpay.com/v2';
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

            // ✅ Données selon la documentation officielle CinetPay
            $data = [
                "apikey"         => $this->apiKey,
                "site_id"        => (int) $this->siteId, // Conversion en entier
                "transaction_id" => $transactionId,
                "amount"         => (int) $amount, // Conversion en entier
                "currency"       => "XOF",
                "description"    => $description,
                "return_url"     => $returnUrl,
                "notify_url"     => $notifyUrl,
                "channels"       => "ALL",

                // ✅ Informations client obligatoires selon la doc
                "customer_id"           => (string) auth()->id(),
                "customer_name"         => auth()->user()->name ?? "Client",
                "customer_surname"      => auth()->user()->prenom ?? "CinetPay",
                "customer_email"        => auth()->user()->email ?? "client@example.com",
                "customer_phone_number" => auth()->user()->telephone ?? "+221000000000",
                "customer_address"      => "Adresse par défaut",
                "customer_city"         => "Dakar",
                "customer_country"      => "SN", // Code ISO Sénégal
                "customer_state"        => "SN", // Code ISO
                "customer_zip_code"     => "00000",

                "metadata" => json_encode([
                    'user_id' => auth()->id(),
                    'timestamp' => now()->toISOString()
                ]),

                // ✅ Langue française
                "lang" => "FR"
            ];

            Log::info('Sending request to CinetPay:', [
                'url' => $this->baseUrl . '/payment',
                'data' => array_merge($data, ['apikey' => '[HIDDEN]'])
            ]);

            // ✅ Requête HTTP avec headers corrects selon la doc
            $response = Http::timeout(30)
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

            // ✅ Vérification selon la documentation : code "201" = succès
            if (isset($result['code']) && $result['code'] !== '201') {
                $errorMessage = $result['message'] ?? 'Erreur inconnue CinetPay';
                $description = $result['description'] ?? '';

                Log::error('CinetPay business error:', [
                    'code' => $result['code'],
                    'message' => $errorMessage,
                    'description' => $description
                ]);

                throw new Exception("Erreur CinetPay [{$result['code']}]: {$errorMessage}. {$description}");
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
                'site_id' => (int) $this->siteId,
                'transaction_id' => $transactionId
            ];

            Log::info('Verifying payment:', ['transaction_id' => $transactionId]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
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

    // ✅ Méthode pour tester avec de vraies données
    public function testPaiement()
    {
        try {
            $testData = [
                "apikey" => $this->apiKey,
                "site_id" => (int) $this->siteId,
                "transaction_id" => "TEST-" . time(),
                "amount" => 1000,
                "currency" => "XOF",
                "description" => "Test de paiement CinetPay",
                "return_url" => "https://example.com/success",
                "notify_url" => "https://example.com/notify",
                "channels" => "ALL",
                "customer_id" => "test001",
                "customer_name" => "Test",
                "customer_surname" => "User",
                "customer_email" => "test@example.com",
                "customer_phone_number" => "+221000000000",
                "customer_address" => "Adresse test",
                "customer_city" => "Dakar",
                "customer_country" => "SN",
                "customer_state" => "SN",
                "customer_zip_code" => "00000",
                "lang" => "FR"
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($this->baseUrl . '/payment', $testData);

            return [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
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
