import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule, AbstractControl } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { Subject, takeUntil } from 'rxjs';
import { AuthService } from '../../core/auth.service';
import { RegisterData } from '../../models/user.model';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.scss']
})
export class RegisterComponent implements OnInit, OnDestroy {
  registerForm!: FormGroup;
  isLoading = false;
  showPassword = false;
  showPasswordConfirm = false;
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
          this.redirectAfterRegister();
        }
      });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private initForm(): void {
    this.registerForm = this.fb.group({
      nom: ['', [Validators.required, Validators.minLength(2)]],
      prenom: ['', [Validators.required, Validators.minLength(2)]],
      email: ['', [Validators.required, Validators.email]],
      telephone: ['', [Validators.pattern(/^[0-9+\-\s]+$/)]],
      adresse: [''],
      ville: [''],
      password: ['', [Validators.required, Validators.minLength(6)]],
      password_confirmation: ['', [Validators.required]]
    }, { validators: this.passwordMatchValidator });
  }

  // Validateur personnalisé pour vérifier que les mots de passe correspondent
  private passwordMatchValidator(control: AbstractControl): { [key: string]: any } | null {
    const password = control.get('password');
    const passwordConfirm = control.get('password_confirmation');
    
    if (password && passwordConfirm && password.value !== passwordConfirm.value) {
      return { passwordMismatch: true };
    }
    return null;
  }

  onSubmit(): void {
    if (this.registerForm.valid && !this.isLoading) {
      this.isLoading = true;
      
      const registerData: RegisterData = {
        nom: this.registerForm.get('nom')?.value,
        prenom: this.registerForm.get('prenom')?.value,
        email: this.registerForm.get('email')?.value,
        telephone: this.registerForm.get('telephone')?.value || undefined,
        adresse: this.registerForm.get('adresse')?.value || undefined,
        ville: this.registerForm.get('ville')?.value || undefined,
        password: this.registerForm.get('password')?.value,
        password_confirmation: this.registerForm.get('password_confirmation')?.value
      };

      this.authService.register(registerData)
        .pipe(takeUntil(this.destroy$))
        .subscribe({
          next: (_response) => {
            this.isLoading = false;
            this.redirectAfterRegister();
          },
          error: (_error) => {
            this.isLoading = false;
            // L'erreur est gérée par le service via NotificationService
          }
        });
    } else {
      this.markFormGroupTouched();
    }
  }

  private redirectAfterRegister(): void {
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

  togglePasswordConfirmVisibility(): void {
    this.showPasswordConfirm = !this.showPasswordConfirm;
  }

  navigateToLogin(): void {
    this.router.navigate(['/login'], {
      queryParams: this.returnUrl ? { returnUrl: this.returnUrl } : {}
    });
  }

  private markFormGroupTouched(): void {
    Object.keys(this.registerForm.controls).forEach(key => {
      const control = this.registerForm.get(key);
      control?.markAsTouched();
    });
  }

  // Getters pour faciliter l'accès aux contrôles dans le template
  get nom() { return this.registerForm.get('nom'); }
  get prenom() { return this.registerForm.get('prenom'); }
  get email() { return this.registerForm.get('email'); }
  get telephone() { return this.registerForm.get('telephone'); }
  get adresse() { return this.registerForm.get('adresse'); }
  get ville() { return this.registerForm.get('ville'); }
  get password() { return this.registerForm.get('password'); }
  get passwordConfirmation() { return this.registerForm.get('password_confirmation'); }

  // Méthodes utilitaires pour les erreurs
  getNomError(): string {
    if (this.nom?.hasError('required')) {
      return 'Le nom est obligatoire';
    }
    if (this.nom?.hasError('minlength')) {
      return 'Le nom doit contenir au moins 2 caractères';
    }
    return '';
  }

  getPrenomError(): string {
    if (this.prenom?.hasError('required')) {
      return 'Le prénom est obligatoire';
    }
    if (this.prenom?.hasError('minlength')) {
      return 'Le prénom doit contenir au moins 2 caractères';
    }
    return '';
  }

  getEmailError(): string {
    if (this.email?.hasError('required')) {
      return 'L\'email est obligatoire';
    }
    if (this.email?.hasError('email')) {
      return 'Format d\'email invalide';
    }
    return '';
  }

  getTelephoneError(): string {
    if (this.telephone?.hasError('pattern')) {
      return 'Format de téléphone invalide';
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

  getPasswordConfirmationError(): string {
    if (this.passwordConfirmation?.hasError('required')) {
      return 'La confirmation du mot de passe est obligatoire';
    }
    if (this.registerForm.hasError('passwordMismatch') && this.passwordConfirmation?.touched) {
      return 'Les mots de passe ne correspondent pas';
    }
    return '';
  }
}