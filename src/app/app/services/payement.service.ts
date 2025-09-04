import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class PayementService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  // Récupérer le token depuis le localStorage
  private getAuthHeaders(): HttpHeaders {
    const token = localStorage.getItem('token');
    
    if (!token) {
      throw new Error('Token d\'authentification manquant. Veuillez vous connecter.');
    }

    return new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    });
  }

  initPayment(montant: number): Observable<any> {
    try {
      const headers = this.getAuthHeaders();
      
      return this.http.post(`${this.apiUrl}/payment/init`, 
        { montant }, 
        { headers }
      ).pipe(
        catchError(this.handleError)
      );
    } catch (error) {
      return throwError(() => error);
    }
  }

  // ✅ AJOUTÉ: Gestion centralisée des erreurs
  private handleError(error: HttpErrorResponse): Observable<never> {
    console.error('Erreur PayementService:', error);
    return throwError(() => error);
  }
}