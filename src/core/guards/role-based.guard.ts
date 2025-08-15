import { CanActivateFn } from '@angular/router';

export const roleBasedGuard: CanActivateFn = (route, state) => {
  return true;
};
