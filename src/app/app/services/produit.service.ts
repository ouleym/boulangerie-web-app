import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse, PaginatedResponse } from '../models/api-response.model';
import { Produit } from '../models/produit.model';

@Injectable({
  providedIn: 'root'
})
export class ProduitService {
  private apiUrl = 'http://localhost:8080/api/produits';

  constructor(private http: HttpClient) {}

  // Récupérer tous les produits avec pagination
  getProduits(page: number = 0, size: number = 10): Observable<PaginatedResponse<Produit>> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('size', size.toString());

    return this.http.get<PaginatedResponse<Produit>>(this.apiUrl, { params });
  }

  // Récupérer un produit par son ID
  getProduitById(id: number): Observable<ApiResponse<Produit>> {
    return this.http.get<ApiResponse<Produit>>(`${this.apiUrl}/${id}`);
  }

  // Créer un nouveau produit
  createProduit(produit: Produit): Observable<ApiResponse<Produit>> {
    return this.http.post<ApiResponse<Produit>>(this.apiUrl, produit);
  }

  // Mettre à jour un produit existant
  updateProduit(id: number, produit: Produit): Observable<ApiResponse<Produit>> {
    return this.http.put<ApiResponse<Produit>>(`${this.apiUrl}/${id}`, produit);
  }

  // Supprimer un produit
  deleteProduit(id: number): Observable<ApiResponse<void>> {
    return this.http.delete<ApiResponse<void>>(`${this.apiUrl}/${id}`);
  }
}
