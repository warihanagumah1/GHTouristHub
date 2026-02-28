# Tourist Hub

Tourist Hub is a multi-tenant marketplace platform that connects travelers with:
- Tour companies (tour packages, day trips, itineraries)
- Utility owners (hotels, transport, attractions, event venues)

This repository currently includes the Phase 1 foundation:
- Public marketplace shell (home, tours, utilities, listing detail scaffold)
- Auth foundation with:
  - Email/password
  - Social login routes (Google + Apple via Socialite)
  - Optional email-based 2FA
  - Login and password-reset throttling
- Reusable themed UI components
- Global currency selector with conversion-ready rates storage
- Architecture/schema/route docs for subsequent phases

## Stack
- PHP 8.2+
- Laravel 12
- Livewire + Volt
- Tailwind CSS + Vite
- SQLite (default local), MySQL 8 target for production

## Local Setup
1. Install dependencies:
```bash
composer install
npm install
```

2. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

3. Run migrations and seed demo data:
```bash
php artisan migrate:fresh --seed
```

4. Start development services:
```bash
composer run dev
```

## Social Login Setup
Set these in `.env`:
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI`
- `LINKEDIN_CLIENT_ID`
- `LINKEDIN_CLIENT_SECRET`
- `LINKEDIN_REDIRECT_URI`

Routes:
- `/auth/redirect/google`
- `/auth/callback/google`
- `/auth/redirect/linkedin`
- `/auth/callback/linkedin`

## Optional 2FA
- Global toggle: `AUTH_2FA_ENABLED=true|false`
- Code expiry: `AUTH_2FA_CODE_EXPIRATION_MINUTES=10`
- Users can enable/disable 2FA from the profile dashboard.

## Tests and Build
```bash
php artisan test
npm run build
```

## Currency Rates Sync
- Set `EXCHANGERATE_API_KEY` in `.env`.
- Manual sync command:
```bash
php artisan currencies:sync-rates
```
- Scheduler is configured to sync twice daily (00:00 and 12:00 server time).

## Stripe Commission + Payout Modes
- Platform commission is configurable with:
  - `STRIPE_PLATFORM_COMMISSION_PERCENT` (default `10`)
- Stripe Connect destination charges (direct-to-vendor, platform fee retained):
  - `STRIPE_CONNECT_DESTINATION_ENABLED=true`
  - Vendor sets `Stripe Connect Account ID` in their Mini Website profile.
  - Vendor sets payout mode to `Stripe Connect Destination Charges`.
- Platform payout mode (central collection + payout requests):
  - Vendor sets payout mode to `Platform Payout Requests`.
  - Vendor requests payouts from `Vendor > Payouts`.
  - Admin reviews from `Admin > Payouts`.
  - When admin marks `paid`, system auto-creates Stripe transfer if connect account is present and Stripe is configured; otherwise admin can enter manual transfer reference.

## Demo Accounts
All seeded accounts use password: `password`

- Admin: `admin@touristhub.test`
- Admin Staff: `admin-staff@touristhub.test`
- Tour Company Owner: `tour-owner@touristhub.test`
- Tour Company Staff: `tour-staff@touristhub.test`
- Utility Owner: `utility-owner@touristhub.test`
- Utility Staff: `utility-staff@touristhub.test`
- Client: `client@touristhub.test`
- Client 2: `client2@touristhub.test`

## Planning and API Docs
- [Architecture overview](docs/architecture-overview.md)
- [Database schema blueprint](docs/database-schema.md)
- [Route map](docs/route-map.md)
- [Implementation phases](docs/implementation-phases.md)
- [OpenAPI scaffold](docs/openapi.yaml)

## Next Milestones
1. Implement tenant, membership, RBAC, onboarding persistence.
2. Implement listing CRUD with media uploads and moderation.
3. Implement booking engine + Stripe payment/webhook idempotency.
# GHTouristHub
