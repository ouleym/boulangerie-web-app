import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse } from '../models/api-response.model';

export interface Livraison {
  id: number;
  commandeId: number;
  statut: 'en préparation' | 'prête' | 'en livraison' | 'livrée';
  adresseLivraison: string;
  livreur?: string;
  updated_at: string;
}

@Injectable({
  providedIn: 'root'
})
export class LivraisonService {
  private apiUrl = 'http://localhost:8080/api/livraisons';

  constructor(private http: HttpClient) {}

  // Récupérer une livraison par ID
  getLivraisonById(id: number): Observable<ApiResponse<Livraison>> {
    return this.http.get<ApiResponse<Livraison>>(`${this.apiUrl}/${id}`);
  }

  // Récupérer toutes les livraisons d'une commande
  getLivraisonsByCommande(commandeId: number): Observable<ApiResponse<Livraison[]>> {
    return this.http.get<ApiResponse<Livraison[]>>(`${this.apiUrl}/commande/${commandeId}`);
  }

  // Mettre à jour le statut d'une livraison
  updateStatut(id: number, statut: Livraison['statut']): Observable<ApiResponse<Livraison>> {
    return this.http.put<ApiResponse<Livraison>>(`${this.apiUrl}/${id}/statut`, { statut });
  }
}
