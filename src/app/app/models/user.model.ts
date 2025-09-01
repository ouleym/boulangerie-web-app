export interface User {
  id: number;
  nom: string;
  prenom: string;
  email: string;
  telephone: string | null;
  adresse: string;
  ville: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  roles: string[];
}

export interface AuthResponse {
  status: number;
  user: User;
  token: string;
}
