import { Component } from '@angular/core';
import { Router, NavigationEnd, RouterOutlet } from '@angular/router';
import { AuthService } from './app/core/auth.service';
import { NavbarComponent } from './app/shared/navbar/navbar.component';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, NavbarComponent,NgIf],
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent {
  isLoginPage = false;
  isLoggedIn = false;

  constructor(private router: Router, private authService: AuthService) {
    // Surveille la navigation pour détecter si on est sur la page de login
    this.router.events.subscribe(event => {
      if (event instanceof NavigationEnd) {
        this.isLoginPage = event.urlAfterRedirects === '/login';
        this.isLoggedIn = this.authService.isAuthenticated(); // ✅ vérifier login
      }
    });
  }
}
