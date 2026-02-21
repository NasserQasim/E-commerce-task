# ShopFlow — E-Commerce Order Module

A simplified e-commerce module built with Laravel, supporting products, cart (Redis-backed), checkout (Cash on Delivery), and admin refund functionality.

## Tech Stack

- **PHP 8.3** with Apache (Docker)
- **Laravel 12**
- **MySQL 8** — relational data storage
- **Redis** — cart storage & caching layer
- **TailwindCSS** — frontend via CDN
- **Docker Compose** — containerized development

## Architecture

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ProductController.php      # Product listing (cached)
│   │   ├── CartController.php         # Cart CRUD operations
│   │   ├── CheckoutController.php     # Checkout orchestration
│   │   └── Admin/
│   │       └── OrderController.php    # Order management & refunds
│   ├── Middleware/
│   │   └── AdminMiddleware.php        # Admin route guard
│   └── Requests/
│       ├── AddToCartRequest.php       # Cart add validation
│       └── UpdateCartRequest.php      # Cart update validation
├── Models/
│   ├── Product.php
│   ├── Order.php
│   └── OrderItem.php
└── Services/
    ├── CartService.php                # Redis-backed cart logic
    ├── CheckoutService.php            # Transactional order creation
    └── RefundService.php              # Transactional refund + stock restore
```

### Design Decisions

**Service Layer Pattern** — Controllers are kept thin. All business logic lives in dedicated service classes (`CartService`, `CheckoutService`, `RefundService`). This makes the code testable, reusable, and easy to reason about.

**Redis for Cart Storage** — Carts are stored in Redis hashes (`cart:{sessionId}`) with a 24-hour TTL. Redis was chosen over database/session because:
- Carts are ephemeral and don't need ACID guarantees
- Redis hash operations are atomic and fast
- No database writes for browsing users
- Automatic expiry cleans up abandoned carts

**DB Transactions for Critical Flows** — Both checkout and refund use `DB::transaction()` to ensure atomicity:
- **Checkout**: stock validation → stock decrement → order creation → cart clear. If any step fails, everything rolls back.
- **Refund**: status update → stock restoration. Prevents partial refunds.
- `lockForUpdate()` is used during checkout to prevent race conditions on stock.

**Eloquent Relationships (No DB Foreign Keys)** — Relationships are defined at the application level via Eloquent rather than database-level foreign keys. This keeps migrations simple and gives more flexibility during development.

**Product List Caching** — The product listing is cached in Redis for 60 seconds via `Cache::remember()`, reducing database queries for the most-hit page.

### Trade-offs

| Decision | Pro | Con |
|---|---|---|
| Redis cart (no DB) | Fast, auto-expiry, no write overhead | Cart lost if Redis restarts without persistence |
| No auth system | Simpler demo scope | Admin routes are open (middleware stub in place) |
| Session-based cart ID | No login required | Cart tied to browser session |
| No DB foreign keys | Simpler migrations, flexible dev | No DB-level referential integrity enforcement |

## Setup Instructions

### Prerequisites

- Docker & Docker Compose installed
- Ports 8000, 3307, 6379, 8081 available

### Quick Start

```bash
# 1. Clone the repository
git clone <repo-url> && cd E-commerce-task

# 2. Copy environment file
cp .env.example .env

# 3. Build and start containers
docker compose up -d --build

# 4. Install dependencies
docker exec ecommerce_app composer install

# 5. Generate app key
docker exec ecommerce_app php artisan key:generate

# 6. Run migrations and seed products
docker exec ecommerce_app php artisan migrate:fresh --seed

# 7. Open in browser
open http://localhost:8000
```

### Services

| Service | URL |
|---|---|
| Application | http://localhost:8000 |
| phpMyAdmin | http://localhost:8081 |
| MySQL | localhost:3307 |
| Redis | localhost:6379 |

## How to Test

### Manual Testing

1. **Browse products** — Visit http://localhost:8000
2. **Add to cart** — Select quantity and click "Add to Cart"
3. **View cart** — Click cart icon in navigation
4. **Checkout** — Click "Checkout (COD)" button
5. **Admin orders** — Visit http://localhost:8000/admin/orders
6. **Refund** — Click "Refund" on a completed order

### Seed Products

```bash
docker exec ecommerce_app php artisan db:seed --class=ProductSeeder
```

### Run Tests

```bash
docker exec ecommerce_app php artisan test
```

### Test Refund Flow

1. Add products to cart and checkout
2. Go to http://localhost:8000/admin/orders
3. Click "View" on an order to see details
4. Click "Refund" — stock quantities will be restored
5. Attempting to refund again will show an error (double-refund prevention)

## Docker Commands

```bash
# Start all services
docker compose up -d

# Rebuild after Dockerfile changes
docker compose up -d --build

# View logs
docker compose logs -f app

# Stop all services
docker compose down

# Reset database
docker exec ecommerce_app php artisan migrate:fresh --seed

# Run artisan commands
docker exec ecommerce_app php artisan <command>

# Access container shell
docker exec -it ecommerce_app bash
```
