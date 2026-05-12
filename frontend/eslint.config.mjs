import { dirname } from 'path';
import { fileURLToPath } from 'url';
import { FlatCompat } from '@eslint/eslintrc';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const compat = new FlatCompat({
  baseDirectory: __dirname,
});

const eslintConfig = [
  ...compat.extends('next/core-web-vitals', 'next/typescript'),
  {
    rules: {
      '@typescript-eslint/no-unused-vars': [
        'error',
        {
          argsIgnorePattern: '^_',
          varsIgnorePattern: '^_',
          caughtErrorsIgnorePattern: '^_',
        },
      ],
      '@typescript-eslint/consistent-type-imports': [
        'error',
        { prefer: 'type-imports', fixStyle: 'separate-type-imports' },
      ],
      '@typescript-eslint/no-explicit-any': 'error',
      'no-console': ['warn', { allow: ['warn', 'error'] }],
    },
  },
  {
    files: [
      'src/components/**/*.{ts,tsx}',
      'src/app/**/_components/**/*.{ts,tsx}',
      'src/lib/api/**/*.{ts,tsx}',
      'src/hooks/**/*.{ts,tsx}',
    ],
    rules: {
      'no-restricted-imports': [
        'error',
        {
          patterns: [
            {
              group: ['@/server/*', '@/config/env'],
              message:
                'Server-only modules cannot be imported from client code. Use BFF route handlers under src/app/api/ instead.',
            },
          ],
        },
      ],
    },
  },
  {
    files: ['src/app/api/**/route.ts'],
    rules: {
      'no-restricted-imports': [
        'error',
        {
          patterns: [
            {
              group: ['@/lib/api/*'],
              message:
                'BFF route handlers must not import the client-only fetch wrapper.',
            },
          ],
        },
      ],
    },
  },
  {
    ignores: ['.next/**', 'node_modules/**', 'public/**', 'next-env.d.ts'],
  },
];

export default eslintConfig;
