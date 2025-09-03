import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { Message } from './chat.service';

declare global {
  interface Window {
    Pusher: any;
    Echo: any;
  }
}

@Injectable({
  providedIn: 'root'
})
export class WebSocketService {
 
private echo: Echo<any> | null = null;
  private connectionStatus = new BehaviorSubject<boolean>(false);
  private messageReceived = new BehaviorSubject<Message | null>(null);

  public connectionStatus$ = this.connectionStatus.asObservable();
  public messageReceived$ = this.messageReceived.asObservable();

  constructor() {}

  // Initialiser la connexion WebSocket
  initialize(userId: number): void {
    if (this.echo) {
      this.disconnect();
    }

    const token = localStorage.getItem('auth_token');
    
    if (!token) {
      console.error('Token JWT manquant pour la connexion WebSocket');
      return;
    }

    // Configuration Pusher
    window.Pusher = Pusher;

    this.echo = new Echo({
      broadcaster: 'reverb',
      key: 'my-app-key',
      wsHost: 'localhost',
      wsPort: 8080,
      wssPort: 8080,
      forceTLS: false,
      enabledTransports: ['ws', 'wss'],
      auth: {
        headers: {
          Authorization: `Bearer ${token}`
        }
      }
    });

    // Écouter les événements de connexion
    this.echo.connector.pusher.connection.bind('connected', () => {
      console.log('WebSocket connecté');
      this.connectionStatus.next(true);
    });

    this.echo.connector.pusher.connection.bind('disconnected', () => {
      console.log('WebSocket déconnecté');
      this.connectionStatus.next(false);
    });

    this.echo.connector.pusher.connection.bind('error', (error: any) => {
      console.error('Erreur WebSocket:', error);
      this.connectionStatus.next(false);
    });

    // S'abonner au canal privé de l'utilisateur
    this.subscribeToUserChannel(userId);
  }

  // S'abonner au canal de l'utilisateur
  private subscribeToUserChannel(userId: number): void {
    if (!this.echo) return;

    this.echo.private(`chat.${userId}`)
      .listen('.message.sent', (e: { message: Message }) => {
        console.log('Nouveau message reçu:', e.message);
        this.messageReceived.next(e.message);
      })
      .error((error: any) => {
        console.error('Erreur lors de l\'abonnement au canal:', error);
      });
  }

  // Déconnecter WebSocket
  disconnect(): void {
    if (this.echo) {
      this.echo.disconnect();
      this.echo = null;
      this.connectionStatus.next(false);
    }
  }

  // Vérifier si connecté
  isConnected(): boolean {
    return this.connectionStatus.value;
  }

 // Obtenir le dernier message reçu
getLastMessage(): Message | null {
  return this.messageReceived.value;
}

}