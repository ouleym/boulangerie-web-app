@extends('layouts.admin')

@section('title', 'Gestion des Livraisons')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">🚚 Gestion des Livraisons</h1>
            <a href="{{ route('admin.livraisons.dashboard') }}" class="btn btn-primary">
                <i class="fas fa-chart-bar"></i> Tableau de bord
            </a>
        </div>

        <!-- Statistiques rapides -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>{{ $stats['en_attente'] }}</h4>
                                <small>En attente</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>{{ $stats['confirmee'] }}</h4>
                                <small>Confirmées</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>{{ $stats['en_preparation'] }}</h4>
                                <small>En préparation</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-utensils fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-orange text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>{{ $stats['en_livraison'] }}</h4>
                                <small>En livraison</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-truck fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>{{ $stats['livree'] }}</h4>
                                <small>Livrées</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.livraisons.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="{{ request('search') }}" placeholder="Commande, client...">
                    </div>
                    <div class="col-md-2">
                        <label for="statut" class="form-label">Statut</label>
                        <select class="form-select" id="statut" name="statut">
                            <option value="">Tous</option>
                            <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                            <option value="confirmee" {{ request('statut') == 'confirmee' ? 'selected' : '' }}>Confirmée</option>
                            <option value="en_preparation" {{ request('statut') == 'en_preparation' ? 'selected' : '' }}>En préparation</option>
                            <option value="prete" {{ request('statut') == 'prete' ? 'selected' : '' }}>Prête</option>
                            <option value="en_livraison" {{ request('statut') == 'en_livraison' ? 'selected' : '' }}>En livraison</option>
                            <option value="livree" {{ request('statut') == 'livree' ? 'selected' : '' }}>Livrée</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                        <a href="{{ route('admin.livraisons.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Actions en lot -->
        <div class="card mb-4" id="bulk-actions" style="display: none;">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <span class="me-3"><strong id="selected-count">0</strong> livraisons sélectionnées</span>
                            <select class="form-select me-2" id="bulk-status" style="width: auto;">
                                <option value="">Changer le statut...</option>
                                <option value="confirmee">Confirmée</option>
                                <option value="en_preparation">En préparation</option>
                                <option value="prete">Prête</option>
                                <option value="en_livraison">En livraison</option>
                                <option value="livree">Livrée</option>
                            </select>
                            <button type="button" class="btn btn-primary" id="apply-bulk-action">Appliquer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des livraisons -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Liste des Livraisons</h5>
                <div>
                    <input type="checkbox" id="select-all" class="form-check-input me-2">
                    <label for="select-all" class="form-check-label">Tout sélectionner</label>
                </div>
            </div>
            <div class="card-body">
                @if($livraisons->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Commande</th>
                                <th>Client</th>
                                <th>Adresse</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Heure départ</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($livraisons as $livraison)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input livraison-checkbox"
                                               value="{{ $livraison->id }}">
                                    </td>
                                    <td>
                                        <strong>#{{ $livraison->commande->id }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $livraison->commande->user->prenom }} {{ $livraison->commande->user->nom }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $livraison->commande->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $livraison->commande->user->adresse }}<br>
                                            {{ $livraison->commande->user->ville }}
                                        </small>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($livraison->commande->montant_total, 2, ',', ' ') }} €</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $this->getStatusColor($livraison->statut) }}">
                                            {{ ucfirst(str_replace('_', ' ', $livraison->statut)) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $livraison->heure_depart ? $livraison->heure_depart->format('H:i') : '-' }}
                                    </td>
                                    <td>
                                        {{ $livraison->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.livraisons.show', $livraison) }}">
                                                        <i class="fas fa-eye"></i> Détails
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                @if($livraison->statut !== 'livree')
                                                    <li>
                                                        <button class="dropdown-item update-status"
                                                                data-livraison-id="{{ $livraison->id }}"
                                                                data-current-status="{{ $livraison->statut }}">
                                                            <i class="fas fa-edit"></i> Changer statut
                                                        </button>
                                                    </li>
                                                @endif
                                                @if($livraison->statut === 'livree')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.facture.show', $livraison->commande) }}">
                                                            <i class="fas fa-file-invoice"></i> Voir facture
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.facture.download', $livraison->commande) }}">
                                                            <i class="fas fa-download"></i> Télécharger facture
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $livraisons->withQueryString()->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune livraison trouvée</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal pour changer le statut -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Changer le statut de livraison</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="statusForm">
                    <div class="modal-body">
                        <input type="hidden" id="livraison-id">
                        <div class="mb-3">
                            <label for="nouveau-statut" class="form-label">Nouveau statut</label>
                            <select class="form-select" id="nouveau-statut" required>
                                <option value="">Sélectionner...</option>
                                <option value="en_attente">En attente</option>
                                <option value="confirmee">Confirmée</option>
                                <option value="en_preparation">En préparation</option>
                                <option value="prete">Prête</option>
                                <option value="en_livraison">En livraison</option>
                                <option value="livree">Livrée</option>
                                <option value="annulee">Annulée</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (optionnel)</label>
                            <textarea class="form-control" id="notes" rows="3"
                                      placeholder="Ajouter une note..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const livraisonCheckboxes = document.querySelectorAll('.livraison-checkbox');
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');

            // Gestion de la sélection multiple
            selectAllCheckbox.addEventListener('change', function() {
                livraisonCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActions();
            });

            livraisonCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBulkActions);
            });

            function updateBulkActions() {
                const checkedBoxes = document.querySelectorAll('.livraison-checkbox:checked');
                const count = checkedBoxes.length;

                selectedCount.textContent = count;
                bulkActions.style.display = count > 0 ? 'block' : 'none';

                selectAllCheckbox.indeterminate = count > 0 && count < livraisonCheckboxes.length;
                selectAllCheckbox.checked = count === livraisonCheckboxes.length;
            }

            // Gestion des actions en lot
            document.getElementById('apply-bulk-action').addEventListener('click', function() {
                const selectedIds = Array.from(document.querySelectorAll('.livraison-checkbox:checked'))
                    .map(cb => cb.value);
                const newStatus = document.getElementById('bulk-status').value;

                if (!newStatus) {
                    alert('Veuillez sélectionner un statut');
                    return;
                }

                if (confirm(`Changer le statut de ${selectedIds.length} livraisons ?`)) {
                    updateMultipleStatus(selectedIds, newStatus);
                }
            });

            // Gestion du changement de statut individuel
            document.querySelectorAll('.update-status').forEach(button => {
                button.addEventListener('click', function() {
                    const livraisonId = this.dataset.livraisonId;
                    const currentStatus = this.dataset.currentStatus;

                    document.getElementById('livraison-id').value = livraisonId;
                    document.getElementById('nouveau-statut').value = currentStatus;

                    new bootstrap.Modal(document.getElementById('statusModal')).show();
                });
            });

            // Soumission du formulaire de statut
            document.getElementById('statusForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const livraisonId = document.getElementById('livraison-id').value;
                const newStatus = document.getElementById('nouveau-statut').value;
                const notes = document.getElementById('notes').value;

                updateStatus(livraisonId, newStatus, notes);
            });

            function updateStatus(livraisonId, newStatus, notes = '') {
                fetch(`/admin/livraisons/${livraisonId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        statut: newStatus,
                        notes: notes
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erreur: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Une erreur est survenue');
                    });
            }

            function updateMultipleStatus(livraisonIds, newStatus) {
                fetch('/admin/livraisons/update-multiple-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        livraisons: livraisonIds,
                        statut: newStatus
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erreur: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Une erreur est survenue');
                    });
            }
        });

        // Fonction pour obtenir la couleur du badge selon le statut
        function getStatusColor(status) {
            const colors = {
                'en_attente': 'warning',
                'confirmee': 'info',
                'en_preparation': 'primary',
                'prete': 'success',
                'en_livraison': 'orange',
                'livree': 'success',
                'annulee': 'danger'
            };
            return colors[status] || 'secondary';
        }
    </script>

    <style>
        .bg-orange {
            background-color: #fd7e14 !important;
        }
    </style>
@endsection
