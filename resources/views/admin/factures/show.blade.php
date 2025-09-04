@extends(Auth::user()->role === 'client' ? 'layouts.app' : 'layouts.admin')

@section('title', 'Facture #' . $commande->id)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- En-tête avec actions -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">📄 Facture #{{ str_pad($commande->id, 6, '0', STR_PAD_LEFT) }}</h1>
                        <p class="text-muted">Commande du {{ $commande->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route(Auth::user()->role === 'client' ? 'client.facture.download' : 'admin.facture.download', $commande->id) }}"
                           class="btn btn-primary">
                            <i class="fas fa-download"></i> Télécharger PDF
                        </a>
                        @if(Auth::user()->role !== 'client')
                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#emailModal">
                                <i class="fas fa-envelope"></i> Envoyer par email
                            </button>
                        @endif
                        <a href="{{ Auth::user()->role === 'client' ? route('client.commandes.show', $commande->id) : route('admin.commandes.show', $commande->id) }}"
                           class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Retour à la commande
                        </a>
                    </div>
                </div>

                <!-- Facture -->
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <!-- En-tête de la facture -->
                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="company-info">
                                    <h2 class="text-brown mb-3">🥖 Ma Boulangerie</h2>
                                    <p class="mb-1">123 Rue des Boulangers</p>
                                    <p class="mb-1">75001 Paris, France</p>
                                    <p class="mb-1">Tél: 01 23 45 67 89</p>
                                    <p class="mb-1">Email: contact@boulangerie.com</p>
                                    <p class="mb-0">SIRET: 123 456 789 00012</p>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <h1 class="display-4 text-brown mb-3">FACTURE</h1>
                                <p class="h5 mb-2"><strong>N° {{ str_pad($commande->id, 6, '0', STR_PAD_LEFT) }}</strong></p>
                                <p class="text-muted">Date: {{ $commande->created_at->format('d/m/Y') }}</p>

                                <!-- Badge statut -->
                                <span class="badge bg-{{ $commande->statut === 'livree' ? 'success' : ($commande->statut === 'annulee' ? 'danger' : 'warning') }} fs-6">
                                {{ ucfirst(str_replace('_', ' ', $commande->statut)) }}
                            </span>
                            </div>
                        </div>

                        <!-- Informations client et commande -->
                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="billing-info">
                                    <h5 class="text-brown border-bottom border-brown pb-2 mb-3">FACTURER À</h5>
                                    <p class="mb-1"><strong>{{ $user->prenom }} {{ $user->nom }}</strong></p>
                                    <p class="mb-1">{{ $user->email }}</p>
                                    @if($user->telephone)
                                        <p class="mb-1">{{ $user->telephone }}</p>
                                    @endif
                                    @if($user->adresse)
                                        <p class="mb-1">{{ $user->adresse }}</p>
                                    @endif
                                    @if($user->ville)
                                        <p class="mb-0">{{ $user->ville }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="order-details">
                                    <h5 class="text-brown border-bottom border-brown pb-2 mb-3">DÉTAILS FACTURE</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Commande N°:</strong></td>
                                            <td class="text-end">{{ $commande->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date commande:</strong></td>
                                            <td class="text-end">{{ $commande->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Mode de paiement:</strong></td>
                                            <td class="text-end">{{ $commande->mode_paiement === 'en_ligne' ? 'En ligne' : 'À la livraison' }}</td>
                                        </tr>
                                        @if($commande->livraison && $commande->livraison->heure_depart)
                                            <tr>
                                                <td><strong>Heure de départ:</strong></td>
                                                <td class="text-end">{{ $commande->livraison->heure_depart->format('H:i') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Détails des produits -->
                        <div class="mb-5">
                            <h5 class="text-brown border-bottom border-brown pb-2 mb-3">DÉTAILS DE LA COMMANDE</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                    <tr class="text-brown">
                                        <th>Produit</th>
                                        <th class="text-center" width="100">Quantité</th>
                                        <th class="text-end" width="120">Prix unitaire</th>
                                        <th class="text-end" width="120">Total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($commande->detailsCommandes as $detail)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $detail->produit->nom }}</strong>
                                                    @if($detail->produit->description)
                                                        <br><small class="text-muted">{{ Str::limit($detail->produit->description, 80) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $detail->quantite }}</td>
                                            <td class="text-end">{{ number_format($detail->prix_unitaire, 2, ',', ' ') }} €</td>
                                            <td class="text-end"><strong>{{ number_format($detail->prix_total, 2, ',', ' ') }} €</strong></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Totaux -->
                        <div class="row">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <table class="table table-borderless mb-0">
                                            <tr>
                                                <td><strong>Sous-total:</strong></td>
                                                <td class="text-end">{{ number_format($commande->montant_produit ?? $commande->montant_total, 2, ',', ' ') }} €</td>
                                            </tr>
                                            @if(isset($commande->frais_livraison) && $commande->frais_livraison > 0)
                                                <tr>
                                                    <td><strong>Frais de livraison:</strong></td>
                                                    <td class="text-end">{{ number_format($commande->frais_livraison, 2, ',', ' ') }} €</td>
                                                </tr>
                                            @endif
                                            @if(isset($commande->reduction) && $commande->reduction > 0)
                                                <tr>
                                                    <td><strong>Réduction:</strong></td>
                                                    <td class="text-end text-success">-{{ number_format($commande->reduction, 2, ',', ' ') }} €</td>
                                                </tr>
                                            @endif
                                            <tr class="border-top">
                                                <td><strong class="h5 text-brown">TOTAL TTC:</strong></td>
                                                <td class="text-end"><strong class="h5 text-brown">{{ number_format($commande->montant_total, 2, ',', ' ') }} €</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations de paiement -->
                        <div class="row mt-5">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="text-brown mb-3">💰 INFORMATIONS DE PAIEMENT</h6>
                                        @if($commande->mode_paiement === 'en_ligne')
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <div>
                                                    <p class="mb-1"><strong>Paiement effectué en ligne</strong></p>
                                                    <small class="text-muted">Date: {{ $commande->created_at->format('d/m/Y à H:i') }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-money-bill-wave text-warning me-2"></i>
                                                <div>
                                                    <p class="mb-1"><strong>Paiement à la livraison</strong></p>
                                                    <p class="mb-0">Montant: <strong>{{ number_format($commande->montant_total, 2, ',', ' ') }} €</strong></p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($commande->livraison)
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="text-brown mb-3">🚚 INFORMATIONS DE LIVRAISON</h6>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <p class="mb-1"><strong>Statut:</strong></p>
                                                    <span class="badge bg-{{ $commande->livraison->statut === 'livree' ? 'success' : 'warning' }}">
                                                {{ ucfirst(str_replace('_', ' ', $commande->livraison->statut)) }}
                                            </span>
                                                    @if($commande->livraison->heure_depart)
                                                        <p class="mt-2 mb-1"><strong>Heure de départ:</strong></p>
                                                        <p class="mb-0">{{ $commande->livraison->heure_depart->format('H:i') }}</p>
                                                    @endif
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="mb-1"><strong>Adresse:</strong></p>
                                                    <p class="mb-0 small">
                                                        {{ $user->adresse }}<br>
                                                        {{ $user->ville }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Notes de commande -->
                        @if($commande->notes)
                            <div class="mt-4">
                                <div class="card border-warning bg-warning bg-opacity-10">
                                    <div class="card-body">
                                        <h6 class="text-brown mb-2">📝 Notes de commande</h6>
                                        <p class="mb-0 small">{{ $commande->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Pied de page -->
                        <div class="mt-5 pt-4 border-top text-center">
                            <p class="text-brown mb-2"><strong>Ma Boulangerie</strong> - Artisan Boulanger depuis 1985</p>
                            <p class="text-muted mb-1">Merci pour votre confiance ! Pour toute question, contactez-nous au 01 23 45 67 89</p>
                            <p class="text-muted small">Cette facture a été générée le {{ now()->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour envoyer par email (admin seulement) -->
    @if(Auth::user()->role !== 'client')
        <div class="modal fade" id="emailModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Envoyer la facture par email</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="emailForm">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="email-destination" class="form-label">Adresse email</label>
                                <input type="email" class="form-control" id="email-destination"
                                       value="{{ $user->email }}" required>
                                <div class="form-text">La facture sera envoyée en pièce jointe PDF</div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="copie-client" checked>
                                    <label class="form-check-label" for="copie-client">
                                        Envoyer une copie au client
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Envoyer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('styles')
    <style>
        .text-brown {
            color: #8B4513 !important;
        }

        .border-brown {
            border-color: #8B4513 !important;
        }

        .company-info p {
            margin-bottom: 0.25rem;
        }

        .billing-info p {
            margin-bottom: 0.25rem;
        }

        @media print {
            .btn-group,
            .modal,
            nav,
            .sidebar {
                display: none !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            body {
                background: white !important;
            }

            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion de l'envoi d'email pour les admins
            @if(Auth::user()->role !== 'client')
            const emailForm = document.getElementById('emailForm');
            if (emailForm) {
                emailForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const email = document.getElementById('email-destination').value;
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';

                    fetch(`{{ route('admin.facture.renvoyer', $commande->id) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            email: email
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Fermer le modal
                                bootstrap.Modal.getInstance(document.getElementById('emailModal')).hide();

                                // Afficher le message de succès
                                showAlert('success', data.message);
                            } else {
                                showAlert('error', data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showAlert('error', 'Une erreur est survenue lors de l\'envoi');
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                });
            }
            @endif

            // Fonction pour afficher les alertes
            function showAlert(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
                alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

                const container = document.querySelector('.container-fluid');
                container.insertBefore(alertDiv, container.firstChild);

                // Auto-masquer après 5 secondes
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }

            // Raccourci clavier pour imprimer (Ctrl+P)
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'p') {
                    e.preventDefault();
                    window.print();
                }
            });
        });
    </script>
@endsection
