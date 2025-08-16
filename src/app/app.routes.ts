import { Routes } from '@angular/router';
import { LoginComponent } from './app/auth/login/login.component';
import { RegisterComponent } from './app/auth/register/register.component';
import { ProduitsListComponent } from './app/produits/list/list.component';
import { ProduitsDetailComponent } from './app/produits/detail/detail.component';
import { ProduitsFormComponent } from './app/produits/form/form.component';
import { authGuard } from './app/core/auth.guard';
import { adminGuard } from './app/core/admin.guard';
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
  // Routes publiques
  { path: '', redirectTo: '/login', pathMatch: 'full' },
  { path: 'login', component: LoginComponent, title: 'Connexion' },
  { path: 'register', component: RegisterComponent, title: 'Inscription' },


  // Routes protégées - Authentification requise
  {
    path: 'dashboard',
    canActivate: [authGuard],
    children: [
      { path: '', redirectTo: 'client', pathMatch: 'full' },
      { path: 'client', component: ClientComponent, title: 'Mon Espace Client' },
      { path: 'employe', component: EmployeComponent, title: 'Espace Employé' },
      { 
        path: 'admin', 
        component: AdminComponent, 
        canActivate: [adminGuard],
        title: 'Administration' 
      }
    ]
  },

  {
    path: 'profile',
    component: ProfileComponent,
    canActivate: [authGuard],
    title: 'Mon Profil'
  },

  {
    path: 'mes-commandes',
    canActivate: [authGuard],
    children: [
      { path: '', component: CommandesListComponent, title: 'Mes Commandes' },
      { path: ':id', component: CommandesDetailComponent, title: 'Détail Commande' },
      { path: ':id/status', component: CommandesStatusComponent, title: 'Suivi Commande' }
    ]
  },

  // Panier et checkout
  {
    path: 'panier',
    component: CommandesPanierComponent,
    canActivate: [authGuard],
    title: 'Mon Panier'
  },
  {
    path: 'checkout',
    component: CommandesCheckoutComponent,
    canActivate: [authGuard],
    title: 'Finaliser Commande'
  },

  // Support client
  {
    path: 'support',
    canActivate: [authGuard],
    children: [
      { path: '', component: ConversationsComponent, title: 'Support Client' },
      { path: 'chat', component: ChatComponent, title: 'Chat' },
      { path: 'conversation/:id', component: MessagesComponent, title: 'Conversation' }
    ]
  },

  // Routes Admin - Protection renforcée
  {
    path: 'admin',
    canActivate: [authGuard, adminGuard],
    children: [
      // Gestion des utilisateurs
      {
        path: 'users',
        children: [
          { path: '', component: UsersListComponent, title: 'Gestion Utilisateurs' },
          { path: 'create', component: UsersFormComponent, title: 'Créer Utilisateur' },
          { path: ':id/edit', component: UsersFormComponent, title: 'Modifier Utilisateur' }
        ]
      },

      // Gestion des catégories
      {
        path: 'categories',
        children: [
          { path: '', component: CategoriesListComponent, title: 'Gestion Catégories' },
          { path: 'create', component: CategoriesFormComponent, title: 'Créer Catégorie' },
          { path: ':id', component: CategoriesDetailComponent, title: 'Détail Catégorie' },
          { path: ':id/edit', component: CategoriesFormComponent, title: 'Modifier Catégorie' }
        ]
      },

      // Gestion des produits (admin)
      {
        path: 'produits',
        children: [
          { path: '', component: ProduitsListComponent, title: 'Gestion Produits' },
          { path: 'create', component: ProduitsFormComponent, title: 'Créer Produit' },
          { path: ':id/edit', component: ProduitsFormComponent, title: 'Modifier Produit' },
          { path: ':id', component: ProduitsDetailComponent, title: 'Détails Produit' }
        ]
      },

      // Gestion des promotions
      {
        path: 'promotions',
        children: [
          { path: '', component: PromotionsListComponent, title: 'Gestion Promotions' },
          { path: 'create', component: PromotionsFormComponent, title: 'Créer Promotion' },
          { path: ':id', component: PromotionsDetailComponent, title: 'Détail Promotion' },
          { path: ':id/edit', component: PromotionsFormComponent, title: 'Modifier Promotion' }
        ]
      },

      // Gestion des commandes (admin)
      {
        path: 'commandes',
        children: [
          { path: '', component: CommandesListComponent, title: 'Toutes les Commandes' },
          { path: ':id', component: CommandesDetailComponent, title: 'Détail Commande Admin' }
        ]
      },

      // Support admin
      {
        path: 'support',
        children: [
          { path: '', component: ConversationsComponent, title: 'Gestion Support' },
          { path: 'conversation/:id', component: MessagesComponent, title: 'Conversation Admin' }
        ]
      }
    ]
  },

  // Route 404
  { path: '**', redirectTo: '/login' }
];