<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private $aiService;

    public function __construct()
    {
        $this->middleware('auth:api');
        // Injection automatique du service selon la config
        $this->aiService = app('ChatAIService');
    }

    /**
     * Liste des chats de l'utilisateur
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            $chats = Chat::where('user_id', $user->id)
                ->with(['messages' => function($query) {
                    $query->latest()->limit(1);
                }])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function($chat) {
                    return [
                        'id' => $chat->id,
                        'user_id' => $chat->user_id,
                        'title' => $chat->title,
                        'created_at' => $chat->created_at,
                        'updated_at' => $chat->updated_at,
                        'latest_message' => $chat->messages->first(),
                        'messages_count' => $chat->messages()->count()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $chats,
                'ai_mode' => config('chat.use_mock_ai') ? 'Mock' : 'OpenAI'
            ]);

        } catch (\Exception $e) {
            Log::error('Chat index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des conversations'
            ], 500);
        }
    }

    /**
     * Créer un nouveau chat avec le premier message
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Créer le chat avec un titre généré par l'IA
            $chatTitle = $this->aiService->generateChatTitle($request->message);

            $chat = Chat::create([
                'user_id' => Auth::id(),
                'title' => $chatTitle
            ]);

            // Message utilisateur
            $userMessage = Message::create([
                'chat_id' => $chat->id,
                'role' => 'user',
                'content' => $request->message
            ]);

            // Réponse de l'IA
            $aiResponse = $this->aiService->generateResponse($chat, $request->message);

            $assistantMessage = Message::create([
                'chat_id' => $chat->id,
                'role' => 'assistant',
                'content' => $aiResponse
            ]);

            $chat->touch(); // Mettre à jour updated_at
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'chat' => $chat->fresh()->load('messages'),
                    'user_message' => $userMessage,
                    'assistant_message' => $assistantMessage
                ],
                'ai_mode' => config('chat.use_mock_ai') ? 'Mock' : 'OpenAI'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Chat creation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du chat',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Voir un chat + messages
     */
    public function show(Chat $chat): JsonResponse
    {
        try {
            if ($chat->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $chatData = $chat->load('messages');

            return response()->json([
                'success' => true,
                'data' => $chatData,
                'ai_mode' => config('chat.use_mock_ai') ? 'Mock' : 'OpenAI'
            ]);

        } catch (\Exception $e) {
            Log::error('Chat show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de la conversation'
            ], 500);
        }
    }

    /**
     * Envoyer un message dans un chat existant
     */
    public function sendMessage(Request $request, Chat $chat): JsonResponse
    {
        if ($chat->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Vérifier le nombre de messages dans le chat
            $messageCount = $chat->messages()->count();
            $maxMessages = config('chat.max_messages_per_conversation', 100);

            if ($messageCount >= $maxMessages) {
                return response()->json([
                    'success' => false,
                    'message' => "Limite de {$maxMessages} messages atteinte pour cette conversation"
                ], 429);
            }

            // Message utilisateur
            $userMessage = Message::create([
                'chat_id' => $chat->id,
                'role' => 'user',
                'content' => $request->message
            ]);

            // Réponse de l'IA
            $aiResponse = $this->aiService->generateResponse($chat, $request->message);

            $assistantMessage = Message::create([
                'chat_id' => $chat->id,
                'role' => 'assistant',
                'content' => $aiResponse
            ]);

            $chat->touch(); // Mettre à jour la date de dernière activité
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'user_message' => $userMessage,
                    'assistant_message' => $assistantMessage,
                    'chat_updated_at' => $chat->fresh()->updated_at
                ],
                'ai_mode' => config('chat.use_mock_ai') ? 'Mock' : 'OpenAI'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Message sending error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du message',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Supprimer un chat
     */
    public function destroy(Chat $chat): JsonResponse
    {
        try {
            if ($chat->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $chatId = $chat->id;
            $chat->delete(); // Les messages seront supprimés automatiquement via cascade

            return response()->json([
                'success' => true,
                'message' => 'Chat supprimé avec succès',
                'deleted_chat_id' => $chatId
            ]);

        } catch (\Exception $e) {
            Log::error('Chat deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    /**
     * Statistiques des chats de l'utilisateur
     */
    public function stats(): JsonResponse
    {
        try {
            $user = Auth::user();

            $stats = [
                'total_chats' => Chat::where('user_id', $user->id)->count(),
                'total_messages' => Message::whereHas('chat', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->count(),
                'recent_chats' => Chat::where('user_id', $user->id)
                    ->where('updated_at', '>=', now()->subDays(7))
                    ->count(),
                'ai_mode' => config('chat.use_mock_ai') ? 'Mock' : 'OpenAI'
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Chat stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des statistiques'
            ], 500);
        }
    }
}
