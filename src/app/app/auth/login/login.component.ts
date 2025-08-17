import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { Subject, takeUntil } from 'rxjs';
import { AuthService } from '../../core/auth.service';
import { LoginCredentials } from '../../models/user.model';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit, OnDestroy {
  loginForm!: FormGroup;
  isLoading = false;
  showPassword = false;
  returnUrl = '';
  private destroy$ = new Subject<void>();

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router,
    private route: ActivatedRoute
  ) {
    this.initForm();
  }

  ngOnInit(): void {
    // Récupérer l'URL de retour si elle existe
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/dashboard';
    
    // Si l'utilisateur est déjà connecté, rediriger
    this.authService.isAuthenticated$
      .pipe(takeUntil(this.destroy$))
      .subscribe(isAuthenticated => {
        if (isAuthenticated) {
          this.redirectAfterLogin();
        }
      });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private initForm(): void {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]],
      remember: [false]
    });
  }

  onSubmit(): void {
    if (this.loginForm.valid && !this.isLoading) {
      this.isLoading = true;
      
      const credentials: LoginCredentials = {
        email: this.loginForm.get('email')?.value,
        password: this.loginForm.get('password')?.value,
        remember: this.loginForm.get('remember')?.value
      };

      this.authService.login(credentials)
        .pipe(takeUntil(this.destroy$))
        .subscribe({
          next: (response) => {
            console.log('Login successful:', response);
            this.isLoading = false;
            this.redirectAfterLogin();
          },
          error: (error) => {
            console.error('Login error:', error);
            this.isLoading = false;
            
            // Si erreur 419, on peut essayer de relancer une fois
            if (error.status === 419) {
              console.log('CSRF token expired, could retry...');
            }
          }
        });
    } else {
      this.markFormGroupTouched();
    }
  }

  private redirectAfterLogin(): void {
    const user = this.authService.currentUser;
    
    if (user) {
      // Redirection intelligente selon le rôle
      if (this.authService.isAdmin()) {
        this.router.navigate(['/dashboard/admin']);
      } else if (this.authService.isEmployee()) {
        this.router.navigate(['/dashboard/employe']);
      } else {
        this.router.navigate([this.returnUrl || '/dashboard/client']);
      }
    }
  }

  togglePasswordVisibility(): void {
    this.showPassword = !this.showPassword;
  }

  navigateToRegister(): void {
    this.router.navigate(['/register'], {
      queryParams: this.returnUrl ? { returnUrl: this.returnUrl } : {}
    });
  }

  private markFormGroupTouched(): void {
    Object.keys(this.loginForm.controls).forEach(key => {
      const control = this.loginForm.get(key);
      control?.markAsTouched();
    });
  }

  // Getters pour faciliter l'accès aux contrôles dans le template
  get email() { return this.loginForm.get('email'); }
  get password() { return this.loginForm.get('password'); }
  get remember() { return this.loginForm.get('remember'); }

  // Méthodes utilitaires pour les erreurs
  getEmailError(): string {
    if (this.email?.hasError('required')) {
      return 'L\'email est obligatoire';
    }
    if (this.email?.hasError('email')) {
      return 'Format d\'email invalide';
    }
    return '';
  }

  getPasswordError(): string {
    if (this.password?.hasError('required')) {
      return 'Le mot de passe est obligatoire';
    }
    if (this.password?.hasError('minlength')) {
      return 'Le mot de passe doit contenir au moins 6 caractères';
    }
    return '';
  }
}