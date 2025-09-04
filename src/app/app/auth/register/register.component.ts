import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule, AbstractControl } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/auth.service';
import { HttpErrorResponse } from '@angular/common/http';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule, RouterLink],
  templateUrl: './register.component.html',
  styleUrl: './register.component.scss'
})
export class RegisterComponent implements OnInit {
  registerForm!: FormGroup;
  message: string = '';
  isLoading: boolean = false;

  constructor(
    private formBuilder: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.initializeForm();
  }

  private initializeForm(): void {
    this.registerForm = this.formBuilder.group({
      nom: ['', [Validators.required, Validators.minLength(2)]],
      prenom: ['', [Validators.required, Validators.minLength(2)]],
      email: ['', [Validators.required, Validators.email]],
      telephone: ['', [Validators.required, Validators.pattern(/^[\+]?[0-9\s\-\(\)]{8,}$/)]],
      adresse: ['', [Validators.required, Validators.minLength(5)]],
      ville: ['', [Validators.required, Validators.minLength(2)]],
      password: ['', [Validators.required, Validators.minLength(6)]],
      password_confirmation: ['', [Validators.required]]
    }, {
      validators: this.passwordMatchValidator
    });
  }

  private passwordMatchValidator(control: AbstractControl): { [key: string]: boolean } | null {
    const password = control.get('password');
    const confirmPassword = control.get('password_confirmation');
    if (!password || !confirmPassword) return null;
    return password.value !== confirmPassword.value ? { 'passwordMismatch': true } : null;
  }

  register(): void {
    this.message = '';
    if (!this.registerForm.valid) {
      this.message = 'Veuillez corriger les erreurs dans le formulaire';
      this.markFormGroupTouched();
      return;
    }

    this.isLoading = true;
    const formData = { ...this.registerForm.value };
    
    // Supprimer password_confirmation avant envoi
    delete formData.password_confirmation;

    this.authService.register(formData).subscribe({
      next: (response) => {
        this.isLoading = false;
        this.message = 'Inscription réussie ! Redirection en cours...';
        setTimeout(() => this.router.navigate(['/login']), 1500);
      },
      error: (error: HttpErrorResponse) => {
        this.isLoading = false;
        this.handleRegistrationError(error);
      }
    });
  }

  private handleRegistrationError(error: HttpErrorResponse): void {
    switch (error.status) {
      case 422:
        if (error.error?.errors) {
          const errors = error.error.errors;
          const messages = Object.entries(errors).map(([field, msgs]: [string, any]) =>
            `${field}: ${Array.isArray(msgs) ? msgs.join(', ') : msgs}`
          );
          this.message = messages.join(' | ');
        } else {
          this.message = error.error?.message || 'Erreur de validation des données';
        }
        break;
      case 409:
        this.message = 'Cette adresse email est déjà utilisée';
        break;
      case 0:
        this.message = 'Impossible de contacter le serveur. Vérifiez votre connexion.';
        break;
      default:
        this.message = error.error?.message || `Erreur ${error.status}: ${error.statusText}`;
    }
  }

  private markFormGroupTouched(): void {
    Object.keys(this.registerForm.controls).forEach(key => {
      const control = this.registerForm.get(key);
      control?.markAsTouched();
    });
  }

  isFieldInvalid(fieldName: string): boolean {
    const field = this.registerForm.get(fieldName);
    return !!(field && field.invalid && field.touched);
  }

  getFieldError(fieldName: string): string {
    const field = this.registerForm.get(fieldName);
    if (field && field.errors && field.touched) {
      const errors = field.errors;
      if (errors['required']) return `Le ${fieldName} est requis`;
      if (errors['email']) return 'Format d\'email invalide';
      if (errors['minlength']) return `Minimum ${errors['minlength'].requiredLength} caractères`;
      if (errors['pattern'] && fieldName === 'telephone') return 'Format de téléphone invalide';
    }
    if (fieldName === 'password_confirmation' && this.registerForm.errors?.['passwordMismatch']) {
      return 'Les mots de passe ne correspondent pas';
    }
    return '';
  }

  login(): void {
    this.router.navigate(['/login']);
  }
}
