import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule, NgFor } from '@angular/common';
import { Router, RouterModule, NavigationEnd } from '@angular/router';
import { AuthService } from '../../core/auth.service';
import { ChatService, Chat } from '../../core/chat.service';
import { Subscription } from 'rxjs';
import { filter } from 'rxjs/operators';

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [CommonModule, RouterModule, NgFor],
  templateUrl: './sidebar.component.html',
  styleUrl: './sidebar.component.scss'
})
export class SidebarComponent implements OnInit, OnDestroy {
  isCollapsed = false;
  isChatPanelOpen = false;
  currentRoute = '';
  
  // Données utilisateur
  userRoles: string[] = [];
  isAuthenticated = false;
  userName = '';
  
  // Données chat
  chats: Chat[] = [];
  currentChat: Chat | null = null;
  isLoadingChats = false;
  
  private subscriptions = new Subscription();

  constructor(
    private authService: AuthService,
    private chatService: ChatService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.initializeComponent();
    this.setupSubscriptions();
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  private initializeComponent(): void {
    // Initialiser les données d'authentification
    this.isAuthenticated = this.authService.isAuthenticated();
    this.userRoles = this.authService.getUserRoles();
    
    // Obtenir le nom d'utilisateur depuis le token
    const decodedToken = this.authService.getDecodedToken();
    this.userName = decodedToken?.nom || decodedToken?.email || 'Utilisateur';

    // Route actuelle
    this.currentRoute = this.router.url;
    
    // Charger les chats si l'utilisateur est un client connecté
    if (this.isAuthenticated && this.isClient()) {
      this.loadChats();
    }
  }

  private setupSubscriptions(): void {
    // Écouter les changements d'authentification
    this.subscriptions.add(
      this.authService.loggedIn$.subscribe(isLoggedIn => {
        this.isAuthenticated = isLoggedIn;
        if (isLoggedIn) {
          this.userRoles = this.authService.getUserRoles();
          // Ne charger les chats que si c'est un client
          if (this.isClient()) {
            this.loadChats();
          }
        } else {
          this.chats = [];
          this.currentChat = null;
          this.isChatPanelOpen = false; // Fermer le panel si déconnecté
        }
      })
    );

    // Écouter les changements de route
    this.subscriptions.add(
      this.router.events.pipe(
        filter(event => event instanceof NavigationEnd)
      ).subscribe((event: NavigationEnd) => {
        this.currentRoute = event.urlAfterRedirects;
      })
    );

    // Écouter les changements de chats (uniquement pour les clients)
    this.subscriptions.add(
      this.chatService.chats$.subscribe(chats => {
        if (this.isClient()) {
          this.chats = chats;
        }
      })
    );

    // Écouter le chat actuel (uniquement pour les clients)
    this.subscriptions.add(
      this.chatService.currentChat$.subscribe(chat => {
        if (this.isClient()) {
          this.currentChat = chat;
        }
      })
    );
  }

  // =================== VÉRIFICATION DU RÔLE ===================
  
  /**
   * Vérifie si l'utilisateur connecté a le rôle "Client"
   */
  isClient(): boolean {
    return this.hasRole('Client');
  }

  // =================== GESTION DE LA SIDEBAR ===================
  
  toggleSidebar(): void {
    this.isCollapsed = !this.isCollapsed;
  }

  toggleChatPanel(): void {
    // Ne permettre l'ouverture que pour les clients
    if (!this.isClient()) {
      return;
    }
    
    this.isChatPanelOpen = !this.isChatPanelOpen;
    if (this.isChatPanelOpen && this.chats.length === 0) {
      this.loadChats();
    }
  }

  closeChatPanel(): void {
    this.isChatPanelOpen = false;
  }

  // =================== GESTION DES CHATS ===================

  private loadChats(): void {
    if (!this.isAuthenticated || !this.isClient()) return;
    
    this.isLoadingChats = true;
    this.chatService.getChats().subscribe({
      next: (chats) => {
        this.isLoadingChats = false;
        console.log('Chats chargés:', chats.length);
      },
      error: (error) => {
        console.error('Erreur lors du chargement des chats:', error);
        this.isLoadingChats = false;
        
        // Afficher un message d'erreur plus spécifique
        if (error.message.includes('serveur')) {
          console.warn('Problème de connexion au serveur - fonctionnalité chat temporairement indisponible');
        }
        
        // Réinitialiser les données
        this.chats = [];
        this.currentChat = null;
      }
    });
  }

  startNewChat(): void {
    if (!this.isClient()) return;
    
    this.chatService.clearCurrentChat();
    this.router.navigate(['/chat']);
    this.closeChatPanel();
  }

  // Correction de la méthode selectChat
  selectChat(chat: Chat): void {
    if (!this.isClient()) return;
    
    this.chatService.setCurrentChat(chat);
    this.router.navigate(['/chat', chat.id]);
    this.closeChatPanel();
  }

  deleteChat(chat: Chat, event: Event): void {
    if (!this.isClient()) return;
    
    event.stopPropagation();
    
    if (confirm(`Supprimer la conversation "${chat.title}" ?`)) {
      this.chatService.deleteChat(chat.id).subscribe({
        next: () => {
          // Chat supprimé avec succès
          if (this.currentChat?.id === chat.id) {
            this.router.navigate(['/chat']);
          }
        },
        error: (error) => {
          console.error('Erreur lors de la suppression:', error);
        }
      });
    }
  }

  // =================== NAVIGATION ===================

  navigateTo(path: string): void {
    this.router.navigate([path]);
    if (this.isCollapsed) {
      this.isCollapsed = false;
    }
  }

  logout(): void {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
      this.authService.logout();
      this.router.navigate(['/login']);
      this.closeChatPanel();
    }
  }

  // =================== UTILITAIRES ===================

  hasRole(role: string): boolean {
    return this.authService.hasRole(role);
  }

  isRouteActive(route: string): boolean {
    return this.currentRoute.startsWith(route);
  }

  formatChatTitle(title: string): string {
    return title.length > 30 ? title.substring(0, 30) + '...' : title;
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

  // Navigation selon le rôle
  getDashboardRoute(): string {
    if (this.hasRole('Admin')) {
      return '/dashboard/admin';
    } else if (this.hasRole('Employe')) {
      return '/dashboard/employe';
    } else {
      return '/dashboard/client';
    }
  }

  trackByFn(index: number, item: Chat): number {
    return item.id;
  }
}