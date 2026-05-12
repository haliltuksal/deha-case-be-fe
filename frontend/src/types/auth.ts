export interface User {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
  created_at: string;
}

export interface AuthTokens {
  access_token: string;
  token_type: 'bearer';
  expires_in: number;
}

export interface LoginResponse extends AuthTokens {
  user: User;
}
