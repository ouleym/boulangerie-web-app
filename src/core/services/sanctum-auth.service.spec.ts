import { TestBed } from '@angular/core/testing';

import { SanctumAuthService } from './sanctum-auth.service';

describe('SanctumAuthService', () => {
  let service: SanctumAuthService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(SanctumAuthService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
