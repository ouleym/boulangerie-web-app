import { TestBed } from '@angular/core/testing';
import { CanActivateFn } from '@angular/router';

import { adminGerantGuard } from './admin-gerant.guard';

describe('adminGerantGuard', () => {
  const executeGuard: CanActivateFn = (...guardParameters) => 
      TestBed.runInInjectionContext(() => adminGerantGuard(...guardParameters));

  beforeEach(() => {
    TestBed.configureTestingModule({});
  });

  it('should be created', () => {
    expect(executeGuard).toBeTruthy();
  });
});
