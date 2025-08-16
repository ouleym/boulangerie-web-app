import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { map, take } from 'rxjs/operators';
import { AuthService } from './auth.service';

export const adminGuard: CanActivateFn = (_route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  return AuthService.currentUser$.pipe(
    take(1),
    map(user => {
      // Vérifier d'abord si l'utilisateur est connecté
      if (!user || !authService.isAuthenticated) {
        router.navigate(['/login'], { 
          queryParams: { returnUrl: state.url } 
        });
        return false;
      }

      // Vérifier si l'utilisateur a les droits d'administration
      if (authService.isAdmin()) {
        return true;
      } else {
        // Rediriger vers le dashboard client si pas admin
        router.navigate(['/dashboard/client']);
        return false;
      }
    })
  );
};