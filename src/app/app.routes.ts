import { Routes } from '@angular/router';
import { LoginComponent } from './app/auth/login/login.component';
import { RegisterComponent } from './app/auth/register/register.component';
import { ProduitsListComponent } from './app/produits/list/list.component';
import { ProduitsDetailComponent } from './app/produits/detail/detail.component';
import { ProduitsFormComponent } from './app/produits/form/form.component';
import { authGuard } from './app/core/auth.guard';
import { roleGuard } from './app/core/role.guard';
import { ClientComponent } from './app/dashboard/client/client.component';
import { EmployeComponent } from './app/dashboard/employe/employe.component';
import { AdminComponent } from './app/dashboard/admin/admin.component';
import { ProfileComponent } from './app/auth/profile/profile.component';
import { CommandesListComponent } from './app/commandes/list/list.component';
import { CommandesDetailComponent } from './app/commandes/detail/detail.component';
import { CommandesStatusComponent } from './app/commandes/status/status.component';
import { CommandesPanierComponent } from './app/commandes/panier/panier.component';
import { CommandesCheckoutComponent } from './app/commandes/checkout/checkout.component';
import { ConversationsComponent } from './app/support/conversations/conversations.component';
import { ChatComponent } from './app/support/chat/chat.component';
import { MessagesComponent } from './app/support/messages/messages.component';
import { UsersListComponent } from './app/users/list/list.component';
import { UsersFormComponent } from './app/users/form/form.component';
import { PromotionsListComponent } from './app/promotions/list/list.component';
import { PromotionsFormComponent } from './app/promotions/form/form.component';
import { PromotionsDetailComponent } from './app/promotions/detail/detail.component';
import { CategoriesListComponent } from './app/categories/list/list.component';
import { CategoriesFormComponent } from './app/categories/form/form.component';
import { CategoriesDetailComponent } from './app/categories/detail/detail.component';

export const routes: Routes = [
  // ============================================
  // ROUTES PUBLIQUES
  // ============================================
  
  // Redirection par défaut
  { 
    path: '', 
    redirectTo: '/login', 
    pathMatch: 'full' 
  },

  // Routes publiques d'authentification
  { 
    path: 'login', 
    component: LoginComponent, 
    title: 'Connexion - Boulangerie'
  },
  { 
    path: 'register', 
    component: RegisterComponent, 
    title: 'Inscription - Boulangerie'
  },

  // ============================================
  // DASHBOARDS PAR RÔLE
  // ============================================

  // Dashboard CLIENT
  {
    path: 'dashboard/client',
    component: ClientComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Client'] },
    title: 'Tableau de bord Client'
  },

  // Dashboard EMPLOYÉ
  {
    path: 'dashboard/employe',
    component: EmployeComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Employe'] },
    title: 'Tableau de bord Employé'
  },

  // Dashboard ADMIN
  {
    path: 'dashboard/admin',
    component: AdminComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Admin'] },
    title: 'Tableau de bord Admin'
  },

  // ============================================
  // PROFIL UTILISATEUR (Tous les rôles)
  // ============================================
  {
    path: 'profile',
    component: ProfileComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Client', 'Employe', 'Admin'] },
    title: 'Mon Profil'
  },

  // ============================================
  // PRODUITS (Consultation - Tous les rôles)
  // ============================================
  {
    path: 'produits',
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Client', 'Employe', 'Admin'] },
    children: [
      { 
        path: '', 
        component: ProduitsListComponent, 
        title: 'Nos Produits' 
      },
      { 
        path: ':id', 
        component: ProduitsDetailComponent, 
        title: 'Détails Produit' 
      }
    ]
  },

  // ============================================
  // COMMANDES CLIENT
  // ============================================
  
  // Mes commandes (Client uniquement)
  {
    path: 'mes-commandes',
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Client'] },
    children: [
      { 
        path: '', 
        component: CommandesListComponent, 
        title: 'Mes Commandes' 
      },
      { 
        path: ':id', 
        component: CommandesDetailComponent, 
        title: 'Détail Commande' 
      },
      { 
        path: ':id/status', 
        component: CommandesStatusComponent, 
        title: 'Suivi Commande' 
      }
    ]
  },

  // Panier (Client uniquement)
  {
    path: 'panier',
    component: CommandesPanierComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Client'] },
    title: 'Mon Panier'
  },

  // Checkout (Client uniquement)
  {
    path: 'checkout',
    component: CommandesCheckoutComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Client'] },
    title: 'Finaliser Commande'
  },

  // ============================================
  // SUPPORT CLIENT (Tous les rôles)
  // ============================================
  {
    path: 'support',
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Client', 'Employe', 'Admin'] },
    children: [
      { 
        path: '', 
        component: ConversationsComponent, 
        title: 'Support Client' 
      },
      { 
        path: 'chat', 
        component: ChatComponent, 
        title: 'Chat Support' 
      },
      { 
        path: 'conversation/:id', 
        component: MessagesComponent, 
        title: 'Conversation Support' 
      }
    ]
  },

  // ============================================
  // GESTION EMPLOYÉ (Employé + Admin)
  // ============================================

  // Gestion des produits pour employés
  {
    path: 'employe/produits',
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Employe', 'Admin'] },
    children: [
      { 
        path: '', 
        component: ProduitsListComponent, 
        title: 'Gestion Produits' 
      },
      { 
        path: 'create', 
        component: ProduitsFormComponent, 
        title: 'Créer Produit' 
      },
      { 
        path: ':id/edit', 
        component: ProduitsFormComponent, 
        title: 'Modifier Produit' 
      }
    ]
  },

  // Gestion des commandes pour employés
  {
    path: 'employe/commandes',
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Employe', 'Admin'] },
    children: [
      { 
        path: '', 
        component: CommandesListComponent, 
        title: 'Toutes les Commandes' 
      },
      { 
        path: ':id', 
        component: CommandesDetailComponent, 
        title: 'Détail Commande' 
      }
    ]
  },

  // Gestion des catégories pour employés
  {
    path: 'employe/categories',
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Employe', 'Admin'] },
    children: [
      { 
        path: '', 
        component: CategoriesListComponent, 
        title: 'Gestion Catégories' 
      },
      { 
        path: 'create', 
        component: CategoriesFormComponent, 
        title: 'Créer Catégorie' 
      },
      { 
        path: ':id/edit', 
        component: CategoriesFormComponent, 
        title: 'Modifier Catégorie' 
      }
    ]
  },

  // ============================================
  // ADMINISTRATION (Admin uniquement)
  // ============================================

  // Section Administration principale
  {
    path: 'admin',
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Admin'] },
    title: 'Administration',
    children: [
      // Dashboard admin (redirection par défaut)
      {
        path: '',
        redirectTo: 'dashboard',
        pathMatch: 'full'
      },
      {
        path: 'dashboard',
        component: AdminComponent,
        title: 'Dashboard Admin'
      },

      // ========== GESTION UTILISATEURS ==========
      {
        path: 'users',
        children: [
          { 
            path: '', 
            component: UsersListComponent, 
            title: 'Gestion Utilisateurs' 
          },
          { 
            path: 'create', 
            component: UsersFormComponent, 
            title: 'Créer Utilisateur' 
          },
          { 
            path: ':id/edit', 
            component: UsersFormComponent, 
            title: 'Modifier Utilisateur' 
          }
        ]
      },

      // ========== GESTION CATÉGORIES ==========
      {
        path: 'categories',
        children: [
          { 
            path: '', 
            component: CategoriesListComponent, 
            title: 'Admin - Gestion Catégories' 
          },
          { 
            path: 'create', 
            component: CategoriesFormComponent, 
            title: 'Admin - Créer Catégorie' 
          },
          { 
            path: ':id', 
            component: CategoriesDetailComponent, 
            title: 'Admin - Détail Catégorie' 
          },
          { 
            path: ':id/edit', 
            component: CategoriesFormComponent, 
            title: 'Admin - Modifier Catégorie' 
          }
        ]
      },

      // ========== GESTION PRODUITS ==========
      {
        path: 'produits',
        children: [
          { 
            path: '', 
            component: ProduitsListComponent, 
            title: 'Admin - Tous les Produits' 
          },
          { 
            path: 'create', 
            component: ProduitsFormComponent, 
            title: 'Admin - Créer Produit' 
          },
          { 
            path: ':id', 
            component: ProduitsDetailComponent, 
            title: 'Admin - Détail Produit' 
          },
          { 
            path: ':id/edit', 
            component: ProduitsFormComponent, 
            title: 'Admin - Modifier Produit' 
          }
        ]
      },

      // ========== GESTION PROMOTIONS ==========
      {
        path: 'promotions',
        children: [
          { 
            path: '', 
            component: PromotionsListComponent, 
            title: 'Admin - Gestion Promotions' 
          },
          { 
            path: 'create', 
            component: PromotionsFormComponent, 
            title: 'Admin - Créer Promotion' 
          },
          { 
            path: ':id', 
            component: PromotionsDetailComponent, 
            title: 'Admin - Détail Promotion' 
          },
          { 
            path: ':id/edit', 
            component: PromotionsFormComponent, 
            title: 'Admin - Modifier Promotion' 
          }
        ]
      },

      // ========== GESTION COMMANDES ==========
      {
        path: 'commandes',
        children: [
          { 
            path: '', 
            component: CommandesListComponent, 
            title: 'Admin - Toutes les Commandes' 
          },
          { 
            path: ':id', 
            component: CommandesDetailComponent, 
            title: 'Admin - Détail Commande' 
          }
        ]
      },

      // ========== SUPPORT ADMINISTRATEUR ==========
      {
        path: 'support',
        children: [
          { 
            path: '', 
            component: ConversationsComponent, 
            title: 'Admin - Gestion Support' 
          },
          { 
            path: 'conversation/:id', 
            component: MessagesComponent, 
            title: 'Admin - Conversation Support' 
          }
        ]
      }
    ]
  },

  // ============================================
  // ROUTES D'ACCÈS RAPIDE PAR RÔLE
  // ============================================

  // Raccourcis pour les employés (accès direct sans préfixe employe/)
  {
    path: 'gestion-produits',
    redirectTo: '/employe/produits',
    pathMatch: 'full'
  },
  {
    path: 'gestion-commandes',
    redirectTo: '/employe/commandes',
    pathMatch: 'full'
  },
  {
    path: 'gestion-categories',
    redirectTo: '/employe/categories',
    pathMatch: 'full'
  },

  // Raccourcis pour l'administration (accès direct)
  {
    path: 'administration',
    redirectTo: '/admin/dashboard',
    pathMatch: 'full'
  },

  // Route 404 - Redirection intelligente
  { 
    path: '**', 
    redirectTo: '/login' 
  }
];