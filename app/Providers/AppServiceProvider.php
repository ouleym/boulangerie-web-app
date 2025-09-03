<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OpenAIService;
use App\Services\MockOpenAIService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding pour le service de Chat AI
        $this->app->bind('ChatAIService', function ($app) {
            // Si CHAT_USE_MOCK_AI est true, utilise MockOpenAI, sinon OpenAI
            if (config('chat.use_mock_ai', true)) {
                return new MockOpenAIService();
            } else {
                return new OpenAIService();
            }
        });

        // Autres bindings si nécessaire
        $this->registerChatServices();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configuration de la timezone
        if (config('app.timezone')) {
            date_default_timezone_set(config('app.timezone'));
        }

        // Configuration des logs pour le chat
        if (config('chat.enabled', true)) {
            $this->bootChatConfiguration();
        }
    }

    /**
     * Enregistrer les services liés au chat
     */
    private function registerChatServices(): void
    {
        // Binding conditionnel pour OpenAI
        $this->app->when(OpenAIService::class)
            ->needs('$apiKey')
            ->give(function () {
                return config('openai.api_key');
            });

        // Singleton pour MockOpenAI (pas besoin de recréer à chaque fois)
        $this->app->singleton(MockOpenAIService::class, function ($app) {
            return new MockOpenAIService();
        });
    }

    /**
     * Configuration du système de chat au démarrage
     */
    private function bootChatConfiguration(): void
    {
        // Log le mode IA utilisé
        if (config('app.debug')) {
            $aiMode = config('chat.use_mock_ai') ? 'Mock AI' : 'OpenAI';
            \Log::info("Chat AI Mode: {$aiMode}");
        }

        // Configuration des timeouts pour les réponses Mock
        if (config('chat.use_mock_ai')) {
            ini_set('max_execution_time', 30); // 30 secondes max
        }
    }
}
