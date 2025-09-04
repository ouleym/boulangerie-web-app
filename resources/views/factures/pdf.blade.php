<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture #{{ $commande->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .company-info {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .invoice-info {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 10px;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 10px;
        }

        .billing-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .billing-to {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .invoice-details {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 10px;
            border-bottom: 2px solid #8B4513;
            padding-bottom: 5px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .details-table th,
        .details-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .details-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #8B4513;
        }

        .details-table .text-right {
            text-align: right;
        }

        .details-table .text-center {
            text-align: center;
        }

        .totals {
            width: 100%;
            margin-top: 20px;
        }

        .totals-table {
            width: 300px;
            float: right;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .totals-table .total-label {
            text-align: right;
            font-weight: bold;
        }

        .totals-table .total-amount {
            text-align: right;
            width: 120px;
        }

        .final-total {
            background-color: #8B4513;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #8B4513;
            font-size: 10px;
            color: #666;
            text-align: center;
        }

        .payment-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- En-tête -->
    <div class="header">
        <div class="company-info">
            <div class="logo">🥖 Ma Boulangerie</div>
            <div>123 Rue des Boulangers</div>
            <div>75001 Paris, France</div>
            <div>Tél: 01 23 45 67 89</div>
            <div>Email: contact@boulangerie.com</div>
            <div>SIRET: 123 456 789 00012</div>
        </div>
        <div class="invoice-info">
            <div class="invoice-title">FACTURE</div>
            <div><strong>N° {{ str_pad($commande->id, 6, '0', STR_PAD_LEFT) }}</strong></div>
            <div>Date: {{ $commande->created_at->format('d/m/Y') }}</div>
        </div>
    </div>

    <!-- Informations de facturation -->
    <div class="billing-info">
        <div class="billing-to">
            <div class="section-title">FACTURER À</div>
            <div><strong>{{ $user->prenom }} {{ $user->nom }}</strong></div>
            <div>{{ $user->email }}</div>
            @if($user->telephone)
                <div>{{ $user->telephone }}</div>
            @endif
            @if($user->adresse)
                <div>{{ $user->adresse }}</div>
            @endif
            @if($user->ville)
                <div>{{ $user->ville }}</div>
            @endif
        </div>
        <div class="invoice-details">
            <div class="section-title">DÉTAILS FACTURE</div>
            <table style="width: 100%;">
                <tr>
                    <td><strong>Commande N°:</strong></td>
                    <td style="text-align: right;">{{ $commande->id }}</td>
                </tr>
                <tr>
                    <td><strong>Date commande:</strong></td>
                    <td style="text-align: right;">{{ $commande->created_at->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Statut:</strong></td>
                    <td style="text-align: right;">{{ ucfirst(str_replace('_', ' ', $commande->statut)) }}</td>
                </tr>
                <tr>
                    <td><strong>Mode de paiement:</strong></td>
                    <td style="text-align: right;">{{ $commande->mode_paiement === 'en_ligne' ? 'En ligne' : 'À la livraison' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Détails des produits -->
    <div class="section-title">DÉTAILS DE LA COMMANDE</div>
    <table class="details-table">
        <thead>
        <tr>
            <th>Produit</th>
            <th class="text-center">Quantité</th>
            <th class="text-right">Prix unitaire</th>
            <th class="text-right">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($commande->detailsCommandes as $detail)
            <tr>
                <td>
                    <strong>{{ $detail->produit->nom }}</strong>
                    @if($detail->produit->description)
                        <br><small style="color: #666;">{{ Str::limit($detail->produit->description, 60) }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $detail->quantite }}</td>
                <td class="text-right">{{ number_format($detail->prix_unitaire, 2, ',', ' ') }} €</td>
                <td class="text-right">{{ number_format($detail->prix_total, 2, ',', ' ') }} €</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Totaux -->
    <div class="totals clearfix">
        <table class="totals-table">
            <tr>
                <td class="total-label">Sous-total:</td>
                <td class="total-amount">{{ number_format($commande->montant_produit ?? $commande->montant_total, 2, ',', ' ') }} €</td>
            </tr>
            @if(isset($commande->frais_livraison) && $commande->frais_livraison > 0)
                <tr>
                    <td class="total-label">Frais de livraison:</td>
                    <td class="total-amount">{{ number_format($commande->frais_livraison, 2, ',', ' ') }} €</td>
                </tr>
            @endif
            @if(isset($commande->reduction) && $commande->reduction > 0)
                <tr>
                    <td class="total-label">Réduction:</td>
                    <td class="total-amount">-{{ number_format($commande->reduction, 2, ',', ' ') }} €</td>
                </tr>
            @endif
            <tr class="final-total">
                <td class="total-label">TOTAL TTC:</td>
                <td class="total-amount">{{ number_format($commande->montant_total, 2, ',', ' ') }} €</td>
            </tr>
        </table>
    </div>

    <!-- Informations de paiement -->
    @if($commande->mode_paiement === 'en_ligne')
        <div class="payment-info">
            <div class="section-title">INFORMATIONS DE PAIEMENT</div>
            <p><strong>✅ Paiement effectué en ligne</strong></p>
            <p>Date de paiement: {{ $commande->created_at->format('d/m/Y à H:i') }}</p>
        </div>
    @else
        <div class="payment-info">
            <div class="section-title">INFORMATIONS DE PAIEMENT</div>
            <p><strong>💰 Paiement à la livraison</strong></p>
            <p>Montant à régler: <strong>{{ number_format($commande->montant_total, 2, ',', ' ') }} €</strong></p>
        </div>
    @endif

    <!-- Informations de livraison -->
    @if($commande->livraison)
        <div style="margin-top: 30px;">
            <div class="section-title">INFORMATIONS DE LIVRAISON</div>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <strong>Statut:</strong> {{ ucfirst(str_replace('_', ' ', $commande->livraison->statut)) }}<br>
                        @if($commande->livraison->heure_depart)
                            <strong>Heure de départ:</strong> {{ $commande->livraison->heure_depart->format('H:i') }}<br>
                        @endif
                    </td>
                    <td style="width: 50%; vertical-align: top;">
                        <strong>Adresse de livraison:</strong><br>
                        {{ $user->adresse }}<br>
                        {{ $user->ville }}
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <p><strong>Ma Boulangerie</strong> - Artisan Boulanger depuis 1985</p>
        <p>Merci pour votre confiance ! Pour toute question, contactez-nous au 01 23 45 67 89</p>
        <p>Cette facture a été générée automatiquement le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</div>
</body>
</html>
