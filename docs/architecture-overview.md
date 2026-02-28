# Tourist Hub Architecture Overview

## Core Components
- Public marketplace (server-rendered Blade + Tailwind): discovery, listing detail, SEO pages.
- Auth and identity service (Laravel auth + optional 2FA + Socialite).
- Tenant domain layer (tenant, tenant members, roles, permissions, vendor profiles).
- Booking engine (availability lock, booking lifecycle, cancellation, refund).
- Payment service (Stripe intents, webhook processor, commission ledger, payout ledger).
- Messaging and notifications (in-app threads + email notifications + queue workers).
- Admin control plane (moderation, approvals, disputes, payouts, settings, analytics).
- Storage services (S3-compatible media/documents + thumbnail jobs).
- Observability and audit (audit logs, failed jobs, alerts, reconciliation reports).

## Component Diagram (Description)
1. Browser clients (public, traveler dashboard, vendor dashboard, admin dashboard) call Laravel web/API routes.
2. Laravel app executes RBAC + tenant scope middleware before business services.
3. Domain services persist to MySQL and enqueue async jobs to Redis queues.
4. Queue workers process emails, webhooks, PDF invoices, thumbnail generation, reconciliation tasks.
5. Stripe webhooks hit signed endpoints, which are idempotently processed and stored.
6. Media uploads are stored in S3-compatible storage; app serves CDN URLs.
7. Admin analytics reads aggregated booking/payment tables and cached reporting views.

## Security Model
- Tenant isolation enforced via `tenant_id` scoping and policies.
- Admin global bypass via dedicated role and policy gate.
- Rate limiting on login/reset/2FA endpoints.
- Signed webhook verification and idempotency keys.
- Sensitive payout fields encrypted at rest.
- Audit logging for high-risk actions (refunds, payouts, role changes, approvals).
