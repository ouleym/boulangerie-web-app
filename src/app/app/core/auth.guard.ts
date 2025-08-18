import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { map, take, tap, switchMap } from 'rxjs/operators';
import { of } from 'rxjs';
import { AuthService } from './auth.service';

export const authGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  return authService.isAuthenticated$.pipe(
    take(1),
    switchMap(isAuthenticated => {
      if (isAuthenticated) {
        // Utilisateur authentifié côté client, vérifier côté serveur
        return authService.validateToken().pipe(
          tap(isValid => {
            if (!isValid) {
              console.log('🔒 Token invalide, redirection vers login');
              router.navigate(['/login'], { 
                queryParams: { returnUrl: state.url, reason: 'token_expired' } 
              });
            }
          })
        );
      } else {
        // Utilisateur non authentifié
        console.log('🔒 Utilisateur non authentifié, redirection vers login');
        router.navigate(['/login'], { 
          queryParams: { returnUrl: state.url } 
        });
        return of(false);
      }
    }),
    map(isValid => {
      if (typeof isValid === 'boolean') {
        return isValid;
      }
      return true; // Si on arrive ici, l'utilisateur est authentifié
    })
  );
};