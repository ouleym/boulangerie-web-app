import { TestBed } from '@angular/core/testing';

import { AllergenCheckerService } from './allergen-checker.service';

describe('AllergenCheckerService', () => {
  let service: AllergenCheckerService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AllergenCheckerService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
