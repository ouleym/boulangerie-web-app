// Représente une réponse générique de l’API
export interface ApiResponse<T> {
  status: number;   // ex: 200, 201, 400, 500
  message?: string; // optionnel
  data: T;          // la donnée renvoyée (un objet, une liste, etc.)
}

// Représente une réponse paginée
export interface PaginatedResponse<T> {
  data: T[];            // liste des éléments
  current_page: number; // page actuelle
  total: number;        // total d'éléments
  per_page: number;     // nombre par page
  last_page: number;    // nombre total de pages
}
