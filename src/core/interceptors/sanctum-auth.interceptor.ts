import { HttpInterceptorFn } from '@angular/common/http';

export const sanctumAuthInterceptor: HttpInterceptorFn = (req, next) => {
  return next(req);
};
