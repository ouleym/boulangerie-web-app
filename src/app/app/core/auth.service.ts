import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { AuthResponse } from '../models/user.model';
import { environment } from '../../../environments/environment.development';
import { jwtDecode } from 'jwt-decode';

export interface JwtPayload {
  exp: number;
  iat: number;
  roles: string[];
  sub?: string; // ID utilisateur
  email?: string;
  nom?: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  redirectUrl: string = '/login';
  
  private loggedInSubject = new BehaviorSubject<boolean>(this.isAuthenticated());
  public loggedIn$ = this.loggedInSubject.asObservable();

  constructor(private httpClient: HttpClient) {}

  login(request: any): Observable<AuthResponse> {
    return this.httpClient.post(
      `${environment.ApiUrl}/login`,
      request,
      { responseType: 'text' }
    ).pipe(
      map((resp: string) => {
        try {
          const parsed = JSON.parse(resp);
          if (parsed.token) {
            localStorage.setItem('token', parsed.token);
            this.loggedInSubject.next(true);
            console.log('Utilisateur connecté avec succès');
          }
          return parsed as AuthResponse;
        } catch (e) {
          console.error('La réponse JSON est invalide :', resp);
          throw new Error('Réponse serveur non conforme.');
        }
      }),
      catchError(error => {
        console.error('Erreur de login :', error);
        return throwError(() => error);
      })
    );
  }

  register(request: any): Observable<AuthResponse> {
    return this.httpClient.post<AuthResponse>(`${environment.ApiUrl}/register`, request).pipe(
      map((response: AuthResponse) => {
        if (response.token) {
          localStorage.setItem('token', response.token);
          this.loggedInSubject.next(true);
        }
        return response;
      }),
      catchError((error) => {
        return throwError(() => error);
      })
    );
  }

  getToken(): string | null {
    return localStorage.getItem('token');
  }

  getDecodedToken(): JwtPayload | null {
    const token = this.getToken();
    if (!token) return null;
    
    try {
      return jwtDecode<JwtPayload>(token);
    } catch (error) {
      console.error('Erreur lors du décodage du token:', error);
      this.logout(); // Token corrompu, déconnecter l'utilisateur
      return null;
    }
  }

  getUserRoles(): string[] {
    const decoded = this.getDecodedToken();
    return decoded?.roles || [];
  }

  isAuthenticated(): boolean {
    const token = this.getToken();
    if (!token) return false;

    const decoded = this.getDecodedToken();
    if (!decoded) return false;

    const now = Math.floor(Date.now() / 1000);
    const isValid = decoded.exp > now;
    
    if (!isValid) {
      this.logout(); // Token expiré, déconnecter automatiquement
    }
    
    return isValid;
  }

  hasRole(role: string): boolean {
    return this.getUserRoles().includes(role);
  }

  logout(): void {
    localStorage.removeItem('token');
    this.loggedInSubject.next(false);
    console.log('Utilisateur déconnecté');
  }
}