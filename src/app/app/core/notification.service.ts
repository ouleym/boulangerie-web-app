import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class NotificationService {

  constructor() { }

  showSuccess(message: string, title: string = 'Succès'): void {
    console.log(`✅ ${title}: ${message}`);
    // Vous pouvez remplacer par une vraie notification (toast, snackbar, etc.)
    // Exemple avec alert temporaire :
    // alert(`${title}: ${message}`);
  }

  showError(message: string, title: string = 'Erreur'): void {
    console.error(`❌ ${title}: ${message}`);
    // Vous pouvez remplacer par une vraie notification
    // Exemple avec alert temporaire :
    // alert(`${title}: ${message}`);
  }

  showInfo(message: string, title: string = 'Info'): void {
    console.info(`ℹ️ ${title}: ${message}`);
    // Vous pouvez remplacer par une vraie notification
    // Exemple avec alert temporaire :
    // alert(`${title}: ${message}`);
  }

  showWarning(message: string, title: string = 'Attention'): void {
    console.warn(`⚠️ ${title}: ${message}`);
    // Vous pouvez remplacer par une vraie notification
    // Exemple avec alert temporaire :
    // alert(`${title}: ${message}`);
  }
}