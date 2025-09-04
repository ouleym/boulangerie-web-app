export interface Livraison {
  id: number;
  commandeId: number;
  adresseLivraison: string;
  statut: 'en préparation' | 'prête' | 'en livraison' | 'livrée';
  livreur?: string;
  updated_at: string;
}
