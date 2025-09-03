import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class PayementService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  // Récupérer le token depuis le localStorage
  private getAuthHeaders(): HttpHeaders {
    const token = localStorage.getItem('token'); // ou sessionStorage
    
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
    const headers = this.getAuthHeaders();
    
    return this.http.post(`${this.apiUrl}/payment/init`, 
      { montant }, 
      { headers }
    );
  }
}