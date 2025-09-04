import { Component } from '@angular/core';
import { PayementService } from '../services/payement.service';
import { Router } from '@angular/router';
import { NgIf } from '@angular/common';
import { NgModel } from '@angular/forms';

@Component({
  selector: 'app-payment',
  standalone:true,
  imports: [NgIf],
  templateUrl: './payement.component.html',
})
export class PayementComponent {
  montant = 1000;
  loading = false;
  errorMessage = '';
  successMessage = ''; // ✅ AJOUTÉ pour les messages de succès

  constructor(
    private paymentService: PayementService,
    private router: Router
  ) {}

  payer() {
    // Validation basique
    if (this.montant < 100) {
      this.errorMessage = 'Le montant minimum est de 100 XOF.';
      return;
    }

    // Vérifier si l'utilisateur est connecté
    const token = localStorage.getItem('token');
    if (!token) {
      this.errorMessage = 'Veuillez vous connecter pour effectuer un paiement.';
      this.router.navigate(['/login']);
      return;
    }

    this.loading = true;
    this.errorMessage = '';
    this.successMessage = '';

    console.log('🚀 Initialisation du paiement...', { montant: this.montant });

    this.paymentService.initPayment(this.montant).subscribe({
      next: (res) => {
        console.log('✅ Réponse du paiement reçue:', res);
        
        // ✅ AMÉLIORÉ: Gestion plus robuste de la réponse
        if (res?.success === false) {
          this.errorMessage = res.message || 'Erreur lors de l\'initialisation du paiement.';
          this.loading = false;
          return;
        }

        // Chercher l'URL de paiement dans différents endroits possibles
        let paymentUrl = null;
        
        if (res?.data?.payment_url) {
          paymentUrl = res.data.payment_url;
        } else if (res?.payment_url) {
          paymentUrl = res.payment_url;
        } else if (res?.data?.url) { // Parfois CinetPay utilise 'url'
          paymentUrl = res.data.url;
        }

        if (paymentUrl) {
          console.log('🌐 Redirection vers:', paymentUrl);
          this.successMessage = 'Redirection vers la page de paiement...';
          
          // ✅ AMÉLIORÉ: Petit délai pour que l'utilisateur voie le message
          setTimeout(() => {
            window.location.href = paymentUrl;
          }, 1000);
        } else {
          console.error('❌ URL de paiement introuvable dans la réponse:', res);
          this.errorMessage = 'URL de paiement non trouvée dans la réponse du serveur.';
        }
        
        this.loading = false;
      },
      error: (error) => {
        console.error('❌ Erreur de paiement:', error);
        this.loading = false;
        
        // ✅ AMÉLIORÉ: Gestion des erreurs plus détaillée
        if (error.status === 0) {
          this.errorMessage = 'Impossible de contacter le serveur. Vérifiez votre connexion.';
        } else if (error.status === 401) {
          this.errorMessage = 'Session expirée. Veuillez vous reconnecter.';
          localStorage.removeItem('token');
          this.router.navigate(['/login']);
        } else if (error.status === 422) {
          // Gestion des erreurs de validation
          if (error.error?.messages) {
            const validationErrors = error.error.messages;
            const firstError = Object.values(validationErrors)[0];
            this.errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
          } else {
            this.errorMessage = 'Données de paiement invalides.';
          }
        } else if (error.status === 500) {
          // Afficher le message du serveur si disponible
          if (error.error?.message) {
            this.errorMessage = `Erreur serveur: ${error.error.message}`;
          } else {
            this.errorMessage = 'Erreur interne du serveur. Réessayez plus tard.';
          }
        } else {
          this.errorMessage = error.error?.message || 'Erreur lors de l\'initialisation du paiement.';
        }
      }
    });
  }

  // ✅ AJOUTÉ: Méthode pour réessayer
  retry() {
    this.errorMessage = '';
    this.successMessage = '';
    this.payer();
  }

  // ✅ AJOUTÉ: Méthode pour annuler et retourner
  annuler() {
    this.router.navigate(['/dashboard']); // ou la route de votre choix
  }
}