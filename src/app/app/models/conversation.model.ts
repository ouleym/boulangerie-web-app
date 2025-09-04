import { Message } from './message.model';

export interface Conversation {
  id: number;
  clientId: number;
  employeId?: number;
  messages: Message[];
  created_at: string;
  updated_at: string;
}
