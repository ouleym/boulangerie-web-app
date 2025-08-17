import { HttpInterceptorFn } from '@angular/common/http';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  // Vérifier si c'est une requête vers votre API Laravel
  const isApiRequest = req.url.includes('localhost:8000') || req.url.includes('/api/') || req.url.includes('/sanctum/');
  
  if (isApiRequest) {
    // Clone la requête avec les configurations Sanctum
    const authReq = req.clone({
      withCredentials: true,
      setHeaders: {
        'Accept': 'application/json',
        // Ajouter Content-Type seulement pour les requêtes POST/PUT/PATCH
        ...(req.method !== 'GET' && req.method !== 'DELETE' && {
          'Content-Type': 'application/json'
        })
      }
    });

    return next(authReq);
  }

  // Pour les autres requêtes, passer sans modification
  return next(req);
};