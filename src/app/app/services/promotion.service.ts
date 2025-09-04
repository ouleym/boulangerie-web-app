import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse, PaginatedResponse } from '../models/api-response.model';
import { Promotion } from '../models/promotion.model';

@Injectable({
  providedIn: 'root'
})
export class PromotionService {
  private apiUrl = 'http://localhost:8080/api/promotions';

  constructor(private http: HttpClient) {}

  // Récupérer toutes les promotions avec pagination
  getPromotions(page: number = 0, size: number = 10): Observable<PaginatedResponse<Promotion>> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('size', size.toString());

    return this.http.get<PaginatedResponse<Promotion>>(this.apiUrl, { params });
  }

  // Récupérer une promotion par son ID
  getPromotionById(id: number): Observable<ApiResponse<Promotion>> {
    return this.http.get<ApiResponse<Promotion>>(`${this.apiUrl}/${id}`);
  }

  // Créer une nouvelle promotion
  createPromotion(promotion: Promotion): Observable<ApiResponse<Promotion>> {
    return this.http.post<ApiResponse<Promotion>>(this.apiUrl, promotion);
  }

  // Mettre à jour une promotion existante
  updatePromotion(id: number, promotion: Promotion): Observable<ApiResponse<Promotion>> {
    return this.http.put<ApiResponse<Promotion>>(`${this.apiUrl}/${id}`, promotion);
  }

  // Supprimer une promotion
  deletePromotion(id: number): Observable<ApiResponse<void>> {
    return this.http.delete<ApiResponse<void>>(`${this.apiUrl}/${id}`);
  }
}
