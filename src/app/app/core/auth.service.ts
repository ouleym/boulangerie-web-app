import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import {
  BehaviorSubject,
  Observable,
  tap,
  catchError,
  throwError,
  of,
  map,
  switchMap,
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
  private readonly SANCTUM_URL = 'http://localhost:8000/sanctum/csrf-cookie';

  private currentUserSubject = new BehaviorSubject<User | null>(null);
  public currentUser$ = this.currentUserSubject.asObservable();

  private isAuthenticatedSubject = new BehaviorSubject<boolean>(false);
  public isAuthenticated$ = this.isAuthenticatedSubject.asObservable();

  constructor(
    private http: HttpClient,
    private router: Router,
    private notificationService: NotificationService
  ) {
    this.restoreUserFromStorage();
  }

  /**
   * Récupérer le token CSRF avant toute authentification
   */
  private getCsrfToken(): Observable<any> {
    console.log('🔐 Récupération du token CSRF...');
    return this.http.get(this.SANCTUM_URL, {
      withCredentials: true,
      headers: new HttpHeaders({
        'Accept': 'application/json',
      })
    }).pipe(
      tap(() => console.log('✅ Token CSRF récupéré')),
      catchError((error) => {
        console.error('❌ Erreur récupération CSRF:', error);
        return throwError(() => error);
      })
    );
  }

  /**
   * Connexion avec gestion CSRF
   */
  login(credentials: LoginCredentials): Observable<AuthResponse> {
    console.log('🚀 Connexion avec gestion CSRF...');
    
    // Étape 1: Récupérer le token CSRF puis faire la connexion
    return this.getCsrfToken().pipe(
      switchMap(() => {
        // Étape 2: Effectuer la connexion
        const headers = new HttpHeaders({
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        });

        return this.http.post<AuthResponse>(`${this.API_URL}/auth/login`, credentials, {
          headers,
          withCredentials: true,
        });
      }),
      tap((response) => {
        console.log('✅ Login réussi:', response);
        if (response.user) {
          this.setCurrentUser(response.user);
          this.notificationService.showSuccess(
            'Connexion réussie',
            `Bienvenue ${this.getUserDisplayName(response.user)} !`
          );
        }
      }),
      catchError((error) => {
        console.error('❌ Erreur login:', error);
        
        let errorMessage = 'Erreur de connexion';
        
        if (error.status === 422) {
          errorMessage = 'Identifiants invalides';
        } else if (error.status === 419) {
          errorMessage = 'Session expirée, veuillez réessayer';
        } else if (error.status === 0) {
          errorMessage = 'Impossible de contacter le serveur';
        } else if (error.error?.message) {
          errorMessage = error.error.message;
        }

        this.notificationService.showError(errorMessage, 'Erreur de connexion');
        return throwError(() => error);
      })
    );
  }

  /**
   * Inscription avec gestion CSRF
   */
  register(userData: RegisterData): Observable<AuthResponse> {
    return this.getCsrfToken().pipe(
      switchMap(() => {
        const headers = new HttpHeaders({
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        });

        return this.http.post<AuthResponse>(`${this.API_URL}/auth/register`, userData, {
          headers,
          withCredentials: true,
        });
      }),
      tap((response) => {
        if (response.user) {
          this.setCurrentUser(response.user);
          this.notificationService.showSuccess(
            'Inscription réussie',
            `Bienvenue ${this.getUserDisplayName(response.user)} !`
          );
        }
      }),
      catchError((error) => {
        let errorMessage = "Erreur lors de l'inscription";
        
        if (error.error?.message) {
          errorMessage = error.error.message;
        } else if (error.error?.errors) {
          const errors = Object.values(error.error.errors).flat();
          errorMessage = errors[0] as string || errorMessage;
        }

        this.notificationService.showError(errorMessage, 'Erreur');
        return throwError(() => error);
      })
    );
  }

  /**
   * Déconnexion
   */
  logout(): Observable<void> {
    const headers = new HttpHeaders({
      'Accept': 'application/json',
    });

    return this.http
      .post<void>(`${this.API_URL}/auth/logout`, {}, { 
        headers,
        withCredentials: true 
      })
      .pipe(
        tap(() => {
          const userName = this.currentUser ? this.getUserDisplayName(this.currentUser) : '';
          this.clearCurrentUser();
          this.router.navigate(['/login']);
          this.notificationService.showInfo(
            `Au revoir ${userName} !`, 
            'Déconnexion réussie'
          );
        }),
        catchError((error) => {
          console.error('Erreur lors de la déconnexion:', error);
          this.clearCurrentUser();
          this.router.navigate(['/login']);
          this.notificationService.showInfo('Vous êtes déconnecté', 'Au revoir !');
          return of();
        })
      );
  }

  /**
   * Obtenir l'utilisateur actuel depuis le serveur
   */
  getCurrentUser(): Observable<User | null> {
    return this.http
      .get<User>(`${this.API_URL}/user`, { 
        withCredentials: true,
        headers: new HttpHeaders({
          'Accept': 'application/json',
        })
      })
      .pipe(
        tap((user) => {
          if (user) {
            console.log('👤 Utilisateur récupéré:', user);
            this.setCurrentUser(user);
          }
        }),
        catchError((error) => {
          console.error('❌ Erreur récupération utilisateur:', error);
          if (error.status === 401) {
            this.clearCurrentUser();
          }
          return of(null);
        })
      );
  }

  /**
   * Mise à jour du profil
   */
  updateProfile(userData: Partial<User>): Observable<User> {
    return this.http
      .put<User>(`${this.API_URL}/profile`, userData, { 
        withCredentials: true,
        headers: new HttpHeaders({
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        })
      })
      .pipe(
        tap((user) => {
          this.setCurrentUser(user);
          this.notificationService.showSuccess(
            'Profil mis à jour avec succès', 
            'Modifications sauvegardées'
          );
        }),
        catchError((error) => {
          let errorMessage = 'Erreur lors de la mise à jour';
          
          if (error.error?.message) {
            errorMessage = error.error.message;
          } else if (error.error?.errors) {
            const errors = Object.values(error.error.errors).flat();
            errorMessage = errors[0] as string || errorMessage;
          }

          this.notificationService.showError(errorMessage, 'Erreur');
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
      .put<void>(`${this.API_URL}/password`, passwords, { 
        withCredentials: true,
        headers: new HttpHeaders({
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        })
      })
      .pipe(
        tap(() => {
          this.notificationService.showSuccess(
            'Mot de passe modifié avec succès',
            'Sécurité renforcée'
          );
        }),
        catchError((error) => {
          let errorMessage = 'Erreur lors du changement de mot de passe';
          
          if (error.error?.message) {
            errorMessage = error.error.message;
          } else if (error.error?.errors) {
            const errors = Object.values(error.error.errors).flat();
            errorMessage = errors[0] as string || errorMessage;
          }

          this.notificationService.showError(errorMessage, 'Erreur');
          return throwError(() => error);
        })
      );
  }

  // === Méthodes utilitaires publiques ===

  get currentUser(): User | null {
    return this.currentUserSubject.value;
  }

  get isAuthenticated(): boolean {
    return this.isAuthenticatedSubject.value;
  }

  hasRole(roleName: string): boolean {
    if (!this.currentUser?.roles) {
      return false;
    }
    
    return this.currentUser.roles.some(role => 
      role.name.toLowerCase() === roleName.toLowerCase()
    );
  }

  hasPermission(permissionName: string): boolean {
    if (!this.currentUser?.permissions) {
      return false;
    }
    
    return this.currentUser.permissions.some(permission => 
      permission.name.toLowerCase() === permissionName.toLowerCase()
    );
  }

  isAdmin(): boolean {
    return this.hasRole('Admin') || this.hasRole('super-admin') || this.hasRole('administrator');
  }

  isEmployee(): boolean {
    return this.hasRole('Employee') || this.hasRole('employe') || this.isAdmin();
  }

  isClient(): boolean {
    return this.hasRole('Client') || this.hasRole('client');
  }

  getUserRoles(): string[] {
    return this.currentUser?.roles?.map(role => role.name) || [];
  }

  getUserPermissions(): string[] {
    return this.currentUser?.permissions?.map(permission => permission.name) || [];
  }

  validateToken(): Observable<boolean> {
    return this.getCurrentUser().pipe(
      map(user => !!user),
      catchError(() => of(false))
    );
  }

  // === Méthodes utilitaires privées ===

  private setCurrentUser(user: User): void {
    console.log('📝 Définition utilisateur:', user);
    this.currentUserSubject.next(user);
    this.isAuthenticatedSubject.next(true);
    localStorage.setItem('currentUser', JSON.stringify(user));
    localStorage.setItem('isAuthenticated', 'true');
  }

  private clearCurrentUser(): void {
    console.log('🗑️ Effacement utilisateur');
    this.currentUserSubject.next(null);
    this.isAuthenticatedSubject.next(false);
    localStorage.removeItem('currentUser');
    localStorage.removeItem('isAuthenticated');
  }

  private restoreUserFromStorage(): void {
    const userJson = localStorage.getItem('currentUser');
    const isAuthenticated = localStorage.getItem('isAuthenticated');
    
    if (userJson && isAuthenticated === 'true') {
      try {
        const user: User = JSON.parse(userJson);
        console.log('🔄 Restauration utilisateur depuis le stockage:', user);
        this.currentUserSubject.next(user);
        this.isAuthenticatedSubject.next(true);
        
        this.validateToken().subscribe(isValid => {
          if (!isValid) {
            console.log('❌ Token invalide, déconnexion');
            this.clearCurrentUser();
          }
        });
        
      } catch (error) {
        console.error('❌ Erreur parsing user from storage:', error);
        this.clearCurrentUser();
      }
    }
  }

  private getUserDisplayName(user: User): string {
    if (!user) return '';
    
    const prenom = user.prenom || '';
    const nom = user.nom || '';
    
    if (prenom && nom) {
      return `${prenom} ${nom}`;
    } else if (prenom) {
      return prenom;
    } else if (nom) {
      return nom;
    }
    
    return user.email || 'Utilisateur';
  }
}