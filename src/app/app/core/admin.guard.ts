import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { map, take, switchMap } from 'rxjs/operators';
import { of } from 'rxjs';
import { AuthService } from './auth.service';

export const adminGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  return authService.currentUser$.pipe(
    take(1),
    switchMap(user => {
      if (!user) {
        // Utilisateur non connecté
        console.log('🔒 Admin Guard: Utilisateur non connecté');
        router.navigate(['/login'], {
          queryParams: { returnUrl: state.url }
        });
        return of(false);
      }

      // Vérifier les permissions admin
      const isAdmin = authService.isAdmin();
      
      if (isAdmin) {
        // Valider le token côté serveur pour les admins
        return authService.validateToken().pipe(
          map(isValid => {
            if (!isValid) {
              console.log('🔒 Admin Guard: Token invalide');
              router.navigate(['/login'], { 
                queryParams: { returnUrl: state.url, reason: 'token_expired' } 
              });
              return false;
            }
            return true;
          })
        );
      } else {
        // Utilisateur connecté mais pas admin
        console.log('🔒 Admin Guard: Accès refusé - pas admin');
        console.log('Rôles utilisateur:', authService.getUserRoles());
        
        // Rediriger vers la page appropriée selon le rôle
        if (authService.isEmployee()) {
          router.navigate(['/dashboard/employe']);
        } else if (authService.isClient()) {
          router.navigate(['/dashboard/client']);
        } else {
          router.navigate(['/dashboard']);
        }
        
        return of(false);
      }
    })
  );
};