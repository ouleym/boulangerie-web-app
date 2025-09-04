import { Produit } from './produit.model';

export interface ProduitPanier extends Produit {
  quantite: number;
}
