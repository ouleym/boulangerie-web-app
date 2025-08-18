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
  loginAttempts = 0;
  maxAttempts = 3;
  errorMessage = '';
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
    // Récupérer l'URL de retour et la raison de redirection
    const queryParams = this.route.snapshot.queryParams;
    this.returnUrl = queryParams['returnUrl'] || '/dashboard';
    
    // Afficher un message selon la raison de la redirection
    if (queryParams['reason']) {
      this.handleRedirectionReason(queryParams['reason']);
    }
    
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

  private handleRedirectionReason(reason: string): void {
    switch (reason) {
      case 'session_expired':
        this.errorMessage = 'Votre session a expiré. Veuillez vous reconnecter.';
        break;
      case 'token_expired':
        this.errorMessage = 'Votre authentification a expiré. Veuillez vous reconnecter.';
        break;
      case 'access_denied':
        this.errorMessage = 'Accès refusé. Veuillez vous connecter avec un compte autorisé.';
        break;
      default:
        this.errorMessage = '';
    }
  }

  onSubmit(): void {
    if (this.loginForm.valid && !this.isLoading && this.loginAttempts < this.maxAttempts) {
      this.isLoading = true;
      this.errorMessage = '';
      this.loginAttempts++;
      
      const credentials: LoginCredentials = {
        email: this.loginForm.get('email')?.value,
        password: this.loginForm.get('password')?.value,
        remember: this.loginForm.get('remember')?.value
      };

      this.authService.login(credentials)
        .pipe(takeUntil(this.destroy$))
        .subscribe({
          next: (response) => {
            console.log('✅ Login successful:', response);
            this.isLoading = false;
            this.loginAttempts = 0; // Reset attempts on success
            this.redirectAfterLogin();
          },
          error: (error) => {
            console.error('❌ Login error:', error);
            this.isLoading = false;
            
            this.handleLoginError(error);
          }
        });
    } else {
      if (this.loginAttempts >= this.maxAttempts) {
        this.errorMessage = `Trop de tentatives de connexion. Attendez quelques minutes.`;
        // Optionnel: implémenter un timer de cooldown
        setTimeout(() => {
          this.loginAttempts = 0;
          this.errorMessage = '';
        }, 300000); // 5 minutes
      } else {
        this.markFormGroupTouched();
      }
    }
  }

  private handleLoginError(error: any): void {
    if (error.status === 419) {
      this.errorMessage = 'Erreur de sécurité. Rechargement de la page...';
      // Attendre 2 secondes puis recharger la page pour récupérer un nouveau token CSRF
      setTimeout(() => {
        window.location.reload();
      }, 2000);
    } else if (error.status === 422) {
      this.errorMessage = 'Identifiants incorrects. Vérifiez votre email et mot de passe.';
    } else if (error.status === 429) {
      this.errorMessage = 'Trop de tentatives. Veuillez patienter avant de réessayer.';
    } else if (error.status === 0) {
      this.errorMessage = 'Impossible de contacter le serveur. Vérifiez votre connexion.';
    } else {
      this.errorMessage = error.error?.message || 'Une erreur inattendue s\'est produite.';
    }
  }

  private redirectAfterLogin(): void {
    const user = this.authService.currentUser;
    
    if (user) {
      console.log('👤 Redirection pour:', user);
      console.log('🔐 Rôles:', this.authService.getUserRoles());
      
      // Redirection intelligente selon le rôle
      if (this.authService.isAdmin()) {
        this.router.navigate(['/dashboard/admin']);
      } else if (this.authService.isEmployee()) {
        this.router.navigate(['/dashboard/employe']);
      } else {
        // Pour les clients, respecter l'URL de retour ou aller au dashboard client
        if (this.returnUrl && this.returnUrl !== '/dashboard') {
          this.router.navigate([this.returnUrl]);
        } else {
          this.router.navigate(['/dashboard/client']);
        }
      }
    }
  }

  togglePasswordVisibility(): void {
    this.showPassword = !this.showPassword;
  }

  navigateToRegister(): void {
    this.router.navigate(['/register'], {
      queryParams: this.returnUrl !== '/dashboard' ? { returnUrl: this.returnUrl } : {}
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

  // Méthodes utilitaires pour le template
  get canAttemptLogin(): boolean {
    return this.loginAttempts < this.maxAttempts;
  }

  get attemptsRemaining(): number {
    return Math.max(0, this.maxAttempts - this.loginAttempts);
  }
}