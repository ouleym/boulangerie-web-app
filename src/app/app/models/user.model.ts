export interface User {
  id: number;
  nom: string;
  prenom: string;
  email: string;
  telephone?: string;
  adresse?: string;
  ville?: string;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
  roles?: Role[];
  permissions?: Permission[];
}

export interface Role {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
}

export interface Permission {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
}

export interface AuthResponse {
  user: User;
  token?: string;
  message?: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
  remember?: boolean;
}

export interface RegisterData {
  nom: string;
  prenom: string;
  email: string;
  telephone?: string;
  adresse?: string;
  ville?: string;
  password: string;
  password_confirmation: string;
}
