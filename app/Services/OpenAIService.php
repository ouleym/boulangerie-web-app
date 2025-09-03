<?php

namespace App\Services;

use App\Models\Chat;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;  // ✅ Import correct

class OpenAIService
{
    /**
     * Générer un titre de conversation basé sur le premier message
     */
    public function generateChatTitle(string $userMessage): string
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Tu es un assistant qui résume un message utilisateur en un court titre de conversation (max 5 mots).'
                    ],
                    [
                        'role' => 'user',
                        'content' => $userMessage
                    ]
                ],
                'max_tokens' => 20
            ]);

            return trim($response->choices[0]->message->content ?? 'Nouvelle conversation');
        } catch (\Exception $e) {
            Log::error('Erreur OpenAI generateChatTitle: ' . $e->getMessage());
            return 'Nouvelle conversation';
        }
    }

    /**
     * Générer une réponse d'IA dans un chat
     */
    public function generateResponse(Chat $chat, string $userMessage): string
    {
        try {
            // Récupérer les anciens messages pour le contexte
            $history = $chat->messages()
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($msg) {
                    return [
                        'role' => $msg->role,
                        'content' => $msg->content
                    ];
                })
                ->toArray();

            // Ajouter le nouveau message
            $history[] = [
                'role' => 'user',
                'content' => $userMessage
            ];

            // Appel API OpenAI
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => $history,
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);

            return trim($response->choices[0]->message->content ?? "Désolé, je n'ai pas pu générer de réponse.");
        } catch (\Exception $e) {
            Log::error('Erreur OpenAI generateResponse: ' . $e->getMessage());
            return "⚠️ Une erreur est survenue lors de la génération de la réponse.";
        }
    }
}
