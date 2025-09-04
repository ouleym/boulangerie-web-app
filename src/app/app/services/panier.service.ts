import { Injectable } from '@angular/core';
import { Produit } from '../models/produit.model';
import { ProduitPanier } from '../models/produit-panier.model';
import { Commande } from '../models/commande.model';
import { DetailCommande } from '../models/detail-commande.model';
import { User } from '../models/user.model';

@Injectable({
  providedIn: 'root'
})
export class PanierService {
  private produits: ProduitPanier[] = [];

  constructor() {}

  // Ajouter un produit au panier
  addProduit(produit: Produit, quantite: number = 1): void {
    const existing = this.produits.find(p => p.id === produit.id);
    if (existing) {
      existing.quantite += quantite;
    } else {
      this.produits.push({ ...produit, quantite });
    }
  }

  // Supprimer un produit du panier
  removeProduit(produitId: number): void {
    this.produits = this.produits.filter(p => p.id !== produitId);
  }

  // Récupérer les produits dans le panier
  getPanier(): ProduitPanier[] {
    return this.produits;
  }

  // Calculer le total
  getTotal(): number {
    return this.produits.reduce((sum, p) => sum + p.prix * p.quantite, 0);
  }

  // Convertir le panier en commande
  getPanierCommande(client: User, modePaiement: 'A_LIVRAISON' | 'EN_LIGNE'): Commande {
    const details: DetailCommande[] = this.produits.map(p => ({
      id: 0, // généré par le backend
      produit: p,
      quantite: p.quantite,
      prixUnitaire: p.prix,
      sousTotal: p.prix * p.quantite
    }));

    const total = details.reduce((sum, d) => sum + d.sousTotal, 0);
    const now = new Date().toISOString();

    return {
      id: 0, // généré par le backend
      client: client,
      details: details,
      statut: 'EN_PREPARATION',
      modePaiement: modePaiement,
      total: total,
      createdAt: now,
      updatedAt: now
    };
  }

  // Vider le panier
  clearPanier(): void {
    this.produits = [];
  }
}
