import { TestBed } from '@angular/core/testing';

import { PermissionCheckerService } from './permission-checker.service';

describe('PermissionCheckerService', () => {
  let service: PermissionCheckerService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(PermissionCheckerService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
