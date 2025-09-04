export interface Produit {
  id: number;
  nom: string;
  description: string;
  prix: number;
  stock: number;
  categorieId: number;
  imageUrl?: string;
  allergenes?: string[];
  created_at?: string;
  updated_at?: string;
}
