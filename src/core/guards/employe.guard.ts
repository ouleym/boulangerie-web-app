import { CanActivateFn } from '@angular/router';

export const employeGuard: CanActivateFn = (route, state) => {
  return true;
};
