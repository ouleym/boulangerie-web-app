import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from './auth.service';

export const authGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

<<<<<<< HEAD
  const rawUrl = state.url || '';
  const url = rawUrl.split('?')[0].split('#')[0]; // normaliser

  const publicRoutes = new Set(['/login', '/register', '/forgot-password']);

  console.log('[authGuard] url=', url, 'isAuthenticated=', authService.isAuthenticated(), 'redirectUrl=', authService.redirectUrl);

  // laisser passer les routes publiques
  if (publicRoutes.has(url)) {
    console.log('[authGuard] route publique — accès autorisé');
    return true;
  }

  if (authService.isAuthenticated()) {
    console.log('[authGuard] utilisateur authentifié — accès autorisé');
    return true;
  }

  console.log('[authGuard] utilisateur NON authentifié — redirection vers', authService.redirectUrl || '/login');
  router.navigateByUrl(authService.redirectUrl || '/login', { replaceUrl: true });
=======
  if(authService.isAuthenticated() ){
    return true;
  }
   router.navigate([authService.redirectUrl]);
>>>>>>> 4ae0300 (Initial commit de mon projet frontend)
  return false;
};
