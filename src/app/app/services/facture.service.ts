import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse } from '../models/api-response.model';

export interface Facture {
  id: number;
  commandeId: number;
  montant: number;
  pdfUrl: string;
  created_at: string;
}

@Injectable({
  providedIn: 'root'
})
export class FactureService {
  private apiUrl = 'http://localhost:8080/api/factures';

  constructor(private http: HttpClient) {}

  // Récupérer une facture par ID
  getFactureById(id: number): Observable<ApiResponse<Facture>> {
    return this.http.get<ApiResponse<Facture>>(`${this.apiUrl}/${id}`);
  }

  // Récupérer toutes les factures d'une commande
  getFacturesByCommande(commandeId: number): Observable<ApiResponse<Facture[]>> {
    return this.http.get<ApiResponse<Facture[]>>(`${this.apiUrl}/commande/${commandeId}`);
  }

  // Générer une facture pour une commande
  genererFacture(commandeId: number): Observable<ApiResponse<Facture>> {
    return this.http.post<ApiResponse<Facture>>(`${this.apiUrl}/generer`, { commandeId });
  }

  // Télécharger une facture au format PDF
  telechargerFacture(id: number): Observable<Blob> {
    return this.http.get(`${this.apiUrl}/${id}/download`, { responseType: 'blob' });
  }
}
