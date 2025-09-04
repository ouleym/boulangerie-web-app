import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse, PaginatedResponse } from '../models/api-response.model';

export interface Conversation {
  id: number;
  clientId: number;
  employeId: number;
  messages: Message[];
  created_at: string;
  updated_at: string;
}

export interface Message {
  id: number;
  conversationId: number;
  sender: 'client' | 'employe' | 'admin';
  content: string;
  timestamp: string;
}

@Injectable({
  providedIn: 'root'
})
export class ConversationService {
  private apiUrl = 'http://localhost:8080/api/conversations';

  constructor(private http: HttpClient) {}

  // Récupérer toutes les conversations avec pagination
  getConversations(page: number = 0, size: number = 10): Observable<PaginatedResponse<Conversation>> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('size', size.toString());
    return this.http.get<PaginatedResponse<Conversation>>(this.apiUrl, { params });
  }

  // Récupérer une conversation par ID
  getConversationById(id: number): Observable<ApiResponse<Conversation>> {
    return this.http.get<ApiResponse<Conversation>>(`${this.apiUrl}/${id}`);
  }

  // Créer une nouvelle conversation
  createConversation(conv: Partial<Conversation>): Observable<ApiResponse<Conversation>> {
    return this.http.post<ApiResponse<Conversation>>(this.apiUrl, conv);
  }

  // Envoyer un message dans une conversation existante
  sendMessage(conversationId: number, message: Partial<Message>): Observable<ApiResponse<Message>> {
    return this.http.post<ApiResponse<Message>>(`${this.apiUrl}/${conversationId}/messages`, message);
  }
}
