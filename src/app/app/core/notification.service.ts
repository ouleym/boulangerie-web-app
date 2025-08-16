import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class NotificationService {
  showSuccess(arg0: string, arg1: string) {
    throw new Error('Method not implemented.');
  }
  showError(arg0: any, arg1: string) {
    throw new Error('Method not implemented.');
  }
  showInfo(arg0: string, arg1: string) {
    throw new Error('Method not implemented.');
  }

  constructor() { }
}
