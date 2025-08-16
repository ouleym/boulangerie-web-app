import { HttpInterceptorFn } from '@angular/common/http';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  // Clone la requête et ajoute withCredentials
  const authReq = req.clone({
    withCredentials: true
  });

  return next(authReq);
};
