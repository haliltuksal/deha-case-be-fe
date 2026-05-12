# Dehasoft Case — Backend API

Laravel 12 e-commerce API for the Dehasoft hiring case study. Issues JWT-protected endpoints for user accounts, products, carts, and orders, with multi-currency display backed by daily TCMB exchange rates and a transactional checkout flow guarded by pessimistic locking and a State-pattern order machine.

> **Architectural note:** this service is intentionally not exposed to the browser. It is consumed only by the Next.js BFF proxy in the companion `frontend` repo. The proxy holds the API URL, attaches the JWT bearer token from an httpOnly cookie, and forwards every call.

---

## Stack

| Layer | Choice |
|-------|--------|
| Language | PHP 8.3 |
| Framework | Laravel 12 |
| Database | MySQL 8 |
| Cache / queue / session | Redis 7 |
| Web server | Nginx 1.27 (FastCGI → PHP-FPM) |
| Auth | JWT (`php-open-source-saver/jwt-auth`), validated server-side only |
| Currency provider | TCMB (Strategy pattern, swappable) |
| Tests | Pest 3 (135 tests, 487 assertions) |
| Static analysis | Larastan (PHPStan level 8) |
| Code style | Laravel Pint with `declare(strict_types=1)` enforced |
| API docs | Scribe (HTML + Postman + OpenAPI) |
| Containers | Plain `docker-compose` (no Sail) |

---

## Architecture

```
┌────────────┐      ┌─────────────────────┐      ┌─────────────────┐
│  Browser   │ ───► │ Next.js (UI + BFF)  │ ───► │  Laravel API    │
│            │      │   /api/*  proxy     │      │  (this repo)    │
└────────────┘      └─────────────────────┘      └─────────────────┘
                              │                            │
                       httpOnly cookie               JWT Bearer
                       (frontend-managed)            (server-only)
```

The browser never sees the Laravel base URL. The BFF stores the JWT in an httpOnly cookie that JavaScript cannot read, attaches it to every proxied request as a `Authorization: Bearer …` header, and forwards the response untouched.

### Internal layout

```
app/
├── Actions/                 single-purpose invokable units
├── Console/Commands/        currency:fetch
├── Contracts/               repository + service interfaces
├── DTOs/                    readonly request/response data carriers
├── Enums/                   Currency, OrderStatus
├── Exceptions/Domain/       per-module typed exceptions extending ApiException
├── Http/
│   ├── Controllers/Api/V1/  thin orchestrators, depend on services
│   ├── Middleware/          AssignRequestId, EnsureUserIsAdmin
│   ├── Requests/            FormRequests with explicit validation
│   └── Resources/V1/        JSON shaping, multi-currency display
├── Models/                  Eloquent models (User, Product, Cart, Order, ExchangeRate, …)
├── OrderStates/             State pattern: Pending / Completed / Cancelled
├── Providers/               JWT + currency + repository bindings
├── Repositories/Eloquent/   data-access boundary
├── Services/                business logic orchestration
└── Support/                 ApiResponse trait, ApiException base
```

### Patterns at a glance

| Pattern | Where | Why |
|---|---|---|
| **Repository** | every aggregate (User, Product, Cart, Order, ExchangeRate) | Dependency Inversion; lets services depend on contracts |
| **Action** | one verb per action (`CreateOrderFromCartAction`, …) | single responsibility, reusable from controllers + commands |
| **Service** | per-domain (`AuthService`, `OrderService`, …) | orchestrate the actions for the controller |
| **Strategy** | `ExchangeRateProviderInterface` + `TcmbExchangeRateProvider` | swap the upstream rate source without touching consumers |
| **State** | `OrderState` + `Pending/Completed/Cancelled` + `OrderStateTransitioner` | domain-correct status transitions, illegal transitions raise typed exceptions |
| **Snapshot** | `order_items` capture name/price/currency at order time | history survives admin product mutations and deletes (FK is SET NULL) |
| **Read-through cache** | `ExchangeRateService` (Redis → DB → throw) | resilient to upstream outages without hitting TCMB on every read |
| **Pessimistic lock** | order checkout + cancel | prevents concurrent over-sell of stock |

---

## Quick start

```bash
# 1. Copy the env file
cp .env.example .env

# 2. Build images and start the stack
make up

# 3. Install dependencies, set the app key, migrate + seed, fetch rates
make install
make key
make fresh
make currency-fetch

# 4. Verify the API responds and the test suite is green
curl -s http://localhost:8080/api/v1/health
make test
```

The API will be available at <http://localhost:8080>. The browseable docs are at <http://localhost:8080/docs>.

> Reviewer tip: `make fresh` truncates the database and re-seeds the demo accounts and product catalog. `make currency-fetch` pulls the latest TCMB rates and is required before any product, cart, or order endpoint can render multi-currency totals.

---

## Demo credentials

`make fresh` seeds two demo users and ten products covering all three base currencies.

| Email | Password | Role |
|---|---|---|
| `admin@dehasoft.test` | `password` | admin (can manage products, complete orders) |
| `customer@dehasoft.test` | `password` | regular user |

> **Rotate these credentials before any non-demo deployment.** Replace the seeder rows with environment-driven values, or delete the seed entirely and create the admin manually.

---

## Make targets

`make help` prints every target. The most useful ones:

| Target | What it does |
|--------|--------------|
| `make up` / `make down` | Start / stop the docker stack |
| `make build` / `make rebuild` | Build images (rebuild = no cache) |
| `make sh` | Open a shell in the app container |
| `make logs` | Tail every container's stdout |
| `make migrate` | Run pending migrations |
| `make fresh` | Drop everything and re-seed (admin + customer + 10 products) |
| `make seed` | Re-run seeders without dropping schema |
| `make currency-fetch` | Pull latest TCMB rates and warm the cache |
| `make test` | Pest suite |
| `make test-coverage` | Pest with coverage |
| `make lint` / `make format` | Pint check / fix |
| `make analyse` | Larastan (PHPStan level 8) |
| `make qa` | lint + analyse + test (CI gate) |
| `make scribe` | Regenerate the API docs |
| `make route-list` | Print every registered route |
| `make cache-clear` | Clear every Laravel cache (config, routes, views) |

---

## API documentation

Two ways to read the docs:

**Booted (interactive UI + try-it-out)** — once the stack is running:

- **HTML UI**: <http://localhost:8080/docs>
- **Postman collection** (download): <http://localhost:8080/docs.postman>
- **OpenAPI 3.0 spec** (download): <http://localhost:8080/docs.openapi>

**Static (no boot required)** — checked into the repo for offline review:

- [`docs/api/postman_collection.json`](docs/api/postman_collection.json) — drop into Postman/Insomnia
- [`docs/api/openapi.yaml`](docs/api/openapi.yaml) — open in Swagger UI / Redoc / Stoplight

The collection covers 21 endpoints across five sections: Meta, Auth, Products, Cart, Orders. Annotations include parameter docs and response examples for both happy paths and edge cases.

To regenerate the docs after editing controller annotations:

```bash
make scribe
# Then refresh the static copy if you want it in the repo:
cp storage/app/private/scribe/collection.json docs/api/postman_collection.json
cp storage/app/private/scribe/openapi.yaml docs/api/openapi.yaml
```

### Endpoint summary

| Method | URL | Auth |
|--------|-----|------|
| `GET` | `/api/v1/health` | open |
| `POST` | `/api/v1/auth/register` | open (rate-limited 5/min/IP) |
| `POST` | `/api/v1/auth/login` | open (rate-limited 5/min/IP) |
| `POST` | `/api/v1/auth/logout` | bearer |
| `POST` | `/api/v1/auth/refresh` | bearer |
| `GET` | `/api/v1/auth/me` | bearer |
| `GET` | `/api/v1/products` | open |
| `GET` | `/api/v1/products/{id}` | open |
| `POST` | `/api/v1/products` | bearer + admin |
| `PUT` | `/api/v1/products/{id}` | bearer + admin |
| `DELETE` | `/api/v1/products/{id}` | bearer + admin |
| `GET` | `/api/v1/cart` | bearer |
| `DELETE` | `/api/v1/cart` | bearer |
| `POST` | `/api/v1/cart/items` | bearer |
| `PUT` | `/api/v1/cart/items/{productId}` | bearer |
| `DELETE` | `/api/v1/cart/items/{productId}` | bearer |
| `GET` | `/api/v1/orders` | bearer |
| `POST` | `/api/v1/orders` | bearer |
| `GET` | `/api/v1/orders/{id}` | bearer |
| `POST` | `/api/v1/orders/{id}/cancel` | bearer |
| `POST` | `/api/v1/orders/{id}/complete` | bearer + admin |

Authenticated endpoints share the `api` rate limiter (60 requests/minute, keyed by user id).

---

## Response envelope

**Success:**

```json
{ "data": { ... } }
```

Paginated success additionally carries `meta` and `links` blocks (Laravel's standard resource collection shape).

**Error:**

```json
{
  "message": "Human-readable summary",
  "code": "ERR_…",
  "details": { "...optional structured payload..." },
  "errors": { "field": ["...optional validation messages..."] }
}
```

`code` is the stable, machine-readable identifier — clients should switch on it rather than parsing `message`.

---

## Error code catalog

| Code | Status | When |
|------|--------|------|
| `ERR_VALIDATION` | 422 | FormRequest validation failed; `errors` map supplied |
| `ERR_INVALID_CREDENTIALS` | 401 | `POST /auth/login` could not match the email + password |
| `ERR_UNAUTHENTICATED` | 401 | Missing, malformed, expired, or blacklisted token (the auth:api middleware deliberately collapses every token failure into one opaque code) |
| `ERR_UNAUTHORIZED` | 403 | Authenticated user lacks the required role (e.g. admin gate) |
| `ERR_NOT_FOUND` | 404 | Either the route does not exist or the requested resource was not found |
| `ERR_METHOD_NOT_ALLOWED` | 405 | Wrong HTTP verb for the route |
| `ERR_TOO_MANY_REQUESTS` | 429 | Rate limit exceeded (auth: 5/min/IP, api: 60/min/user) |
| `ERR_INSUFFICIENT_STOCK` | 422 | Cart-add or order-create would push a line past available stock; `details` carries `product_id`, `requested`, `available` |
| `ERR_EMPTY_CART` | 422 | `POST /orders` against an empty cart |
| `ERR_INVALID_ORDER_TRANSITION` | 422 | Illegal status transition (e.g. cancel a completed order); `details` carries `current_status` and `attempted_action` |
| `ERR_TOKEN_EXPIRED` | 401 | JWT TTL elapsed (raised at the service layer, not the auth middleware) |
| `ERR_TOKEN_BLACKLISTED` | 401 | Token was invalidated by an earlier `/logout` |
| `ERR_TOKEN_INVALID` | 401 | Token signature does not verify (raised outside the middleware) |
| `ERR_TOKEN_ABSENT` | 401 | Generic JWT package fallback |
| `ERR_EXCHANGE_PROVIDER_FAILED` | 502 | TCMB unreachable or returned malformed XML |
| `ERR_EXCHANGE_RATE_UNAVAILABLE` | 503 | Cache and database both empty for the requested currency |
| `ERR_HTTP` | varies | Generic Symfony `HttpException` fallback |
| `ERR_INTERNAL` | 500 | Unhandled `Throwable` — `debug` block included only when `APP_DEBUG=true` |

---

## Observability

- **`X-Request-Id` header** — every request carries a correlation id (caller-supplied if sane, generated UUID otherwise) that is mirrored on the response and added to every log entry's context. The Next.js BFF should forward the same id end-to-end so a single request can be traced across services.
- **Per-domain log channels** under `storage/logs/`:
  - `auth-YYYY-MM-DD.log` — login attempts (success info, failure warning; never the password)
  - `currency-YYYY-MM-DD.log` — TCMB fetch results and provider failures
  - `order-YYYY-MM-DD.log` — created / cancelled / completed order events
  - `laravel-YYYY-MM-DD.log` — anything else
- **`/api/v1/health`** — liveness probe that pings MySQL and Redis. Returns 200 + per-service `ok` when both reachable, otherwise 503 + the failing service flagged `down`.

---

## Testing

```bash
make test           # full Pest suite (135 tests, ~1s)
make test-coverage  # with coverage report
make qa             # lint + analyse + test (CI-style gate)
```

- Pest 3 with `RefreshDatabase` per test that mutates state.
- SQLite in-memory database for the test runner (configured in `phpunit.xml`).
- HTTP calls mocked via `Http::fake()` so the TCMB provider tests run offline.
- Cross-cutting concerns covered: stock locking, transactional rollback, order state matrix, ownership guards, rate limiting, request-id normalisation, exception handler shape.

The Larastan baseline (`phpstan-baseline.neon`) freezes a small set of Pest DSL signatures that the static analyser cannot model (`$this->get`, `$this->postJson`, mocking-related types). Application code remains at zero PHPStan errors at level 8.

---

## Beyond the case scope

Production-readiness checklist for a non-demo deployment:

- [ ] Set `APP_DEBUG=false` so stack traces never reach clients
- [ ] Rotate `APP_KEY` (`php artisan key:generate`) and `JWT_SECRET` (`php artisan jwt:secret`)
- [ ] Replace the seeded admin/customer rows with operator-managed accounts
- [ ] Lock Laravel's network access — only the Next.js BFF should reach it (private subnet / docker network / VPC)
- [ ] Schedule `currency:fetch` via a real cron / supervisor (the in-app schedule list is a definition, not a daemon)
- [ ] Tune the `auth` and `api` rate limits to actual traffic
- [ ] Add an APM / error tracker (Sentry, Bugsnag) on top of the file logs
- [ ] Consider gating `/docs` behind auth middleware
- [ ] Terminate TLS at the nginx layer
- [ ] Configure Redis with a password and decide on an appropriate persistence policy
- [ ] Set up daily database backups and point-in-time recovery

Out of scope for this case study (deliberately omitted to keep the surface focused):

- Payment gateway integration
- Shipping / fulfillment tracking
- Email confirmations and password resets
- Soft-deletes (admin `DELETE` cascades cart_items and SET NULLs order_items.product_id)
- Multi-role authorization (single `is_admin` boolean — Spatie permission or similar would replace this for richer roles)
- Distributed tracing, request-level sampling
- Idempotency keys for checkout

---

## License

Proprietary — submitted as a hiring case study to Dehasoft. Not licensed for redistribution.
