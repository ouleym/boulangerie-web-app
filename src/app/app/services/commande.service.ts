import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse, PaginatedResponse } from '../models/api-response.model';
import { Commande } from '../models/commande.model';

@Injectable({
  providedIn: 'root'
})
export class CommandeService {
  private apiUrl = 'http://localhost:8080/api/commandes';

  constructor(private http: HttpClient) {}

  // Récupérer toutes les commandes avec pagination
  getCommandes(page: number = 0, size: number = 10): Observable<PaginatedResponse<Commande>> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('size', size.toString());
    return this.http.get<PaginatedResponse<Commande>>(this.apiUrl, { params });
  }

  // Récupérer une commande par son ID
  getCommandeById(id: number): Observable<ApiResponse<Commande>> {
    return this.http.get<ApiResponse<Commande>>(`${this.apiUrl}/${id}`);
  }

  // Créer une nouvelle commande
  createCommande(commande: Commande): Observable<ApiResponse<Commande>> {
    return this.http.post<ApiResponse<Commande>>(this.apiUrl, commande);
  }

  // Mettre à jour une commande existante
  updateCommande(id: number, commande: Commande): Observable<ApiResponse<Commande>> {
    return this.http.put<ApiResponse<Commande>>(`${this.apiUrl}/${id}`, commande);
  }

  // Mettre à jour seulement le statut d'une commande
  updateStatut(id: number, statut: string): Observable<ApiResponse<Commande>> {
    return this.http.patch<ApiResponse<Commande>>(`${this.apiUrl}/${id}/statut`, { statut });
  }

  // Supprimer une commande
  deleteCommande(id: number): Observable<ApiResponse<void>> {
    return this.http.delete<ApiResponse<void>>(`${this.apiUrl}/${id}`);
  }
}
