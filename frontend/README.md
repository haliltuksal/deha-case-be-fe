# Deha Storefront — Frontend

Next.js 15 storefront for the Dehasoft hiring case. Companion to the Laravel
backend at [`deha-backend`](https://github.com/haliltuksal/deha-backend).

The browser **never** talks to the Laravel API directly. Every backend call is
proxied through Next.js route handlers under `src/app/api/*`. JWT bearer
tokens issued by the backend live exclusively in HttpOnly cookies set by the
BFF layer; client-side JavaScript cannot read them.

---

## Architecture

```
┌──────────────┐       ┌────────────────────────────────┐       ┌──────────────┐
│   Browser    │──────▶│ Next.js (UI + BFF route hand.) │──────▶│ Laravel API  │
└──────────────┘       └────────────────────────────────┘       └──────────────┘
       │                            │                                    │
       │   no Authorization header  │   Authorization: Bearer <jwt>      │
       │   no LARAVEL_API_URL       │   X-Request-Id (AsyncLocalStorage) │
       │   no Laravel endpoints     │   token taken from HttpOnly cookie │
       ▼                            ▼                                    ▼
   browser only sees           server-only modules                   /api/v1/*
   `/api/...` paths            under src/server/* with               20+ endpoints
   on its network log          `import 'server-only'`
```

The split between **server-only** code (`src/server/*`) and **client-only**
code (`src/lib/api/*`) is enforced two ways:

1. `import 'server-only'` at the top of every server module breaks the build
   if it ever ends up in the client bundle.
2. ESLint's `no-restricted-imports` rule blocks `@/server/*` and
   `@/config/env` imports from `src/components/**`, `src/app/**/_components/**`,
   `src/lib/api/**`, and `src/hooks/**`. BFF route handlers
   (`src/app/api/**/route.ts`) cannot import `@/lib/api/*` either.

Pages and layouts (`src/app/**/page.tsx`, `src/app/**/layout.tsx`) are exempt
because they run on the server by default; client interactivity lives in
the per-route `_components/` folders.

---

## Tech stack

| Layer            | Choice                                                    | Why                                                                      |
| ---------------- | --------------------------------------------------------- | ------------------------------------------------------------------------ |
| Framework        | Next.js 15 (App Router)                                   | Case requires Next.js; App Router gives route handlers ideal for the BFF |
| Runtime          | Node.js 20 LTS                                            | Stable, widely deployed; pinned in `engines` and the Dockerfile          |
| Language         | TypeScript 5 (strict + `noUncheckedIndexedAccess`)        | Backend ships PHPStan level 8; frontend matches the rigour               |
| Styling          | Tailwind CSS 3 + shadcn/ui (new-york)                     | Stable Tailwind line; shadcn primitives are owned, vendor-locked-out     |
| Forms            | react-hook-form + Zod                                     | Same Zod schema validates both the form and the BFF body                 |
| Server data      | Native `fetch` in server components and BFF               | No extra HTTP client; cache integration is built in                      |
| Client mutations | tiny `apiClient` wrapper that throws typed `ApiError`     | UI branches on stable backend codes (`ERR_INSUFFICIENT_STOCK`, …)        |
| Tests            | Vitest + Testing Library + jsdom                          | 25 unit/component tests for helpers and rendering                        |
| Lint/format      | ESLint 9 (flat config) + Prettier 3                       | Run `pnpm qa` locally or in CI before merging                            |
| Tooling          | pnpm 10                                                   | Fast, deterministic install; pinned via `engines`                        |
| Containers       | Multi-stage Dockerfile + docker-compose                   | Reviewer runs `docker compose up` with no Node toolchain                 |

---

## Quickstart

### With Docker (zero local toolchain)

```bash
# 1. Bring up the backend first (in the deha-backend repo)
cd ../backend
make up && make currency-fetch    # boots Laravel + warms exchange rates

# 2. Then the frontend
cd ../frontend
docker compose up --build
```

The storefront is now available at <http://localhost:3000>. The compose file
points the BFF at `host.docker.internal:8080` so the container can reach the
backend running on the host.

### Locally with pnpm

```bash
corepack enable && corepack prepare pnpm@10 --activate

pnpm install
cp .env.example .env.local
# .env.local is already pointed at http://localhost:8080

pnpm dev
```

---

## Demo credentials

The backend seeds two accounts with the password **`password`**:

| Role     | Email                    |
| -------- | ------------------------ |
| Admin    | `admin@dehasoft.test`    |
| Customer | `customer@dehasoft.test` |

Admin gets the **Admin Paneli** entry in the user menu and can complete other
users' orders. Customer can browse, manage cart, place and cancel orders.

---

## Manual demo flow

A reviewer can validate every system requirement in under five minutes:

1. **Anonymous browsing** — visit `/`, switch currency in the header
   (`TRY → USD → EUR`), search for a product, paginate.
2. **Register** — `/register`, fill the form. After submit you are auto-logged
   in (cookie set by BFF, no token in response body — verifiable in devtools)
   and redirected to `/`.
3. **Cart** — click _Sepete Ekle_ on a product card. Header badge increments.
   Visit `/cart`, change quantities (try exceeding stock to see
   `ERR_INSUFFICIENT_STOCK` surfaced), remove a line, clear the cart.
4. **Checkout** — refill the cart, click _Siparişi Onayla_ in the cart
   summary, then _Siparişi Tamamla_ on the checkout page. You land on
   `/orders/<id>` in the _Beklemede_ state and the cart badge resets to zero.
5. **Cancel** — on the new order, _Siparişi İptal Et_ with confirmation. The
   status flips to _İptal Edildi_ and the backend restores stock.
6. **Admin** — log out, log in as `admin@dehasoft.test`. The user menu now
   exposes _Admin Paneli_. Visit `/admin/products` to create, edit, delete
   products. Open a non-completed order and _Tamamla_ it.
7. **Auth boundary** — log out, then visit `/cart` directly. Middleware
   bounces you to `/login?next=/cart`. Log in, you land back on the cart.
8. **Health probe** — `curl http://localhost:3000/api/health` returns the
   composite frontend/backend status.

---

## Scripts

| Command             | Purpose                                |
| ------------------- | -------------------------------------- |
| `pnpm dev`          | Start the dev server (Turbopack)       |
| `pnpm build`        | Production build (standalone output)   |
| `pnpm start`        | Run the production build               |
| `pnpm lint`         | ESLint (`--max-warnings 0`)            |
| `pnpm lint:fix`     | Apply ESLint autofixes                 |
| `pnpm typecheck`    | `tsc --noEmit`                         |
| `pnpm format`       | Apply Prettier                         |
| `pnpm format:check` | Check formatting only                  |
| `pnpm test`         | Run the Vitest suite                   |
| `pnpm test:watch`   | Watch-mode Vitest                      |
| `pnpm qa`           | typecheck + lint + format:check + test |

---

## Project layout

Components and server actions live next to the route that consumes them
(`_components/`, `_actions/`). Only components used from multiple routes
are promoted to `src/components/`.

```
src/
├── app/                          routes (App Router) + BFF route handlers
│   ├── (auth)/                   /login, /register, auth-only layout
│   │   ├── login/_components/    login-form
│   │   └── register/_components/ register-form
│   │
│   ├── (shop)/                   storefront + admin, header layout
│   │   ├── _components/          header, user-menu, currency-switcher,
│   │   │                         cart-indicator, logout-button,
│   │   │                         product-list, product-card
│   │   ├── _actions/             setCurrencyPreference server action
│   │   ├── page.tsx              product catalog (homepage)
│   │   ├── products/[id]/_components/  product-detail
│   │   ├── cart/_components/     item-row, summary, stepper, clear, remove, empty
│   │   ├── checkout/_components/ checkout-summary, place-order-button
│   │   ├── orders/
│   │   │   ├── _components/      row, status-badge, list-empty
│   │   │   └── [id]/_components/ detail, item-row, cancel/complete buttons
│   │   └── admin/
│   │       └── products/_components/ admin-product-row, delete, product-form
│   │
│   ├── api/                      BFF route handlers
│   │   ├── auth/                 register, login, logout, me
│   │   ├── products/             GET list/detail (public) + admin CRUD
│   │   ├── cart/                 cart show/clear + items add/update/remove
│   │   ├── orders/               list/checkout + detail + cancel + complete
│   │   └── health/               composite health probe
│   ├── error.tsx                 root error boundary
│   └── layout.tsx                root layout (metadata, sonner)
│
├── server/                       SERVER-ONLY (`import 'server-only'`)
│   ├── http/                     laravel-client, http-error, request-id,
│   │                             request-context (AsyncLocalStorage)
│   ├── auth/                     cookie, session, guard
│   ├── bff/                      handle-error, route-helpers, parse-body, parse-id
│   ├── repositories/             auth, product, cart, order
│   └── preferences/              currency reader (cookie)
│
├── components/                   shared client components (multi-route)
│   ├── ui/                       shadcn primitives + confirm-action-button,
│   │                             pagination-nav
│   ├── products/                 product-search, empty-state
│   └── cart/                     add-to-cart-button
│
├── lib/                          isomorphic + client helpers
│   ├── api/                      client (`import 'client-only'`, browser → /api/*)
│   ├── currency/                 format (Intl.NumberFormat per currency)
│   ├── errors/                   ApiError class + Turkish message map
│   ├── forms/                    map-api-errors (Zod field errors → RHF)
│   ├── utils/                    cn, format-date, parse-int, sanitise-next
│   └── auth-constants.ts         shared with edge middleware
│
├── hooks/                        reusable client hooks (use-api-mutation)
├── schemas/                      Zod (auth, cart, product)
├── types/                        TS contract types
├── config/                       Zod-validated env (lazy proxy)
└── middleware.ts                 Edge auth-guard (cookie presence)
```

### Shared client building blocks

Common UI patterns are factored into reusable primitives so that mutation
buttons and forms stay declarative:

- **`useApiMutation`** (`hooks/`) — one hook handles `useTransition`, the
  `apiClient` call, success toasts, optional navigation/refresh, and the
  `ApiError → translated toast` mapping. Buttons supply only what differs.
- **`<ConfirmActionButton>`** (`components/ui/`) — composes an
  `AlertDialog` with `useApiMutation` for any guarded mutation
  (`Delete product`, `Cancel order`, …). Each button file becomes ~15 lines.
- **`<PaginationNav>`** (`components/ui/`) — single pagination component
  used by all three list pages; callers supply a `buildHref(page)` callback.
- **`handleFormSubmitError`** (`lib/forms/`) — maps an `ERR_VALIDATION`
  envelope's field errors onto react-hook-form, falling back to a global
  toast when no field matches.
- **`sanitiseNextPath`** (`lib/utils/`) — shared between the middleware and
  the login form; rejects external hosts and the auth pages themselves.

---

## Environment variables

| Variable             | Required | Description                                             |
| -------------------- | -------- | ------------------------------------------------------- |
| `LARAVEL_API_URL`    | yes      | Backend base URL (server-only, never bundled)           |
| `COOKIE_NAME`        | no       | Auth cookie name (default `deha_token`)                 |
| `COOKIE_DOMAIN`      | no       | Cookie domain (default: current host)                   |
| `REQUEST_TIMEOUT_MS` | no       | Outbound HTTP timeout against Laravel (default `15000`) |

Variables prefixed with `NEXT_PUBLIC_*` are deliberately not used so the
backend URL and any other server-side configuration can never end up in the
client bundle.

---

## Security checklist (case requirements)

| Requirement                          | Implementation                                                                                                                                                                                                                                                        |
| ------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **API Endpoint Exposure**            | Browser only sees `/api/*` paths. Laravel endpoint names (`/api/v1/cart/items/...`) never appear in the client bundle.                                                                                                                                                |
| **Doğrudan Backend Erişimi**         | `LARAVEL_API_URL` is read inside `src/server/*` only. `import 'server-only'` plus an ESLint rule blocks accidental client imports. The BFF route handler is the only network seam.                                                                                    |
| **Token Çalınması / Yanlış Saklama** | JWT lives in an HttpOnly + SameSite=Lax cookie set by the BFF on login/register/refresh. Response bodies never echo the token. JS cannot read `document.cookie` for it.                                                                                               |
| **Yetkisiz Erişim**                  | Three layers of defense: (1) Edge middleware checks cookie presence on protected prefixes, (2) Admin layout asserts `is_admin` server-side, (3) BFF route handlers re-verify auth and admin via `withAuth`/`withAdmin`/`withAdminToken` before forwarding to Laravel. |
| **CORS Yanlış Yapılandırması**       | The browser never makes cross-origin requests, so CORS is structurally impossible to misconfigure on the client side. The Laravel backend's CORS allow-list is the only surface.                                                                                      |

Additional hardening:

- `next.config.ts` sets `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`,
  `Referrer-Policy: strict-origin-when-cross-origin`, and a `Permissions-Policy`
  that disables camera/microphone/geolocation.
- Login redirects sanitise the `next` query parameter to relative paths only
  to prevent open redirects (`https://evil.com` is rejected).
- The auth cookie expires 30 seconds before the JWT does so a stale cookie
  can never out-live the token it carries.
- Destructive UI actions (clear cart, cancel order, delete product) require
  an AlertDialog confirmation.

---

## Architectural decisions

These are the choices a reviewer might wonder about; each is intentional.

- **BFF route handlers per Laravel endpoint.** Even pages that fetch in server
  components have `/api/*` mirrors so a curl-based reviewer can verify the
  proxy works end-to-end. The repositories under `src/server/repositories/`
  are the single seam both routes and pages call.
- **`withToken` vs `withAuth`.** Cart and order mutations only need the bearer
  token forwarded; they skip the `/auth/me` round-trip that `withAuth`
  performs (the upstream call validates the token anyway). `withAuth` is for
  endpoints that need the resolved user object. `withAdminToken` combines
  admin enforcement with token forwarding.
- **Cookie-only currency persistence.** A separate `deha_currency` cookie
  carries the selected currency. URL `?currency=` was considered but dropped:
  it would have required `useSearchParams` Suspense scaffolding throughout
  the layout, and the case spec has no shareable-link requirement.
- **Snapshot pattern for orders.** Order items carry name + price snapshots
  from the time of purchase. When a product is later deleted the row still
  renders (italic + "ürün artık katalogda değil"); this matches what the
  Laravel side stores via `FK SET NULL` on `order_items.product_id`.
- **No optimistic UI for cart edits.** Quantity stepper and remove buttons
  use a `useTransition` pending state and call `router.refresh()` on
  success. This avoids the rollback complexity that an optimistic UI would
  require for `ERR_INSUFFICIENT_STOCK` (and the case has no requirement
  for sub-100ms perceived latency).
- **Auth refresh is endpoint-only, not auto-invoked.** A 401 from any BFF
  route bubbles up as `ERR_UNAUTHENTICATED`; the affected client island
  redirects to `/login?next=<path>`. A token-refresh BFF route exists for
  future "extend session" UX.
- **Lazy env validation.** `src/config/env.ts` exports a `Proxy` that runs
  `envSchema.safeParse` on first property read. This keeps `next build`
  green even when `LARAVEL_API_URL` is missing at build time, while still
  throwing loudly on the first runtime access.
- **`typedRoutes` is disabled.** Re-enabling would require `as Route` casts on
  every dynamic `/products/${id}` style link. The marginal type safety is
  not worth the friction; ESLint and TS strict already catch dead imports.

---

## Test plan

### Automated (`pnpm test`)

| File                                            | Coverage                                                                               |
| ----------------------------------------------- | -------------------------------------------------------------------------------------- |
| `lib/errors/error-messages.test.ts`             | All 18 backend error codes have a Turkish message; map size matches the canonical list |
| `lib/currency/format.test.ts`                   | TRY/USD/EUR each format with the right symbol, separator, and decimal precision        |
| `schemas/auth.test.ts`                          | Login + register Zod schemas accept valid payloads and reject every guarded edge       |
| `app/(shop)/orders/_components/order-status-badge.test.tsx` | Each `OrderStatus` renders the correct Turkish label                                   |
| `app/(shop)/_components/product-card.test.tsx`              | Currency switching updates the displayed price; out-of-stock disables the CTA          |

`pnpm qa` runs typecheck + lint + format:check + tests. Run it locally
before pushing — CI runs the same command.

### Manual

See **Manual demo flow** above. Each step exercises one of the case spec's
five system requirements.

---

## Deliberate omissions

These are out of scope for the case and intentionally left out:

- Playwright / browser-driven E2E tests (manual demo flow above is the
  contract for review).
- Service worker / offline support.
- Product image upload and rendering.
- Address management, shipping calculation, payment processor.
- Email verification and forgot-password flow.
- Network-level backend isolation (the case is a code-quality exercise,
  not an infrastructure exercise).
- Auto token refresh on 401 (deliberately left as `/login` redirect; see
  Architectural decisions).
- Wishlist, product reviews, categories.

---

## Known limitations

- A user whose token is server-side blacklisted but whose cookie is still in
  the browser will see a confused state: header renders as anonymous (because
  `/auth/me` returns 401), but middleware still treats them as logged in
  (because the cookie is present). The cookie's `Max-Age = expires_in - 30s`
  keeps this window narrow in practice. A production-grade fix would
  validate the token in middleware or expose a `/api/auth/clear` endpoint.
- The Vitest `describe`/`it` suite is intentionally lean. A larger codebase
  would add component tests for forms (LoginForm, ProductForm) and BFF
  route handlers (mocking the upstream). For this case the focus stayed on
  the helpers most likely to drift silently (currency formatter, error
  message map) plus a couple of representative UI smoke tests.

---

## Backend integration

The BFF mirrors the Laravel API. Backend repository:
<https://github.com/haliltuksal/deha-backend>.

| BFF route                           | Backend route                          | Notes                                  |
| ----------------------------------- | -------------------------------------- | -------------------------------------- |
| `POST /api/auth/register`           | `POST /api/v1/auth/register`           | Sets cookie, drops token from response |
| `POST /api/auth/login`              | `POST /api/v1/auth/login`              | Same                                   |
| `POST /api/auth/logout`             | `POST /api/v1/auth/logout`             | Clears cookie unconditionally          |
| `POST /api/auth/refresh`            | `POST /api/v1/auth/refresh`            | Reissues cookie with new JWT           |
| `GET /api/auth/me`                  | `GET /api/v1/auth/me`                  | Resolves the current user              |
| `GET /api/products`                 | `GET /api/v1/products`                 | Public, paginated, search              |
| `POST /api/products`                | `POST /api/v1/products`                | Admin                                  |
| `GET /api/products/:id`             | `GET /api/v1/products/:id`             | Public                                 |
| `PUT /api/products/:id`             | `PUT /api/v1/products/:id`             | Admin                                  |
| `DELETE /api/products/:id`          | `DELETE /api/v1/products/:id`          | Admin                                  |
| `GET /api/cart`                     | `GET /api/v1/cart`                     | Bearer                                 |
| `DELETE /api/cart`                  | `DELETE /api/v1/cart`                  | Bearer                                 |
| `POST /api/cart/items`              | `POST /api/v1/cart/items`              | Bearer                                 |
| `PUT /api/cart/items/:productId`    | `PUT /api/v1/cart/items/:productId`    | Bearer                                 |
| `DELETE /api/cart/items/:productId` | `DELETE /api/v1/cart/items/:productId` | Bearer                                 |
| `GET /api/orders`                   | `GET /api/v1/orders`                   | Bearer                                 |
| `POST /api/orders`                  | `POST /api/v1/orders`                  | Bearer (checkout from cart)            |
| `GET /api/orders/:id`               | `GET /api/v1/orders/:id`               | Bearer                                 |
| `POST /api/orders/:id/cancel`       | `POST /api/v1/orders/:id/cancel`       | Bearer (owner of pending order)        |
| `POST /api/orders/:id/complete`     | `POST /api/v1/orders/:id/complete`     | Admin                                  |
| `GET /api/health`                   | `GET /api/v1/health`                   | Public composite probe                 |

The error envelope shape is identical on both sides:

```json
{ "message": "...", "code": "ERR_*", "details": {}, "errors": {} }
```

Stable error codes are listed in `src/types/api.ts`; the Turkish translation
table is in `src/lib/errors/error-messages.ts`.
