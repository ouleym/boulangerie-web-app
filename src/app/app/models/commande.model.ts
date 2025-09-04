import { User } from './user.model';
import { DetailCommande } from './detail-commande.model';
import { Livraison } from './livraison.model';
import { Facture } from './facture.model';

export interface Commande {
  id: number;
  client: User;
  details: DetailCommande[];
  statut: 'EN_PREPARATION' | 'PRETE' | 'EN_LIVRAISON' | 'LIVREE';
  modePaiement: 'A_LIVRAISON' | 'EN_LIGNE';
  total: number;
  createdAt: string;
  updatedAt: string;

  livraison?: Livraison;
  facture?: Facture;
}
