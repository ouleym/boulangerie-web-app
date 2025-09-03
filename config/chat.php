<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chat System Configuration
    |--------------------------------------------------------------------------
    */

    // Activer/désactiver le système de chat
    'enabled' => env('CHAT_ENABLED', true),

    // Utiliser Mock AI au lieu d'OpenAI (pour développement/test)
    'use_mock_ai' => env('CHAT_USE_MOCK_AI', true),

    // Limites
    'max_messages_per_conversation' => env('CHAT_MAX_MESSAGES_PER_CONVERSATION', 100),
    'max_conversations_per_user' => env('CHAT_MAX_CONVERSATIONS_PER_USER', 50),

    // Configuration OpenAI (quand use_mock_ai = false)
    'openai' => [
        'model' => env('CHAT_AI_MODEL', 'gpt-4o-mini'),
        'max_tokens' => env('CHAT_AI_MAX_TOKENS', 500),
        'temperature' => env('CHAT_AI_TEMPERATURE', 0.7),
    ],

    // Configuration Mock AI
    'mock' => [
        'response_delay' => env('MOCK_AI_RESPONSE_DELAY', 0.5), // secondes
        'typing_delay' => env('MOCK_AI_TYPING_DELAY', 1), // secondes
        'enable_contextual_responses' => true,
        'enable_bakery_expertise' => true,
    ],

    // Messages par défaut
    'default_messages' => [
        'welcome' => 'Bienvenue dans votre assistant boulangerie virtuel !',
        'error' => 'Désolé, une erreur est survenue. Veuillez réessayer.',
        'limit_reached' => 'Vous avez atteint la limite de messages pour cette conversation.',
    ],

    // Contexte de la boulangerie pour l'IA
    'bakery_context' => [
        'business_name' => env('BUSINESS_NAME', 'Ma Boulangerie'),
        'location' => env('BUSINESS_ADDRESS', 'Pikine, Dakar, Sénégal'),
        'specialties' => [
            'Pains traditionnels',
            'Viennoiseries',
            'Pâtisseries',
            'Gâteaux sur mesure'
        ],
        'services' => [
            'Commandes en ligne',
            'Livraisons locales',
            'Conseils recettes',
            'Formations boulangerie'
        ]
    ]
];
