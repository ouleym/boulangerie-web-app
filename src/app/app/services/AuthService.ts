import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { User } from '../models/user.model';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private apiUrl = 'http://localhost:8080/api/auth';
  private storageKey = 'currentUser';

  constructor(private http: HttpClient) {}

  login(email: string, motDePasse: string): Observable<User> {
    return this.http.post<User>(`${this.apiUrl}/login`, { email, motDePasse })
      .pipe(tap(user => localStorage.setItem(this.storageKey, JSON.stringify(user))));
  }

  logout(): void {
    localStorage.removeItem(this.storageKey);
  }

  getCurrentUser(): User | null {
    const raw = localStorage.getItem(this.storageKey);
    return raw ? JSON.parse(raw) as User : null;
  }

  isAuthenticated(): boolean {
    return !!this.getCurrentUser();
  }
}
