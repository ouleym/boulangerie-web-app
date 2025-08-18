import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const router = inject(Router);
  
  // Vérifier si c'est une requête vers l'API Laravel
  const isApiRequest = req.url.includes('localhost:8000') || 
                       req.url.includes('/api/') || 
                       req.url.includes('/sanctum/');
  
  if (isApiRequest) {
    // Configuration spécifique pour Sanctum
    const authReq = req.clone({
      withCredentials: true, // CRUCIAL pour Sanctum
      setHeaders: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest', // Header SPA standard
        // Ajouter Content-Type seulement pour les requêtes avec body
        ...(req.method !== 'GET' && req.method !== 'DELETE' && req.method !== 'HEAD' && {
          'Content-Type': 'application/json'
        })
      }
    });

    return next(authReq).pipe(
      catchError((error: HttpErrorResponse) => {
        // Gestion centralisée des erreurs d'authentification
        if (error.status === 401) {
          console.log('🔒 Erreur 401: Non authentifié');
          // Rediriger vers login en cas d'erreur 401
          router.navigate(['/login'], {
            queryParams: { reason: 'session_expired' }
          });
        } else if (error.status === 403) {
          console.log('🔒 Erreur 403: Accès interdit');
          // Rediriger vers page d'accès refusé
          router.navigate(['/access-denied']);
        } else if (error.status === 419) {
          console.log('🔒 Erreur 419: Token CSRF expiré');
          // Le service AuthService va gérer automatiquement le refresh du token CSRF
        } else if (error.status === 0) {
          console.log('🔒 Erreur réseau: Serveur inaccessible');
        }
        
        return throwError(() => error);
      })
    );
  }

  // Pour les autres requêtes, passer sans modification
  return next(req);
};