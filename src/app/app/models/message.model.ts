export interface Message {
  id: number;
  conversationId: number;
  sender: 'client' | 'employe' | 'admin';
  contenu: string;
  timestamp: string;
}
