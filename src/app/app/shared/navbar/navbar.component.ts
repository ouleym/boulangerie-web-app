import { Component, OnInit, OnDestroy } from '@angular/core';
import { Subscription } from 'rxjs';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../core/auth.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss']
})
export class NavbarComponent implements OnInit, OnDestroy {
  isLoggedIn = false;
  userRoles: string[] = [];
  panierCount = 0;
  brandTitle = 'Boulangerie Hi-Tech';
  dashboardRoute = '/dashboard';
  userInitials = '';
  userDisplayName = '';
  userRoleDisplay = '';
  
  private subscription: Subscription = new Subscription();

  constructor(private authService: AuthService, private router: Router) {}

  ngOnInit() {
    this.subscription = this.authService.loggedIn$.subscribe(status => {
      this.isLoggedIn = status;
      
      if (status) {
        this.loadUserData();
      } else {
        this.clearUserData();
      }
    });
  }

  ngOnDestroy() {
    this.subscription.unsubscribe();
  }

  private loadUserData(): void {
    this.userRoles = this.authService.getUserRoles();
    const decoded = this.authService.getDecodedToken();
    
    console.log('Rôles de l\'utilisateur:', this.userRoles); // Debug
    
    if (decoded) {
      this.userDisplayName = decoded.nom || decoded.email || 'Utilisateur';
      this.userInitials = this.generateInitials(this.userDisplayName);
      this.userRoleDisplay = this.formatRoles(this.userRoles);
      this.setDashboardRoute();
    }
  }

  private clearUserData(): void {
    this.userRoles = [];
    this.userDisplayName = '';
    this.userInitials = '';
    this.userRoleDisplay = '';
    this.dashboardRoute = '/dashboard';
  }

  private generateInitials(name: string): string {
    return name
      .split(' ')
      .map(n => n[0])
      .join('')
      .toUpperCase()
      .substring(0, 2);
  }

  private formatRoles(roles: string[]): string {
    const roleLabels: { [key: string]: string } = {
      'Admin': 'Administrateur',
      'Employe': 'Employé',
      'Client': 'Client',
      'admin': 'Administrateur',
      'employe': 'Employé',
      'client': 'Client'
    };
    
    return roles
      .map(role => roleLabels[role] || role)
      .join(', ');
  }

  private setDashboardRoute(): void {
    if (this.userRoles.includes('Admin')) {
      this.dashboardRoute = '/dashboard/admin';
    } else if (this.userRoles.includes('Employe')) {
      this.dashboardRoute = '/dashboard/employe';
    } else if (this.userRoles.includes('Client')) {
      this.dashboardRoute = '/dashboard/client';
    } else {
      this.dashboardRoute = '/dashboard/client'; // Par défaut
    }
  }

  // Getters pour les rôles
  get isClient(): boolean {
    return this.userRoles.includes('Client');
  }

  get isEmployeOrAdmin(): boolean {
    return this.userRoles.includes('Employe') || this.userRoles.includes('Admin');
  }

  get isAdmin(): boolean {
    return this.userRoles.includes('Admin');
  }

  logout() {
    this.authService.logout();
    this.router.navigate(['/login']);
  }
}
