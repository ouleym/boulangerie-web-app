import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { AuthService } from '../../core/auth.service';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './navbar.component.html',
  styleUrl: './navbar.component.scss'
})
export class NavbarComponent implements OnInit {
  private authService = inject(AuthService);
  private router = inject(Router);

  isAuthenticated$: Observable<boolean> = this.authService.isAuthenticated$;
  currentUser$ = this.authService.currentUser$;
  isAdmin$ = this.authService.isAdmin$;
  
  isMenuOpen = false;
  isDropdownOpen = false;

  ngOnInit() {
    // Auto-fermer le menu mobile lors du changement de route
    this.router.events.subscribe(() => {
      this.isMenuOpen = false;
      this.isDropdownOpen = false;
    });
  }

  toggleMenu() {
    this.isMenuOpen = !this.isMenuOpen;
  }

  toggleDropdown() {
    this.isDropdownOpen = !this.isDropdownOpen;
  }

  logout() {
    this.authService.logout().subscribe({
      next: () => {
        this.router.navigate(['/login']);
      },
      error: (error: any) => {
        console.error('Erreur de déconnexion:', error);
      }
    });
  }

  closeMenu() {
    this.isMenuOpen = false;
    this.isDropdownOpen = false;
  }
}