export interface Promotion {
  id: number;
  titre: string;
  description?: string;
  reductionPourcentage: number;
  dateDebut: string;
  dateFin: string;
  produitsIds?: number[];
  pack?: boolean; 
  created_at: string;
  updated_at: string;

}
