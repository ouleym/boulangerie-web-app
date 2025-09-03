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
import { AdminStatsComponent } from './app/support/admin-stats/admin-stats.component';
import { PayementComponent } from './app/payement/payement.component';

export const routes: Routes = [
  // ============================================
  // ROUTES PUBLIQUES
  // ============================================
  
  { 
    path: '', 
    redirectTo: '/login', 
    pathMatch: 'full' 
  },

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

  {
    path: 'dashboard/client',
    component: ClientComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Client'] },
    title: 'Tableau de bord Client'
  },

  {
    path: 'dashboard/employe',
    component: EmployeComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Employe'] },
    title: 'Tableau de bord Employé'
  },

  {
    path: 'dashboard/admin',
    component: AdminComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Admin'] },
    title: 'Tableau de bord Admin - Statistiques'
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
  // PRODUITS - Accès selon les rôles
  // ============================================
  
  // Consultation produits (Tous les rôles)
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

  // Gestion produits (Employé seulement)
  {
    path: 'employe/produits',
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Employe'] },
    children: [
      { 
        path: '', 
        component: ProduitsListComponent, 
        title: 'Gestion Produits - Employé' 
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
      },
      { 
        path: ':id', 
        component: ProduitsDetailComponent, 
        title: 'Détail Produit' 
      }
    ]
  },

  // ============================================
  // COMMANDES - Accès selon les rôles
  // ============================================
  
  // Commandes clients (Client seulement)
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

  // Gestion commandes (Employé seulement)
  {
    path: 'employe/commandes',
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Employe'] },
    children: [
      { 
        path: '', 
        component: CommandesListComponent, 
        title: 'Gestion Commandes - Employé' 
      },
      { 
        path: ':id', 
        component: CommandesDetailComponent, 
        title: 'Détail Commande' 
      },
      { 
        path: ':id/status', 
        component: CommandesStatusComponent, 
        title: 'Modifier Statut' 
      }
    ]
  },

  // Panier et checkout (Client seulement)
  {
    path: 'panier',
    component: CommandesPanierComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Client'] },
    title: 'Mon Panier'
  },

  {
    path: 'payement',
    component: PayementComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Client'] },
    title: 'Finaliser Commande'
  },

  // ============================================
  // CATÉGORIES - Accès selon les rôles
  // ============================================
  
  // Gestion catégories (Employé seulement)
  {
    path: 'employe/categories',
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Employe'] },
    children: [
      { 
        path: '', 
        component: CategoriesListComponent, 
        title: 'Gestion Catégories - Employé' 
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
      },
      { 
        path: ':id', 
        component: CategoriesDetailComponent, 
        title: 'Détail Catégorie' 
      }
    ]
  },

  // ============================================
  // ADMINISTRATION (Admin uniquement)
  // ============================================
  {
    path: 'admin',
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Admin'] },
    title: 'Administration',
    children: [
      // Dashboard statistiques (redirection par défaut)
      {
        path: '',
        redirectTo: 'statistics',
        pathMatch: 'full'
      },
      {
        path: 'statistics',
        component: AdminStatsComponent,
        title: 'Statistiques de la Boulangerie'
      },

      // ========== GESTION UTILISATEURS (Admin seulement) ==========
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

      // ========== SUPERVISION GÉNÉRALE (Admin) ==========
      {
        path: 'supervision',
        children: [
          // Vue d'ensemble de tous les produits
          {
            path: 'produits',
            children: [
              { 
                path: '', 
                component: ProduitsListComponent, 
                title: 'Admin - Supervision Produits' 
              },
              { 
                path: ':id', 
                component: ProduitsDetailComponent, 
                title: 'Admin - Détail Produit' 
              }
            ]
          },
          
          // Vue d'ensemble de toutes les commandes
          {
            path: 'commandes',
            children: [
              { 
                path: '', 
                component: CommandesListComponent, 
                title: 'Admin - Supervision Commandes' 
              },
              { 
                path: ':id', 
                component: CommandesDetailComponent, 
                title: 'Admin - Détail Commande' 
              }
            ]
          },

          // Vue d'ensemble de toutes les catégories
          {
            path: 'categories',
            children: [
              { 
                path: '', 
                component: CategoriesListComponent, 
                title: 'Admin - Supervision Catégories' 
              },
              { 
                path: ':id', 
                component: CategoriesDetailComponent, 
                title: 'Admin - Détail Catégorie' 
              }
            ]
          }
        ]
      },

      // ========== GESTION PROMOTIONS (Admin) ==========
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

      // ========== SUPPORT CHAT (Admin) ==========
      {
        path: 'support',
        children: [
          { 
            path: 'chat', 
            component: ChatComponent, 
            title: 'Admin - Gestion Support' 
          },
          { 
            path: 'chat/:id', 
            component: ChatComponent, 
            title: 'Admin - Conversation Support'
          },
          { 
            path: 'messages', 
            component: MessagesComponent, 
            title: 'Admin - Tous les Messages' 
          }
        ]
      }
    ]
  },

  // ============================================
  // SUPPORT CLIENT
  // ============================================
  {
    path: 'chat',
    component: ChatComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Client', 'Employe', 'Admin'] },
    title: 'Chat Support'
  },
  {
    path: 'chat/:id',
    component: ChatComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Client', 'Employe', 'Admin'] },
    title: 'Conversation Chat'
  },

  // Support pour clients
  {
    path: 'support',
    canActivate: [authGuard, roleGuard],
    data: { roles: ['Client'] },
    children: [
      { 
        path: 'chat', 
        component: ChatComponent, 
        title: 'Chat Support' 
      },
      { 
        path: 'chat/:id', 
        component: ChatComponent, 
        title: 'Conversation Support'
      },
      { 
        path: 'conversation/:id', 
        component: MessagesComponent, 
        title: 'Messages Support' 
      }
    ]
  },

  // ============================================
  // ROUTES D'ACCÈS RAPIDE
  // ============================================

  // Raccourcis pour les employés
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

  // Raccourcis pour l'administration
  {
    path: 'administration',
    redirectTo: '/admin/statistics',
    pathMatch: 'full'
  },
  {
    path: 'statistiques',
    redirectTo: '/admin/statistics',
    pathMatch: 'full'
  },

  // Route 404
  { 
    path: '**', 
    redirectTo: '/login'
  }
];
