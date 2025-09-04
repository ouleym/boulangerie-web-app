import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { PanierService } from '../../services/panier.service';
import { CommandeService } from '../../services/commande.service';
import { AuthService } from '../../services/AuthService';
import { ProduitPanier } from '../../models/produit-panier.model';
import { Commande } from '../../models/commande.model';
import { User } from '../../models/user.model';

@Component({
  selector: 'app-checkout',
  templateUrl: './checkout.component.html',
  styleUrls: ['./checkout.component.scss']
})
export class CommandesCheckoutComponent implements OnInit {
  produits: ProduitPanier[] = [];
  total = 0;
  client: User | null = null; 

  modePaiement: 'A_LIVRAISON' | 'EN_LIGNE' = 'A_LIVRAISON';

  constructor(
    private panierService: PanierService,
    private commandeService: CommandeService,
    private auth: AuthService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.produits = this.panierService.getPanier();
    this.total = this.panierService.getTotal();

    this.client = this.auth.getCurrentUser();
    if (!this.client) {
      this.router.navigateByUrl('/login');
    }
  }

  passerCommande(): void {
    if (!this.client) {
      alert('Vous devez être connecté pour passer une commande.');
      this.router.navigateByUrl('/login');
      return;
    }

    const commande: Commande = this.panierService.getPanierCommande(
      this.client,
      this.modePaiement
    );

    this.commandeService.createCommande(commande).subscribe({
      next: () => {
        alert('Commande enregistrée avec succès 🎉');
        this.panierService.clearPanier();
        this.produits = [];
        this.total = 0;
        this.router.navigateByUrl('/mes-commandes'); 
      },
      error: (err) => {
        console.error(err);
        alert('Une erreur est survenue lors de la commande.');
      }
    });
  }
}
