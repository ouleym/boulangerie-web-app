import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule, AbstractControl } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { AuthService } from '../../core/auth.service';
import { HttpErrorResponse } from '@angular/common/http';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule],
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
    
    if (!password || !confirmPassword) {
      return null;
    }
    
    return password.value !== confirmPassword.value ? { 'passwordMismatch': true } : null;
  }

  register(): void {
    // Réinitialiser le message d'erreur
    this.message = '';
    
    if (this.registerForm.valid) {
      this.isLoading = true;
      
      // Préparer les données à envoyer
      const formData = { ...this.registerForm.value };
      
      // S'assurer que password_confirmation est présent
      if (!formData.password_confirmation) {
        formData.password_confirmation = formData.password;
      }

      console.log('Données envoyées:', formData); // Debug
  
      this.authService.register(formData).subscribe({
        next: (response) => {
          this.isLoading = false;
          console.log('Inscription réussie:', response);
          this.message = 'Inscription réussie ! Redirection en cours...';
          
          // Redirection après un court délai pour que l'utilisateur voie le message
          setTimeout(() => {
            this.router.navigate(['/login']);
          }, 1500);
        },
        error: (error: HttpErrorResponse) => {
          this.isLoading = false;
          console.error('Erreur complète:', error); // Debug complet
          this.handleRegistrationError(error);
        }
      });
    } else {
      this.message = 'Veuillez corriger les erreurs dans le formulaire';
      this.markFormGroupTouched();
    }
  }

  private handleRegistrationError(error: HttpErrorResponse): void {
    console.log('Status:', error.status);
    console.log('Error body:', error.error);
    
    switch (error.status) {
      case 422:
        // Erreurs de validation Laravel
        if (error.error?.errors) {
          const errors = error.error.errors;
          const errorMessages = Object.entries(errors)
            .map(([field, messages]: [string, any]) => {
              const fieldMessages = Array.isArray(messages) ? messages : [messages];
              return `${field}: ${fieldMessages.join(', ')}`;
            });
          this.message = errorMessages.join(' | ');
        } else if (error.error?.message) {
          this.message = error.error.message;
        } else {
          this.message = 'Erreur de validation des données';
        }
        break;
        
      case 409:
        this.message = 'Cette adresse email est déjà utilisée';
        break;
        
      case 500:
        this.message = 'Erreur serveur. Veuillez réessayer plus tard.';
        break;
        
      case 0:
        this.message = 'Impossible de contacter le serveur. Vérifiez votre connexion internet.';
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

  // Méthodes utilitaires pour les validations
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