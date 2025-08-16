import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import {
  BehaviorSubject,
  Observable,
  tap,
  catchError,
  throwError,
  of,
  switchMap,
  map,
} from 'rxjs';
import { Router } from '@angular/router';
import {
  User,
  AuthResponse,
  LoginCredentials,
  RegisterData,
} from '../models/user.model';
import { NotificationService } from './notification.service';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
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
    this.restoreUserFromStorage();
  }

  /**
   * Récupération du cookie CSRF requis par Sanctum
   */
  private getCsrfToken(): Observable<void> {
    return this.http.get<void>(`${this.SANCTUM_URL}/csrf-cookie`, {
      withCredentials: true,
    });
  }

  /**
   * Connexion avec Sanctum
   */
  login(credentials: LoginCredentials): Observable<AuthResponse> {
    return this.getCsrfToken().pipe(
      switchMap(() =>
        this.http.post<AuthResponse>(`${this.API_URL}/login`, credentials, {
          withCredentials: true,
        })
      ),
      tap((response) => {
        if (response.user) {
          this.setCurrentUser(response.user);
          this.notificationService.showSuccess(
            'Connexion réussie',
            'Bienvenue !'
          );
        }
      }),
      catchError((error) => {
        this.notificationService.showError(
          error.error?.message || 'Erreur de connexion',
          'Erreur'
        );
        return throwError(() => error);
      })
    );
  }

  /**
   * Inscription
   */
  register(userData: RegisterData): Observable<AuthResponse> {
    return this.getCsrfToken().pipe(
      switchMap(() =>
        this.http.post<AuthResponse>(`${this.API_URL}/register`, userData, {
          withCredentials: true,
        })
      ),
      tap((response) => {
        if (response.user) {
          this.setCurrentUser(response.user);
          this.notificationService.showSuccess(
            'Inscription réussie',
            'Bienvenue !'
          );
        }
      }),
      catchError((error) => {
        this.notificationService.showError(
          error.error?.message || "Erreur lors de l'inscription",
          'Erreur'
        );
        return throwError(() => error);
      })
    );
  }

  /**
   * Déconnexion
   */
  logout(): Observable<void> {
    return this.http
      .post<void>(`${this.API_URL}/logout`, {}, { withCredentials: true })
      .pipe(
        tap(() => {
          this.clearCurrentUser();
          this.router.navigate(['/login']);
          this.notificationService.showInfo('Vous êtes déconnecté', 'Au revoir !');
        }),
        catchError(() => {
          // Même si l'API échoue, on déconnecte localement
          this.clearCurrentUser();
          this.router.navigate(['/login']);
          return of();
        })
      );
  }

  /**
   * Obtenir l'utilisateur actuel
   */
  getCurrentUser(): Observable<User | null> {
    return this.http
      .get<User>(`${this.API_URL}/user`, { withCredentials: true })
      .pipe(
        tap((user) => {
          if (user) this.setCurrentUser(user);
        }),
        catchError(() => of(null))
      );
  }

  /**
   * Mise à jour du profil
   */
  updateProfile(userData: Partial<User>): Observable<User> {
    return this.http
      .put<User>(`${this.API_URL}/profile`, userData, { withCredentials: true })
      .pipe(
        tap((user) => {
          this.setCurrentUser(user);
          this.notificationService.showSuccess('Profil mis à jour', 'Succès');
        }),
        catchError((error) => {
          this.notificationService.showError(
            error.error?.message || 'Erreur lors de la mise à jour',
            'Erreur'
          );
          return throwError(() => error);
        })
      );
  }

  /**
   * Changer le mot de passe
   */
  changePassword(passwords: {
    current_password: string;
    password: string;
    password_confirmation: string;
  }): Observable<void> {
    return this.http
      .put<void>(`${this.API_URL}/password`, passwords, { withCredentials: true })
      .pipe(
        tap(() => {
          this.notificationService.showSuccess(
            'Mot de passe modifié',
            'Succès'
          );
        }),
        catchError((error) => {
          this.notificationService.showError(
            error.error?.message || 'Erreur lors du changement de mot de passe',
            'Erreur'
          );
          return throwError(() => error);
        })
      );
  }

  // === Gestion utilisateur local ===
  get currentUser(): User | null {
    return this.currentUserSubject.value;
  }

  get isAuthenticated(): boolean {
    return this.isAuthenticatedSubject.value;
  }

  hasRole(roleName: string): boolean {
    return (
      this.currentUser?.roles?.some((role) => role.name === roleName) ?? false
    );
  }

  hasPermission(permissionName: string): boolean {
    return (
      this.currentUser?.permissions?.some(
        (permission) => permission.name === permissionName
      ) ?? false
    );
  }

  isAdmin(): boolean {
    return this.hasRole('admin') || this.hasRole('super-admin');
  }

  isEmployee(): boolean {
    return this.hasRole('employe') || this.isAdmin();
  }

  // === Méthodes privées ===
  private setCurrentUser(user: User): void {
    this.currentUserSubject.next(user);
    this.isAuthenticatedSubject.next(true);
    localStorage.setItem('currentUser', JSON.stringify(user));
  }

  private clearCurrentUser(): void {
    this.currentUserSubject.next(null);
    this.isAuthenticatedSubject.next(false);
    localStorage.removeItem('currentUser');
  }

  private restoreUserFromStorage(): void {
    const userJson = localStorage.getItem('currentUser');
    if (userJson) {
      const user: User = JSON.parse(userJson);
      this.currentUserSubject.next(user);
      this.isAuthenticatedSubject.next(true);
    }
  }
}
