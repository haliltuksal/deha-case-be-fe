import 'server-only';
import { laravel } from '@/server/http/laravel-client';
import type { ApiSuccess } from '@/types/api';
import type { LoginResponse, User } from '@/types/auth';

export interface RegisterInput {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface LoginInput {
  email: string;
  password: string;
}

export const authRepository = {
  async login(input: LoginInput): Promise<LoginResponse> {
    const response = await laravel<ApiSuccess<LoginResponse>>('/api/v1/auth/login', {
      method: 'POST',
      json: input,
    });
    return response.data;
  },

  async register(input: RegisterInput): Promise<LoginResponse> {
    const response = await laravel<ApiSuccess<LoginResponse>>('/api/v1/auth/register', {
      method: 'POST',
      json: input,
    });
    return response.data;
  },

  async logout(token: string): Promise<void> {
    await laravel<void>('/api/v1/auth/logout', {
      method: 'POST',
      token,
    });
  },

  async me(token: string): Promise<User> {
    const response = await laravel<ApiSuccess<User>>('/api/v1/auth/me', { token });
    return response.data;
  },
};
