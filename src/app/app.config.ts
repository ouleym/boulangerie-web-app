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
    // Angular core
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideRouter(routes),

    // HTTP client avec cookies + interceptors
    provideHttpClient(
      withFetch(),
      withInterceptors([authInterceptor, errorInterceptor])
    ),

    // Force Angular à envoyer les cookies cross-domain
    {
      provide: 'withCredentials',
      useValue: true
    },

    // Animations Material
    provideAnimationsAsync(),

    // Toastr config
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
