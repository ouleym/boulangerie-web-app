import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { BehaviorSubject, Observable, throwError } from 'rxjs';
import { map, catchError, tap } from 'rxjs/operators';
import { environment } from '../../../environments/environment.development';

export interface Message {
  id: number;
  chat_id: number;
  role: 'user' | 'assistant' | 'system';
  content: string;
  created_at: string;
  meta?: any;
}

export interface Chat {
  id: number;
  user_id: number;
  title: string;
  created_at: string;
  updated_at: string;
  messages?: Message[];
  latest_message?: Message;
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
  errors?: any;
}

@Injectable({
  providedIn: 'root'
})
export class ChatService {
  private chatsSubject = new BehaviorSubject<Chat[]>([]);
  private currentChatSubject = new BehaviorSubject<Chat | null>(null);
  private isChatVisibleSubject = new BehaviorSubject<boolean>(false);

  public chats$ = this.chatsSubject.asObservable();
  public currentChat$ = this.currentChatSubject.asObservable();
  public isChatVisible$ = this.isChatVisibleSubject.asObservable();

  constructor(private http: HttpClient) {}

  private getHeaders(): HttpHeaders {
    const token = localStorage.getItem('token');
    return new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    });
  }

  private handleError = (error: HttpErrorResponse) => {
    console.error('ChatService Error:', error);
    let errorMessage = 'Une erreur est survenue';
    
    if (error.error?.message) {
      errorMessage = error.error.message;
    } else if (error.status === 0) {
      errorMessage = 'Impossible de contacter le serveur';
    } else if (error.status === 401) {
      errorMessage = 'Session expirée, veuillez vous reconnecter';
    } else if (error.status === 403) {
      errorMessage = 'Accès non autorisé';
    } else if (error.status >= 500) {
      errorMessage = 'Erreur serveur, veuillez réessayer';
    }
    
    return throwError(() => new Error(errorMessage));
  };

  // Gestion de la visibilité du chat
  toggleChatVisibility(): void {
    this.isChatVisibleSubject.next(!this.isChatVisibleSubject.value);
  }

  showChat(): void {
    this.isChatVisibleSubject.next(true);
  }

  hideChat(): void {
    this.isChatVisibleSubject.next(false);
  }

  // Récupérer tous les chats
  getChats(): Observable<Chat[]> {
    return this.http.get<ApiResponse<Chat[]>>(`${environment.ApiUrl}/chats`, {
      headers: this.getHeaders()
    }).pipe(
      map(response => {
        if (response.success && response.data) {
          this.chatsSubject.next(response.data);
          return response.data;
        }
        return [];
      }),
      tap(chats => console.log('Chats loaded:', chats.length)),
      catchError(this.handleError)
    );
  }

  // Récupérer un chat avec ses messages
  getChat(chatId: number): Observable<Chat> {
    return this.http.get<ApiResponse<Chat>>(`${environment.ApiUrl}/chats/${chatId}`, {
      headers: this.getHeaders()
    }).pipe(
      map(response => {
        if (response.success && response.data) {
          this.currentChatSubject.next(response.data);
          return response.data;
        }
        throw new Error(response.message || 'Chat non trouvé');
      }),
      catchError(this.handleError)
    );
  }

  // Créer un nouveau chat
  createChat(message: string): Observable<{ chat: Chat; messages: Message[] }> {
    return this.http.post<ApiResponse<{ chat: Chat; messages: Message[] }>>(`${environment.ApiUrl}/chats`, {
      message
    }, {
      headers: this.getHeaders()
    }).pipe(
      map(response => {
        if (response.success && response.data) {
          const currentChats = this.chatsSubject.value;
          this.chatsSubject.next([response.data.chat, ...currentChats]);
          this.currentChatSubject.next(response.data.chat);
          return response.data;
        }
        throw new Error(response.message || 'Erreur lors de la création');
      }),
      catchError(this.handleError)
    );
  }

  // Envoyer un message
  sendMessage(chatId: number, message: string): Observable<{ user_message: Message; assistant_message: Message }> {
    return this.http.post<ApiResponse<{ user_message: Message; assistant_message: Message }>>(`${environment.ApiUrl}/chats/${chatId}/messages`, {
      message
    }, {
      headers: this.getHeaders()
    }).pipe(
      map(response => {
        if (response.success && response.data) {
          return response.data;
        }
        throw new Error(response.message || 'Erreur lors de l\'envoi');
      }),
      catchError(this.handleError)
    );
  }

  // Modifier un message
  updateMessage(messageId: number, newContent: string): Observable<Message> {
    return this.http.put<ApiResponse<Message>>(`${environment.ApiUrl}/messages/${messageId}`, {
      content: newContent
    }, {
      headers: this.getHeaders()
    }).pipe(
      map(response => {
        if (response.success && response.data) {
          // Mettre à jour le message dans le chat actuel
          const currentChat = this.currentChatSubject.value;
          if (currentChat && currentChat.messages) {
            const messageIndex = currentChat.messages.findIndex(m => m.id === messageId);
            if (messageIndex !== -1) {
              currentChat.messages[messageIndex] = response.data;
              this.currentChatSubject.next({ ...currentChat });
            }
          }
          return response.data;
        }
        throw new Error(response.message || 'Erreur lors de la modification');
      }),
      catchError(this.handleError)
    );
  }

  // Supprimer un chat
  deleteChat(chatId: number): Observable<boolean> {
    return this.http.delete<ApiResponse<any>>(`${environment.ApiUrl}/chats/${chatId}`, {
      headers: this.getHeaders()
    }).pipe(
      map(response => {
        if (response.success) {
          const currentChats = this.chatsSubject.value;
          const updatedChats = currentChats.filter(chat => chat.id !== chatId);
          this.chatsSubject.next(updatedChats);
          
          const currentChat = this.currentChatSubject.value;
          if (currentChat && currentChat.id === chatId) {
            this.currentChatSubject.next(null);
          }
          
          return true;
        }
        return false;
      }),
      catchError(this.handleError)
    );
  }

  // Définir le chat actuel
  setCurrentChat(chat: Chat | null): void {
    this.currentChatSubject.next(chat);
  }

  // Obtenir le chat actuel
  getCurrentChat(): Chat | null {
    return this.currentChatSubject.value;
  }

  // Effacer le chat actuel
  clearCurrentChat(): void {
    this.currentChatSubject.next(null);
  }

  // Ajouter un message au chat actuel
  addMessageToCurrentChat(message: Message): void {
    const currentChat = this.currentChatSubject.value;
    if (currentChat && currentChat.id === message.chat_id) {
      if (!currentChat.messages) {
        currentChat.messages = [];
      }
      currentChat.messages.push(message);
      this.currentChatSubject.next({ ...currentChat });
    }
  }

  // Réinitialiser le service
  reset(): void {
    this.chatsSubject.next([]);
    this.currentChatSubject.next(null);
    this.isChatVisibleSubject.next(false);
  }
}