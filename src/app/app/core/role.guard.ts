import { inject } from '@angular/core';
import { CanActivateFn, Router, ActivatedRouteSnapshot } from '@angular/router';
import { AuthService } from './auth.service';

export const roleGuard: CanActivateFn = (route: ActivatedRouteSnapshot) => {
  const authService = inject(AuthService);
  const router = inject(Router);
  
  const expectedRoles = route.data['roles'] as string[];
  
  if (!authService.isAuthenticated()) {
    console.log('Utilisateur non authentifié, redirection vers login');
    router.navigate(['/login']);
    return false;
  }
  
  if (!expectedRoles || expectedRoles.length === 0) {
    return true;
  }
  
  const userRoles = authService.getUserRoles();
  const hasAccess = expectedRoles.some(role => userRoles.includes(role));
  
  console.log('Vérification des rôles:', {
    expectedRoles,
    userRoles,
    hasAccess
  });
  
  if (!hasAccess) {
    console.log('Accès refusé. Redirection vers le dashboard approprié.');
    
    // ✅ CORRECTION : Utiliser les bons noms de rôles avec majuscule
    if (userRoles.includes('Admin')) {
      router.navigate(['/dashboard/admin']);
    } else if (userRoles.includes('Employe')) {
      router.navigate(['/dashboard/employe']);
    } else if (userRoles.includes('Client')) {
      router.navigate(['/dashboard/client']);
    } else {
      router.navigate(['/login']);
    }
    
    return false;
  }
  
  return true;
};