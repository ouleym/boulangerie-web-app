import { CanActivateFn } from '@angular/router';

export const adminGerantGuard: CanActivateFn = (route, state) => {
  return true;
};
