import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse } from '../models/api-response.model';

export interface Categorie {
  id: number;
  nom: string;
  description?: string;
  created_at: string;
  updated_at: string;
}

@Injectable({
  providedIn: 'root'
})
export class CategorieService {
  private apiUrl = 'http://localhost:8080/api/categories';

  constructor(private http: HttpClient) {}

  // Récupérer toutes les catégories
  getAllCategories(): Observable<ApiResponse<Categorie[]>> {
    return this.http.get<ApiResponse<Categorie[]>>(this.apiUrl);
  }

  // Récupérer une catégorie par son ID
  getCategorieById(id: number): Observable<ApiResponse<Categorie>> {
    return this.http.get<ApiResponse<Categorie>>(`${this.apiUrl}/${id}`);
  }

  // Créer une nouvelle catégorie
  createCategorie(cat: Categorie): Observable<ApiResponse<Categorie>> {
    return this.http.post<ApiResponse<Categorie>>(this.apiUrl, cat);
  }

  // Mettre à jour une catégorie existante
  updateCategorie(id: number, cat: Categorie): Observable<ApiResponse<Categorie>> {
    return this.http.put<ApiResponse<Categorie>>(`${this.apiUrl}/${id}`, cat);
  }

  // Supprimer une catégorie
  deleteCategorie(id: number): Observable<ApiResponse<void>> {
    return this.http.delete<ApiResponse<void>>(`${this.apiUrl}/${id}`);
  }
}
