import { Produit } from './produit.model';

export interface DetailCommande {
  id: number;
  produit: Produit;   
  quantite: number;   
  prixUnitaire: number; 
  sousTotal: number;  
}
