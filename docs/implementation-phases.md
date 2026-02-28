# Implementation Phases

## Phase 1 (Current focus)
- Public marketplace shell: home, tours, utilities, listing detail.
- Responsive public navigation and mobile menu.
- Auth hardening: social login, optional 2FA, rate-limited reset flow.
- Tenant and listing domain migrations/models/policies (next immediate step in Phase 1).

## Phase 2
- Booking engine with capacity locks and transactional creation.
- Stripe payment intents + webhook processing + idempotency.
- Client dashboard and vendor booking management.

## Phase 3
- Reviews and moderation.
- In-app messaging and notification center.
- Refund workflow (request, approve, process) and payout ledger.
- Admin moderation panels and dispute handling.

## Phase 4
- SEO/destination pages, performance tuning, caching.
- Analytics dashboards (admin + vendor).
- Full test suite expansion (booking race conditions, webhook retries, tenant isolation).
- Production hardening (Horizon, S3, backups, observability, CI/CD).

## Release Rules
- Every phase ships with migrations, seed data, and tests.
- No partial payment/booking flow deploys without idempotency and reconciliation checks.
- Tenant-scoped endpoints require policy tests before merge.
