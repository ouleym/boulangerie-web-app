import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap, catchError, throwError, of, switchMap, map } from 'rxjs';
import { Router } from '@angular/router';
import { User, AuthResponse, LoginCredentials, RegisterData } from '../models/user.model';
import { NotificationService } from './notification.service';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  [x: string]: any;
  private readonly API_URL = 'http://localhost:8000/api';
  private readonly SANCTUM_URL = 'http://localhost:8000/sanctum';
  
  private currentUserSubject = new BehaviorSubject<User | null>(null);
  public currentUser$ = this.currentUserSubject.asObservable();
  
  private isAuthenticatedSubject = new BehaviorSubject<boolean>(false);
  public isAuthenticated$ = this.isAuthenticatedSubject.asObservable();
  static currentUser$: any;

  constructor(
    private http: HttpClient,
    private router: Router,
    private notificationService: NotificationService
  ) {
    this.checkAuthStatus();
  }

  // Vérifier le statut d'authentification au démarrage
  private checkAuthStatus(): void {
    const token = this.getToken();
    if (token) {
      this.getCurrentUser().subscribe({
        next: (user) => {
          if (user) {
            this.setCurrentUser(user);
          } else {
            this.logout();
          }
        },
        error: () => {
          this.logout();
        }
      });
    }
  }

  // Obtenir le token CSRF pour Sanctum
  private getCsrfToken(): Observable<void> {
    return this.http.get<void>(`${this.SANCTUM_URL}/csrf-cookie`, {
      withCredentials: true
    });
  }

  // Connexion
  login(credentials: LoginCredentials): Observable<AuthResponse> {
    return this.getCsrfToken().pipe(
      switchMap(() => 
        this.http.post<AuthResponse>(`${this.API_URL}/login`, credentials, {
          withCredentials: true
        })
      ),
      tap(response => {
        if (response.user) {
          this.setCurrentUser(response.user);
          this.notificationService.showSuccess('Connexion réussie', 'Bienvenue !');
        }
      }),
      catchError(error => {
        this.notificationService.showError(
          error.error?.message || 'Erreur de connexion', 
          'Erreur'
        );
        return throwError(() => error);
      })
    );
  }

  // Inscription
  register(userData: RegisterData): Observable<AuthResponse> {
    return this.getCsrfToken().pipe(
      switchMap(() => 
        this.http.post<AuthResponse>(`${this.API_URL}/register`, userData, {
          withCredentials: true
        })
      ),
      tap(response => {
        if (response.user) {
          this.setCurrentUser(response.user);
          this.notificationService.showSuccess('Inscription réussie', 'Bienvenue !');
        }
      }),
      catchError(error => {
        this.notificationService.showError(
          error.error?.message || 'Erreur lors de l\'inscription', 
          'Erreur'
        );
        return throwError(() => error);
      })
    );
  }

  // Déconnexion
  logout(): Observable<void> {
    return this.http.post<void>(`${this.API_URL}/logout`, {}, {
      withCredentials: true
    }).pipe(
      tap(() => {
        this.clearCurrentUser();
        this.router.navigate(['/login']);
        this.notificationService.showInfo('Vous êtes déconnecté', 'Au revoir !');
      }),
      catchError(error => {
        // Même si l'API échoue, on déconnecte localement
        this.clearCurrentUser();
        this.router.navigate(['/login']);
        return of();
      })
    );
  }

  // Obtenir l'utilisateur actuel depuis l'API
  getCurrentUser(): Observable<User | null> {
    return this.http.get<{ user: User }>(`${this.API_URL}/user`, {
      withCredentials: true
    }).pipe(
      map((response: { user: any; }) => response.user),
      catchError(() => of(null))
    );
  }

  // Mettre à jour le profil
  updateProfile(userData: Partial<User>): Observable<User> {
    return this.http.put<{ user: User }>(`${this.API_URL}/profile`, userData, {
      withCredentials: true
    }).pipe(
      map(response => response.user),
      tap(user => {
        this.setCurrentUser(user);
        this.notificationService.showSuccess('Profil mis à jour', 'Succès');
      }),
      catchError(error => {
        this.notificationService.showError(
          error.error?.message || 'Erreur lors de la mise à jour', 
          'Erreur'
        );
        return throwError(() => error);
      })
    );
  }

  // Changer le mot de passe
  changePassword(passwords: { current_password: string, password: string, password_confirmation: string }): Observable<void> {
    return this.http.put<void>(`${this.API_URL}/password`, passwords, {
      withCredentials: true
    }).pipe(
      tap(() => {
        this.notificationService.showSuccess('Mot de passe modifié', 'Succès');
      }),
      catchError(error => {
        this.notificationService.showError(
          error.error?.message || 'Erreur lors du changement de mot de passe', 
          'Erreur'
        );
        return throwError(() => error);
      })
    );
  }

  // Getters pour les composants
  get currentUser(): User | null {
    return this.currentUserSubject.value;
  }

  get isAuthenticated(): boolean {
    return this.isAuthenticatedSubject.value;
  }

  // Vérifier les rôles
  hasRole(roleName: string): boolean {
    const user = this.currentUser;
    return user?.roles?.some(role => role.name === roleName) ?? false;
  }

  // Vérifier les permissions
  hasPermission(permissionName: string): boolean {
    const user = this.currentUser;
    return user?.permissions?.some(permission => permission.name === permissionName) ?? false;
  }

  // Vérifier si l'utilisateur est admin
  isAdmin(): boolean {
    return this.hasRole('admin') || this.hasRole('super-admin');
  }

  // Vérifier si l'utilisateur est employé
  isEmployee(): boolean {
    return this.hasRole('employe') || this.isAdmin();
  }

  // Méthodes privées
  private setCurrentUser(user: User): void {
    this.currentUserSubject.next(user);
    this.isAuthenticatedSubject.next(true);
    // Optionnel: stocker en localStorage pour persistance
    localStorage.setItem('currentUser', JSON.stringify(user));
  }

  private clearCurrentUser(): void {
    this.currentUserSubject.next(null);
    this.isAuthenticatedSubject.next(false);
    localStorage.removeItem('currentUser');
  }

  private getToken(): string | null {
    // Avec Sanctum, pas besoin de token local, utilise les cookies
    const user = localStorage.getItem('currentUser');
    return user ? 'sanctum' : null;
  }
}