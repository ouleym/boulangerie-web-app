import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse } from '../models/api-response.model';

export interface Notification {
  id: number;
  userId: number;
  message: string;
  lu: boolean;
  created_at: string;
}

@Injectable({
  providedIn: 'root'
})
export class NotificationService {
  private apiUrl = 'http://localhost:8080/api/notifications';

  constructor(private http: HttpClient) {}

  // Récupérer toutes les notifications d'un utilisateur
  getNotificationsByUser(userId: number): Observable<ApiResponse<Notification[]>> {
    return this.http.get<ApiResponse<Notification[]>>(`${this.apiUrl}/user/${userId}`);
  }

  // Marquer une notification comme lue
  marquerCommeLue(id: number): Observable<ApiResponse<Notification>> {
    return this.http.put<ApiResponse<Notification>>(`${this.apiUrl}/${id}/lu`, {});
  }

  // Créer une nouvelle notification
  creerNotification(notification: Partial<Notification>): Observable<ApiResponse<Notification>> {
    return this.http.post<ApiResponse<Notification>>(this.apiUrl, notification);
  }
}
