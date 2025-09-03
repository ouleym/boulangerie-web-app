import { Component, OnInit, ViewChild, ElementRef, AfterViewChecked, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MessagesComponent } from '../messages/messages.component';
import { Subscription } from 'rxjs';
import { Chat, ChatService, Message } from '../../core/chat.service';
import { AuthService } from '../../core/auth.service';

@Component({
  selector: 'app-chat',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './chat.component.html',
  styleUrl: './chat.component.scss'
})
export class ChatComponent implements OnInit, AfterViewChecked, OnDestroy {
  @ViewChild('messagesContainer') messagesContainer!: ElementRef;
  @ViewChild('messageInput') messageInput!: ElementRef;

  currentChat: Chat | null = null;
  chats: Chat[] = [];
  messageText: string = '';
  isTyping: boolean = false;
  isLoading: boolean = false;
  hasError: boolean = false;
  errorMessage: string = '';
  editingMessageId: number | null = null;
  editingText: string = '';
  
  // Interface states
  showChatList: boolean = false;
  isChatVisible: boolean = false;
  isAuthenticated: boolean = false;
  isClient: boolean = false;
  
  private shouldScrollToBottom = false;
  private subscriptions = new Subscription();

  constructor(
    private chatService: ChatService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.initializeComponent();
    this.setupSubscriptions();
  }

  ngAfterViewChecked(): void {
    if (this.shouldScrollToBottom) {
      this.scrollToBottom();
      this.shouldScrollToBottom = false;
    }
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  private initializeComponent(): void {
    this.isAuthenticated = this.authService.isAuthenticated();
    this.isClient = this.authService.hasRole('Client');
    
    if (this.isAuthenticated && this.isClient) {
      this.loadChats();
    }
  }

  private setupSubscriptions(): void {
    // Écouter la visibilité du chat
    this.subscriptions.add(
      this.chatService.isChatVisible$.subscribe(visible => {
        this.isChatVisible = visible;
        if (visible && this.isClient && !this.chats.length) {
          this.loadChats();
        }
      })
    );

    // Écouter les changements de chat actuel
    this.subscriptions.add(
      this.chatService.currentChat$.subscribe((chat: Chat | null) => {
        this.currentChat = chat;
        this.hasError = false;
        this.errorMessage = '';
        
        if (chat && chat.messages) {
          this.shouldScrollToBottom = true;
        }
      })
    );

    // Écouter la liste des chats
    this.subscriptions.add(
      this.chatService.chats$.subscribe(chats => {
        this.chats = chats;
      })
    );

    // Écouter les changements d'authentification
    this.subscriptions.add(
      this.authService.loggedIn$.subscribe(isLoggedIn => {
        this.isAuthenticated = isLoggedIn;
        this.isClient = isLoggedIn && this.authService.hasRole('Client');
        
        if (!isLoggedIn) {
          this.hideChat();
        }
      })
    );
  }

  private loadChats(): void {
    if (!this.isAuthenticated || !this.isClient) return;
    
    this.isLoading = true;
    this.chatService.getChats().subscribe({
      next: () => {
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Erreur lors du chargement des chats:', error);
        this.isLoading = false;
        this.handleError('Erreur lors du chargement des conversations');
      }
    });
  }

  // =================== GESTION DE L'AFFICHAGE ===================

  showChat(): void {
    this.chatService.showChat();
  }

  hideChat(): void {
    this.chatService.hideChat();
  }

  toggleChat(): void {
    this.chatService.toggleChatVisibility();
  }

  toggleChatList(): void {
    this.showChatList = !this.showChatList;
  }

  // =================== GESTION DES CHATS ===================

  startNewChat(): void {
    if (!this.isAuthenticated || !this.isClient) return;
    
    this.chatService.clearCurrentChat();
    this.showChatList = false;
    this.hasError = false;
    this.errorMessage = '';
    setTimeout(() => this.focusInput(), 100);
  }

  selectChat(chat: Chat): void {
    if (!this.isClient) return;
    
    this.isLoading = true;
    this.chatService.getChat(chat.id).subscribe({
      next: (loadedChat: Chat) => {
        this.currentChat = loadedChat;
        this.shouldScrollToBottom = true;
        this.isLoading = false;
        this.showChatList = false;
        this.hasError = false;
      },
      error: (error) => {
        console.error('Erreur lors du chargement du chat:', error);
        this.isLoading = false;
        this.handleError('Conversation introuvable');
      }
    });
  }

  deleteChat(chat: Chat, event: Event): void {
    if (!this.isClient) return;
    
    event.stopPropagation();
    
    if (confirm(`Supprimer la conversation "${chat.title}" ?`)) {
      this.chatService.deleteChat(chat.id).subscribe({
        next: () => {
          if (this.currentChat?.id === chat.id) {
            this.startNewChat();
          }
        },
        error: (error) => {
          console.error('Erreur lors de la suppression:', error);
          this.handleError('Erreur lors de la suppression');
        }
      });
    }
  }

  // =================== GESTION DES MESSAGES ===================

  sendMessage(): void {
    if (!this.canSendMessage()) return;

    const message = this.messageText.trim();
    this.messageText = '';
    this.isTyping = true;
    this.hasError = false;

    if (!this.currentChat) {
      this.createNewChatWithMessage(message);
    } else {
      this.sendMessageToExistingChat(message);
    }
  }

  private canSendMessage(): boolean {
    return this.messageText.trim().length > 0 && 
           !this.isTyping && 
           this.isAuthenticated && 
           this.isClient;
  }

  private createNewChatWithMessage(message: string): void {
    this.chatService.createChat(message).subscribe({
      next: (result: { chat: Chat; messages: Message[] }) => {
        this.currentChat = result.chat;
        
        if (this.currentChat && !this.currentChat.messages) {
          this.currentChat.messages = [];
        }
        
        if (this.currentChat && this.currentChat.messages && result.messages) {
          this.currentChat.messages.push(...result.messages);
        }
        
        this.shouldScrollToBottom = true;
        this.isTyping = false;
        this.focusInput();
      },
      error: (error) => {
        console.error('Erreur lors de la création du chat:', error);
        this.isTyping = false;
        this.messageText = message;
        this.handleError('Erreur lors de la création de la conversation');
      }
    });
  }

  private sendMessageToExistingChat(message: string): void {
    if (!this.currentChat) return;

    this.chatService.sendMessage(this.currentChat.id, message).subscribe({
      next: (result: { user_message: Message; assistant_message: Message }) => {
        if (this.currentChat?.messages) {
          this.currentChat.messages.push(result.user_message, result.assistant_message);
          this.shouldScrollToBottom = true;
        }
        this.isTyping = false;
        this.focusInput();
      },
      error: (error) => {
        console.error('Erreur lors de l\'envoi du message:', error);
        this.isTyping = false;
        this.messageText = message;
        this.handleError('Erreur lors de l\'envoi du message');
      }
    });
  }

  // =================== ÉDITION DES MESSAGES ===================

  startEditMessage(message: Message): void {
    if (message.role !== 'user') return;
    
    this.editingMessageId = message.id;
    this.editingText = message.content;
  }

  saveEditMessage(): void {
    if (!this.editingMessageId || !this.editingText.trim()) return;

    this.chatService.updateMessage(this.editingMessageId, this.editingText.trim()).subscribe({
      next: () => {
        this.editingMessageId = null;
        this.editingText = '';
      },
      error: (error) => {
        console.error('Erreur lors de la modification:', error);
        this.handleError('Erreur lors de la modification du message');
      }
    });
  }

  cancelEditMessage(): void {
    this.editingMessageId = null;
    this.editingText = '';
  }

  // =================== ÉVÉNEMENTS ===================

  onKeyDown(event: KeyboardEvent): void {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      this.sendMessage();
    }
  }

  onEditKeyDown(event: KeyboardEvent): void {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      this.saveEditMessage();
    } else if (event.key === 'Escape') {
      this.cancelEditMessage();
    }
  }

  // =================== UTILITAIRES ===================

  private handleError(message: string): void {
    this.hasError = true;
    this.errorMessage = message;
  }

  private scrollToBottom(): void {
    try {
      if (this.messagesContainer) {
        const element = this.messagesContainer.nativeElement;
        element.scrollTop = element.scrollHeight;
      }
    } catch (err) {
      console.warn('Erreur lors du scroll:', err);
    }
  }

  private focusInput(): void {
    setTimeout(() => {
      try {
        if (this.messageInput && !this.editingMessageId) {
          this.messageInput.nativeElement.focus();
        }
      } catch (err) {
        console.warn('Erreur lors du focus sur l\'input:', err);
      }
    }, 100);
  }

  formatChatDate(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = now.getTime() - date.getTime();
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) {
      return date.toLocaleTimeString('fr-FR', { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
    } else if (diffDays === 1) {
      return 'Hier';
    } else if (diffDays < 7) {
      return date.toLocaleDateString('fr-FR', { weekday: 'short' });
    } else {
      return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit'
      });
    }
  }

  formatChatTitle(title: string): string {
    return title.length > 50 ? title.substring(0, 50) + '...' : title;
  }

  setSuggestion(suggestion: string): void {
    this.messageText = suggestion;
    this.focusInput();
  }

  // =================== GETTERS ===================

  get isInputDisabled(): boolean {
    return this.isTyping || !this.isAuthenticated || !this.isClient;
  }

  get canSendButton(): boolean {
    return this.messageText.trim().length > 0 && !this.isTyping;
  }

  get messageCount(): number {
    return this.currentChat?.messages?.length || 0;
  }

  get chatTitle(): string {
    return this.currentChat?.title || 'Assistant IA';
  }

  get hasChats(): boolean {
    return this.chats.length > 0;
  }
}