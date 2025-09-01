import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule, AbstractControl } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { AuthService } from '../../core/auth.service';

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
    if (this.registerForm.valid) {
      const formData = this.registerForm.value;
      
      // Suppression de password_confirmation avant envoi
      delete formData.password_confirmation;

      this.authService.register(formData).subscribe({
        next: (response) => {
          console.log('Inscription réussie:', response);
          // Redirection vers le dashboard ou page de confirmation
          this.router.navigate(['/dashboard']);
        },
        error: (error) => {
          console.error('Erreur d\'inscription:', error);
          
          if (error.status === 422) {
            // Erreurs de validation Laravel
            const errors = error.error.errors || error.error.message;
            if (typeof errors === 'object') {
              const errorMessages = Object.values(errors).flat();
              this.message = errorMessages.join(', ');
            } else {
              this.message = errors || 'Erreur de validation des données';
            }
          } else if (error.status === 409) {
            this.message = 'Cette adresse email est déjà utilisée';
          } else if (error.status === 0) {
            this.message = 'Impossible de contacter le serveur. Vérifiez votre connexion.';
          } else {
            this.message = error.error?.message || 'Une erreur est survenue lors de l\'inscription';
          }
        }
      });
    } else {
      this.message = 'Veuillez corriger les erreurs dans le formulaire';
      this.markFormGroupTouched();
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
}