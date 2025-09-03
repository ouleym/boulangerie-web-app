import { Component } from '@angular/core';
import { PayementService } from '../services/payement.service';
import { Router } from '@angular/router';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-payment',
  imports: [NgIf],
  templateUrl: './payement.component.html',
})
export class PayementComponent {
  montant = 1000;
  loading = false;
  errorMessage = '';

  constructor(
    private paymentService: PayementService,
    private router: Router
  ) {}

  payer() {
    // Vérifier si l'utilisateur est connecté
    const token = localStorage.getItem('token');
    if (!token) {
      this.errorMessage = 'Veuillez vous connecter pour effectuer un paiement.';
      this.router.navigate(['/login']);
      return;
    }

    this.loading = true;
    this.errorMessage = '';

    this.paymentService.initPayment(this.montant).subscribe({
      next: (res) => {
        console.log('Réponse du paiement:', res);
        if (res.data && res.data.payment_url) {
          window.location.href = res.data.payment_url;
        } else if (res.payment_url) {
          window.location.href = res.payment_url;
        } else {
          this.errorMessage = 'URL de paiement non trouvée dans la réponse.';
        }
        this.loading = false;
      },
      error: (error) => {
        console.error('Erreur de paiement:', error);
        
        if (error.status === 401) {
          this.errorMessage = 'Session expirée. Veuillez vous reconnecter.';
          localStorage.removeItem('token');
          this.router.navigate(['/login']);
        } else if (error.status === 422) {
          this.errorMessage = 'Données de paiement invalides.';
        } else {
          this.errorMessage = 'Erreur lors de l\'initialisation du paiement.';
        }
        
        this.loading = false;
      }
    });
  }
}