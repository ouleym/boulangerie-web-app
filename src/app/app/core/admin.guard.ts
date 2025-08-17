import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { map, take } from 'rxjs/operators';
import { AuthService } from './auth.service';

export const adminGuard: CanActivateFn = (_route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  return authService.currentUser$.pipe(
    take(1),
    map(user => {
      if (user && authService.isAdmin()) {
        return true;
      } else {
        // Rediriger selon le statut d'authentification
        if (user) {
          // Utilisateur connecté mais pas admin
          router.navigate(['/dashboard']);
        } else {
          // Utilisateur non connecté
          router.navigate(['/login'], {
            queryParams: { returnUrl: state.url }
          });
        }
        return false;
      }
    })
  );
};