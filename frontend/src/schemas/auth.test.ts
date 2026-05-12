import { describe, expect, it } from 'vitest';
import { loginSchema, registerSchema } from './auth';

describe('loginSchema', () => {
  it('accepts a well-formed login payload', () => {
    const result = loginSchema.safeParse({ email: 'a@b.co', password: 'x' });
    expect(result.success).toBe(true);
  });

  it('rejects an invalid email', () => {
    const result = loginSchema.safeParse({ email: 'nope', password: 'x' });
    expect(result.success).toBe(false);
  });

  it('rejects an empty password', () => {
    const result = loginSchema.safeParse({ email: 'a@b.co', password: '' });
    expect(result.success).toBe(false);
  });
});

describe('registerSchema', () => {
  const valid = {
    name: 'Halil',
    email: 'halil@dehasoft.test',
    password: 'password123',
    password_confirmation: 'password123',
  };

  it('accepts a fully valid registration payload', () => {
    expect(registerSchema.safeParse(valid).success).toBe(true);
  });

  it('rejects passwords shorter than eight characters', () => {
    const result = registerSchema.safeParse({
      ...valid,
      password: 'short',
      password_confirmation: 'short',
    });
    expect(result.success).toBe(false);
  });

  it('rejects when the two passwords do not match', () => {
    const result = registerSchema.safeParse({ ...valid, password_confirmation: 'different' });
    expect(result.success).toBe(false);
    if (!result.success) {
      expect(result.error.issues[0]?.path).toEqual(['password_confirmation']);
    }
  });

  it('rejects names shorter than two characters', () => {
    const result = registerSchema.safeParse({ ...valid, name: 'A' });
    expect(result.success).toBe(false);
  });

  it('rejects malformed e-mail addresses', () => {
    const result = registerSchema.safeParse({ ...valid, email: 'not-an-email' });
    expect(result.success).toBe(false);
  });
});
