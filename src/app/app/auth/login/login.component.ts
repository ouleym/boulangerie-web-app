import { Component } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../core/auth.service';
import { Router, RouterLink } from '@angular/router';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink], 
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'] 
})
export class LoginComponent {
  message: string = '';
  isLoading: boolean = false;

  loginForm = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required, Validators.minLength(6)])
  });

  constructor(private authService: AuthService, private router: Router) {}

  login() {
    if (this.loginForm.invalid) {
      this.message = 'Veuillez remplir tous les champs correctement.';
      return;
    }

    this.isLoading = true;
    this.message = '';

    this.authService.login(this.loginForm.value).subscribe({
      next: (response) => {
        this.isLoading = false;

        if (response.status === 200 && response.token) {
          console.log('Connexion réussie');

          this.redirectToDashboard();
        } else {
          this.message = 'Email ou mot de passe incorrect';
        }
      },
      error: (error) => {
        this.isLoading = false;
        this.message = 'Erreur de connexion. Veuillez réessayer.';
        console.error('Erreur de login:', error);
      }
    });
  }

  /**
   * Redirige l'utilisateur vers le bon dashboard selon son rôle
   */
  private redirectToDashboard(): void {
    const userRoles = this.authService.getUserRoles().map((r: string) => r.toLowerCase());

    if (userRoles.includes('admin')) {
      this.router.navigate(['/dashboard/admin']);
    } else if (userRoles.includes('employe')) {
      this.router.navigate(['/dashboard/employe']);
    } else if (userRoles.includes('client')) {
      this.router.navigate(['/dashboard/client']);
    } else {
      console.warn('Aucun rôle reconnu, redirection vers client par défaut');
      this.router.navigate(['/dashboard/client']);
    }
  }
}
