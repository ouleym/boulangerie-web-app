import { ApplicationConfig, provideZoneChangeDetection } from '@angular/core';
import { provideRouter } from '@angular/router';
import { provideHttpClient, withInterceptors, withFetch } from '@angular/common/http';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';
import { provideToastr } from 'ngx-toastr';
import { routes } from './app.routes';
import { authInterceptor } from './app/core/auth.interceptor';
import { errorInterceptor } from './app/core/error.interceptor';

export const appConfig: ApplicationConfig = {
  providers: [
    // Configuration de base Angular
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideRouter(routes),
    
    // Configuration HTTP avec interceptors pour Sanctum
    provideHttpClient(
      withFetch(), // Utilise fetch API au lieu de XMLHttpRequest
      withInterceptors([authInterceptor, errorInterceptor]) 
    ),
    
    // Animations pour Angular Material
    provideAnimationsAsync(),
    
    // Configuration Toast notifications
    provideToastr({
      timeOut: 3000,
      positionClass: 'toast-top-right',
      preventDuplicates: true,
      closeButton: true,
      progressBar: true,
      tapToDismiss: true,
      extendedTimeOut: 1000
    })
  ]
};