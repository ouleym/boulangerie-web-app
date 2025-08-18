import { Component, OnInit } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/auth.service';
import { NgIf } from '@angular/common';
import { User } from '../../models/user.model';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [NgIf, RouterLink],
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss']
})
export class NavbarComponent implements OnInit {
  isLoggedIn = false;
  currentUser: User | null = null;
  dashboardRoute = '/login';
  brandTitle = 'E-Commerce App';

  constructor(public authService: AuthService, private router: Router) {}

  ngOnInit(): void {
    // Initialiser l'état actuel immédiatement
    this.isLoggedIn = this.authService.isAuthenticated;
    this.currentUser = this.authService.currentUser;
    
    if (this.isLoggedIn && this.currentUser) {
      this.setDashboardInfo();
    }

    // S'abonner aux changements d'authentification
    this.authService.isAuthenticated$.subscribe(isAuth => {
      this.isLoggedIn = isAuth;
      if (!isAuth) {
        this.currentUser = null;
        this.brandTitle = 'E-Commerce App';
        this.dashboardRoute = '/login';
      }
    });

    // S'abonner aux changements d'utilisateur
    this.authService.currentUser$.subscribe(user => {
      this.currentUser = user;
      if (this.isLoggedIn && this.currentUser) {
        this.setDashboardInfo();
      }
    });
  }

  /**
   * Déterminer le dashboard et le titre en fonction du rôle
   */
  private setDashboardInfo(): void {
    if (this.authService.hasRole('Admin')) {
      this.dashboardRoute = '/admin';
      this.brandTitle = 'Admin Panel';
    } else if (this.authService.hasRole('Employee')) {
      this.dashboardRoute = '/dashboard/employe';
      this.brandTitle = 'Employee Panel';
    } else if (this.authService.hasRole('Client')) {
      this.dashboardRoute = '/dashboard/client';
      this.brandTitle = 'Client Panel';
    } else {
      this.dashboardRoute = '/login';
      this.brandTitle = 'E-Commerce App';
    }
  }

  /**
   * Déconnexion
   */
  logout(): void {
    this.authService.logout().subscribe();
  }

  /**
   * Vérifier si l'utilisateur a accès aux fonctionnalités admin
   */
  get canAccessAdmin(): boolean {
    return this.authService.hasRole('Admin') || this.authService.hasRole('admin') || this.authService.hasRole('super-admin');
  }

  /**
   * Obtenir le nom d'affichage de l'utilisateur (prénom + nom)
   */
  get userDisplayName(): string {
    if (!this.currentUser) {
      return 'Utilisateur';
    }
    
    // Si on a un nom complet, l'utiliser
    if (this.currentUser.nom) {
      return this.currentUser.nom;
    }
    
    // Sinon construire avec prénom + nom
    const prenom = this.currentUser.prenom || '';
    const nom = this.currentUser.nom || '';
    
    if (prenom && nom) {
      return `${prenom} ${nom}`;
    } else if (prenom) {
      return prenom;
    } else if (nom) {
      return nom;
    }
    
    // En dernier recours, utiliser l'email
    return this.currentUser.email || 'Utilisateur';
  }

  /**
   * Obtenir les initiales pour l'avatar
   */
  get userInitials(): string {
    if (!this.currentUser) {
      return 'U';
    }

    const prenom = this.currentUser.prenom || '';
    const nom = this.currentUser.nom || '';
    
    if (prenom && nom) {
      return `${prenom.charAt(0)}${nom.charAt(0)}`.toUpperCase();
    } else if (this.currentUser.nom) {
      const parts = this.currentUser.nom.split(' ');
      if (parts.length >= 2) {
        return `${parts[0].charAt(0)}${parts[1].charAt(0)}`.toUpperCase();
      } else {
        return parts[0].charAt(0).toUpperCase();
      }
    }
    
    return this.currentUser.email?.charAt(0).toUpperCase() || 'U';
  }
}