<?php

namespace App\Services;

use App\Models\Chat;
use Illuminate\Support\Facades\Log;

class MockOpenAIService
{
    /**
     * Réponses contextuelles précises pour chaque type de demande
     */
    private array $contextualResponses = [
        // Demandes de gâteaux personnalisés
        'gateau_sur_mesure' => [
            "Parfait ! Nous réalisons des gâteaux personnalisés pour anniversaires. Combien de personnes ? Quel thème souhaitez-vous ? Nos créations vont de 8 000 FCFA (4 parts) à 25 000 FCFA (12 parts).",
            "Excellente idée ! Pour un gâteau d'anniversaire sur mesure, j'ai besoin de quelques détails : nombre d'invités, âge de la personne, préférences (chocolat, vanille, fruits) ? Délai minimum 48h.",
            "Avec plaisir ! Nos gâteaux d'anniversaire personnalisés sont notre spécialité. Quel est votre budget et vos préférences ? Nous avons des créations de 6 000 FCFA à 30 000 FCFA.",
            "Nous créons des gâteaux uniques pour chaque anniversaire ! Dites-moi : combien de parts, saveurs préférées, décoration souhaitée ? Prix à partir de 7 500 FCFA."
        ],

        // Questions sur les pâtisseries disponibles
        'patisseries_liste' => [
            "Nos pâtisseries du jour : éclairs au chocolat, tartes aux mangues, Paris-Brest aux cajou, millefeuilles, religieuses au café Touba, et nos spéciales tartes au bissap. Que vous tente ?",
            "Notre vitrine propose : éclairs, tartes tropicales (mangue, goyave), Saint-Honoré, opéras au chocolat, fraisiers et nos créations locales au baobab. Tout est fait maison !",
            "Aujourd'hui nous avons : millefeuilles, Paris-Brest, tartes aux fruits de saison, éclairs vanille/chocolat, religieuses, et nos spécialités sénégalaises aux arachides et bissap.",
            "Belle sélection aujourd'hui : éclairs au cacao local, tartes à la mangue de Casamance, Saint-Honoré, opéras, et nos exclusivités aux ingrédients du terroir sénégalais."
        ],

        // Demandes sur les événements couverts
        'evenements_services' => [
            "Nous couvrons tous vos événements : mariages (wedding cakes traditionnels), baptêmes, anniversaires, Tabaski, Korité, cérémonies d'entreprise. Service traiteur complet disponible !",
            "Nos services événementiels : mariages avec gâteaux à étages, baptêmes, fêtes d'anniversaire, célébrations religieuses (Tabaski, Korité), réceptions d'entreprise. Tout sur mesure !",
            "Événements que nous couvrons : mariages wolof/peul/serer, baptêmes, anniversaires, fêtes religieuses, inaugurations, réceptions. Devis gratuit sous 24h !",
            "Service complet pour : mariages traditionnels, baptêmes, anniversaires, Tabaski, Korité, Magal, événements d'entreprise. Livraison et installation incluses dans Dakar."
        ],

        // Questions générales sur la boulangerie
        'presentation_generale' => [
            "Notre boulangerie artisanale à Dakar propose pains traditionnels, viennoiseries tropicales, pâtisseries sur mesure et spécialités sénégalaises. Ouvert 6j/7, livraison Dakar. Que cherchez-vous ?",
            "Boulangerie Teranga, artisans depuis 15 ans à Dakar : pains aux céréales locales, viennoiseries adaptées au climat, gâteaux de cérémonie. Comment puis-je vous aider ?",
            "Bienvenue ! Nous sommes spécialisés en boulangerie-pâtisserie sénégalaise : produits locaux (mil, arachide, bissap), techniques adaptées au tropical. Dites-moi vos besoins !"
        ]
    ];

    /**
     * Mots-clés pour la détection contextuelle
     */
    private array $contextKeywords = [
        'gateau_sur_mesure' => [
            'gâteau', 'gateau', 'sur mesure', 'personnalisé', 'anniversaire', 'commande',
            'commander', 'faire faire', 'spécial', 'unique', 'birthday', 'cake'
        ],
        'patisseries_liste' => [
            'pâtisserie', 'patisserie', 'liste', 'quoi', 'avoir', 'proposez',
            'disponible', 'vitrine', 'dessert', 'sucré', 'gâteaux'
        ],
        'evenements_services' => [
            'événement', 'evenement', 'mariage', 'baptême', 'fête', 'cérémonie',
            'celebration', 'couvrir', 'couvrez', 'service', 'traiteur'
        ],
        'prix_tarifs' => [
            'prix', 'tarif', 'coût', 'combien', 'fcfa', 'franc', 'cfa', 'coute'
        ],
        'horaires_infos' => [
            'heure', 'horaire', 'ouvert', 'ferme', 'quand', 'ouvre'
        ],
        'livraison' => [
            'livraison', 'livrer', 'domicile', 'apporter', 'déplacer'
        ]
    ];

    /**
     * Réponses pour les salutations
     */
    private array $greetings = [
        "Asalam aleykum ! Bienvenue dans notre boulangerie. Comment puis-je vous aider aujourd'hui ?",
        "Bonjour ! Votre boulanger dakarois à votre service. Que cherchez-vous ?",
        "Salut ! Bienvenue chez nous. Produits frais du jour, que puis-je vous proposer ?",
        "Nanga def ! Notre boulangerie est à votre disposition. Dites-moi vos envies !"
    ];

    /**
     * Réponses par défaut quand aucun contexte n'est détecté
     */
    private array $defaultResponses = [
        "Je suis votre assistant boulangerie. Pouvez-vous préciser ce que vous cherchez ? Pains, pâtisseries, commandes spéciales ?",
        "Comment puis-je vous aider avec nos produits ? Nous avons pains, viennoiseries, gâteaux sur mesure et spécialités locales.",
        "Bienvenue ! Que souhaitez-vous savoir sur notre boulangerie ? Produits, prix, commandes, horaires ?",
        "Notre équipe est là pour vous conseiller. De quoi avez-vous besoin ? Pain quotidien, pâtisserie, événement spécial ?"
    ];

    /**
     * Détecte le contexte principal du message
     */
    private function detectContext(string $message): ?string
    {
        $message = strtolower(trim($message));

        // Vérifier chaque catégorie de mots-clés
        foreach ($this->contextKeywords as $context => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return $context;
                }
            }
        }

        // Détecter les salutations
        if ($this->isGreeting($message)) {
            return 'greeting';
        }

        return null;
    }

    /**
     * Vérifie si c'est une salutation
     */
    private function isGreeting(string $message): bool
    {
        $greetingWords = [
            'bonjour', 'bonsoir', 'salut', 'hello', 'asalam', 'aleykum',
            'nanga', 'def', 'hey', 'coucou'
        ];

        foreach ($greetingWords as $greeting) {
            if (strpos($message, $greeting) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Génère une réponse contextuelle appropriée
     */
    public function generateResponse(Chat $chat, string $userMessage): string
    {
        $context = $this->detectContext($userMessage);
        $message = strtolower(trim($userMessage));

        // Gestion des salutations
        if ($context === 'greeting') {
            return $this->greetings[array_rand($this->greetings)];
        }

        // Réponses spécifiques pour prix
        if ($context === 'prix_tarifs' || strpos($message, 'combien') !== false) {
            if (strpos($message, 'gâteau') !== false || strpos($message, 'gateau') !== false) {
                return "Nos gâteaux sur mesure : 4 parts 8 000 FCFA, 6 parts 12 000 FCFA, 8 parts 18 000 FCFA, 12 parts 25 000 FCFA. Créations spéciales à partir de 15 000 FCFA.";
            }
            return "Nos tarifs : baguette 300 FCFA, croissant 350 FCFA, pain complet 800 FCFA, pâtisseries 500-2000 FCFA. Gâteaux sur mesure dès 8 000 FCFA.";
        }

        // Réponses pour horaires
        if ($context === 'horaires_infos') {
            return "Nous sommes ouverts du lundi au samedi 5h-20h, dimanche 6h-18h. Productions chaudes : 6h, 11h, 16h. Commandes spéciales sur rendez-vous.";
        }

        // Réponses pour livraison
        if ($context === 'livraison') {
            return "Livraison gratuite à Dakar pour commandes +15 000 FCFA. Zones : Plateau, Médina, Pikine, Parcelles, Almadies. Commande par WhatsApp, livraison 2h.";
        }

        // Réponses contextuelles principales
        if ($context && isset($this->contextualResponses[$context])) {
            return $this->contextualResponses[$context][array_rand($this->contextualResponses[$context])];
        }

        // Réponse par défaut si aucun contexte détecté
        return $this->defaultResponses[array_rand($this->defaultResponses)];
    }

    /**
     * Génère un titre intelligent basé sur le contexte
     */
    public function generateChatTitle(string $userMessage): string
    {
        $context = $this->detectContext($userMessage);
        $message = strtolower(trim($userMessage));

        // Titres basés sur le contexte détecté
        $contextTitles = [
            'gateau_sur_mesure' => 'Gâteau Personnalisé',
            'patisseries_liste' => 'Nos Pâtisseries',
            'evenements_services' => 'Services Événements',
            'prix_tarifs' => 'Tarifs et Prix',
            'horaires_infos' => 'Horaires d\'ouverture',
            'livraison' => 'Service Livraison',
            'greeting' => 'Accueil Client'
        ];

        if ($context && isset($contextTitles[$context])) {
            return $contextTitles[$context];
        }

        // Analyse des mots-clés pour titre spécifique
        if (strpos($message, 'anniversaire') !== false) {
            return 'Gâteau Anniversaire';
        }
        if (strpos($message, 'mariage') !== false) {
            return 'Gâteau de Mariage';
        }
        if (strpos($message, 'tabaski') !== false || strpos($message, 'korité') !== false) {
            return 'Pâtisseries Religieuses';
        }

        return 'Demande Client';
    }

    /**
     * Simuler l'appel OpenAI avec réponses contextuelles
     */
    public function generateChatCompletion(array $messages): array
    {
        try {
            $lastMessage = end($messages);
            $userMessage = $lastMessage['content'] ?? '';

            // Générer une réponse vraiment contextuelle
            $response = $this->generateResponse(new Chat(), $userMessage);

            Log::info('MockOpenAI Contextual Response', [
                'user_message' => $userMessage,
                'detected_context' => $this->detectContext($userMessage),
                'response' => $response
            ]);

            return [
                'choices' => [
                    [
                        'message' => [
                            'content' => $response,
                            'role' => 'assistant'
                        ],
                        'finish_reason' => 'stop'
                    ]
                ],
                'usage' => [
                    'prompt_tokens' => str_word_count($userMessage),
                    'completion_tokens' => str_word_count($response),
                    'total_tokens' => str_word_count($userMessage . $response)
                ],
                'model' => 'boulangerie-senegal-contextual-v2.0',
                'created' => time()
            ];

        } catch (\Exception $e) {
            Log::error('MockOpenAI Error', [
                'error' => $e->getMessage(),
                'messages' => $messages
            ]);

            return [
                'choices' => [
                    [
                        'message' => [
                            'content' => "Désolé, une erreur s'est produite. Notre équipe technique règle cela rapidement. Pouvez-vous reformuler votre demande ?",
                            'role' => 'assistant'
                        ],
                        'finish_reason' => 'stop'
                    ]
                ],
                'usage' => ['prompt_tokens' => 0, 'completion_tokens' => 0, 'total_tokens' => 0],
                'model' => 'boulangerie-senegal-contextual-v2.0',
                'created' => time(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir des suggestions contextuelles
     */
    public function getSuggestions(): array
    {
        return [
            "Je voudrais commander un gâteau d'anniversaire",
            "Quelles pâtisseries avez-vous aujourd'hui ?",
            "Quels événements couvrez-vous ?",
            "Quels sont vos tarifs en FCFA ?",
            "Quels sont vos horaires d'ouverture ?",
            "Livrez-vous à domicile dans Dakar ?",
            "Avez-vous des spécialités sénégalaises ?",
            "Comment commander pour un mariage ?",
            "Produits pour Tabaski disponibles ?",
            "Pain quotidien, quelles variétés ?"
        ];
    }
}
