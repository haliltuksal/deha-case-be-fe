# Dehasoft Case — Full Stack

Tek bir Docker stack ile Next.js storefront (BFF proxy dahil) ve Laravel API'yi
birlikte ayağa kaldıran üst dizin.

```
deha-case-be-fe/
├── backend/             Laravel 12 API (JWT, multi-currency, transactional orders)
├── frontend/            Next.js 15 storefront + /api/* BFF proxy
├── docker-compose.yml   üstten her şeyi tek seferde başlatır
└── Makefile             (opsiyonel) sık kullanılan komutlara kısayol
```

## Hızlı başlangıç

Gereken: Docker (Desktop veya Engine 20.10+).

```bash
git clone git@github.com:haliltuksal/deha-case-be-fe.git
cd deha-case-be-fe
docker compose up -d
```

İlk başlatma ~30–60 saniye sürer. Container'lar ayağa kalkarken `app`
container'ının entrypoint'i şunları **otomatik** yapar:

1. `.env` dosyasını `.env.example`'dan üretir
2. `APP_KEY` ve `JWT_SECRET` değerlerini oluşturur
3. MySQL'in hazır olmasını bekler, migration'ları koşar
4. Demo veriyi (admin + customer + 10 ürün) seed eder
5. TCMB'den günlük USD/EUR kurunu çeker (Redis'e cacheler)

İlerlemeyi izlemek istersen:

```bash
docker compose logs -f app
```

`fpm is running, pid 1` satırını gördüğünde backend hazır demektir.

### Açıldığında

| Servis | URL |
|---|---|
| Storefront | <http://localhost:3000> |
| Laravel API | <http://localhost:8080/api/v1> |
| Live API docs (Scribe) | <http://localhost:8080/docs> |

Statik API teslimi (boot etmeden inceleme için):
- [`backend/docs/api/postman_collection.json`](backend/docs/api/postman_collection.json) — Postman/Insomnia'ya import et
- [`backend/docs/api/openapi.yaml`](backend/docs/api/openapi.yaml) — Swagger UI / Redoc / Stoplight'a yükle

### Demo hesapları

Seed'den gelen kullanıcılar (`backend/database/seeders/DatabaseSeeder.php`):

| Rol | E-posta | Şifre |
|---|---|---|
| Admin | `admin@dehasoft.test` | `password` |
| Müşteri | `customer@dehasoft.test` | `password` |

### Demo akışı (case'in 5 maddesini birebir gezer)

1. <http://localhost:3000>'i aç — TRY/USD/EUR switcher'ı sağ üstte, fiyatlar anlık değişir **(#5 Döviz kuru)**
2. **Hesap Oluştur** → `test@dehasoft.com` / `password123` **(#1 Kullanıcı yönetimi)**
3. Bir ürüne **Sepete Ekle**, `/cart`'a git, miktar **+/-** dene, **Kaldır** **(#3 Sepet)**
4. **Siparişi Onayla** → `/orders/[id]`'de status **Beklemede** **(#4 Sipariş)**
5. **Siparişi İptal Et** → status **İptal Edildi**, stok geri yüklenir
6. Logout → `admin@dehasoft.test` / `password` ile giriş
7. Sağ üst menüden **Admin Paneli** → Ürün CRUD **(#2 Ürün yönetimi)**
8. Başka bir pending siparişi admin olarak **Tamamlandı** olarak işaretle

## Yaygın docker compose komutları

```bash
docker compose ps                          # canlı servis durumu
docker compose logs -f                     # birleşik log akışı
docker compose logs -f app                 # sadece backend
docker compose logs -f frontend            # sadece frontend
docker compose exec app bash               # backend container'ında shell
docker compose exec frontend sh            # frontend container'ında shell
docker compose down                        # her şeyi durdur
docker compose down -v                     # durdur + DB/Redis verisini de sil
docker compose up -d --build               # imajları yeniden yapıp ayağa kaldır
```

### Sık ihtiyaç duyacağın artisan / pnpm komutları

```bash
# Backend
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan currency:fetch     # TCMB kurlarını manuel çek
docker compose exec app php artisan scribe:generate    # API docs'u yenile
docker compose exec app composer test                  # 135 Pest testi
docker compose exec app composer qa                    # lint + phpstan + test

# Frontend
docker compose exec frontend pnpm test                 # 25 Vitest testi
docker compose exec frontend pnpm qa                   # typecheck + lint + format + test
```

### Health probe

BFF → Laravel zincirini host'tan tek istek ile doğrula:

```bash
curl -s http://localhost:3000/api/health | jq
```

Beklenen çıktı:

```json
{
  "status": "success",
  "data": { "overall": "ok", "bff": "ok", "backend": { "database": "ok", "redis": "ok" } }
}
```

## Port çakışmaları

Varsayılan portlar host'ta dolu ise üst dizinde env değişkeni ile override et:

```bash
APP_PORT=8081 FRONTEND_PORT=3001 docker compose up -d
```

Desteklenen değişkenler: `APP_PORT`, `FRONTEND_PORT`, `DB_FORWARD_PORT`, `REDIS_FORWARD_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`, `REDIS_PASSWORD`, `COOKIE_NAME`, `REQUEST_TIMEOUT_MS`.

> Redis boot anında `--requirepass` ile ayağa kalkar. Local kolaylık için
> `REDIS_PASSWORD`'ün varsayılan değeri `change-me-redis-password`; gerçek
> bir kuruluma çıkmadan önce mutlaka ortam değişkeniyle override et.

## Mimari özet

```
Browser
   │  same-origin /api/*
   ▼
Next.js (frontend container, port 3000)
   ├─ Server components + BFF route handlers
   │  → src/lib/api/client.ts (client-only fetch wrapper)
   │  → src/server/* (server-only modules, repository layer)
   │
   │  docker network: http://nginx
   ▼
Laravel (app + nginx containers, port 8080)
   ├─ JWT (php-open-source-saver/jwt-auth) + Redis blacklist
   ├─ MySQL 8 (siparişler, ürünler, sepetler)
   └─ Redis (cart cache + exchange rates + queue)
```

**Kritik güvenlik özellikleri:**

- `LARAVEL_API_URL` sadece frontend'in server-side modüllerinde okunur (`import 'server-only'` + ESLint kuralı). Browser bundle'ında Laravel URL'i geçmez.
- JWT yalnızca HttpOnly + SameSite=Lax cookie'de tutulur; JavaScript okuyamaz.
- Frontend container'ı Laravel'e `http://nginx` üzerinden (docker network) erişir, host port'unu kullanmaz.

Detaylar için her iki projenin kendi README'sine bakın:
- [`backend/README.md`](backend/README.md) — mimari, JWT akışı, FX entegrasyonu, test stratejisi
- [`frontend/README.md`](frontend/README.md) — BFF deseni, co-location yapısı, güvenlik checklist'i

## (Opsiyonel) Makefile kısayolları

Aynı işleri kısa komutlarla çalıştıran bir Makefile de var; `make` kullanmak istemiyorsan tamamen yok sayabilirsin.

```bash
make up              # docker compose up -d
make down            # docker compose down
make logs            # docker compose logs -f
make qa              # backend + frontend tüm QA
make help            # tüm hedefleri listele
```

## Per-project standalone

Solo dev için her projenin kendi `docker-compose.yml`'i de korunur:

```bash
cd backend && docker compose up -d        # sadece Laravel + MySQL + Redis
cd frontend && docker compose up -d       # sadece Next.js (backend'i ayrıca koşturmak gerek)
```

Üst dizinin compose'u ile bunlar farklı `project name` kullanır (`dehasoft` vs `dehasoft-backend` / standalone frontend), çakışmazlar.
