# Tourist Hub Route Map (Target)

## Public Marketplace
- `GET /` home
- `GET /tours` browse tour listings
- `GET /utilities` browse utility listings
- `GET /listings/{slug}` listing detail
- `GET /vendors/{slug}` vendor public profile
- `GET /destinations/{slug}` destination landing
- `GET /pages/{slug}` CMS pages (terms/privacy/faq)

## Auth
- `GET /register`, `POST /register`
- `GET /login`, `POST /login`
- `GET /auth/{provider}/redirect`, `GET /auth/{provider}/callback`
- `GET /two-factor-challenge`, `POST /two-factor-challenge`
- `GET /forgot-password`, `POST /forgot-password`
- `GET /reset-password/{token}`, `POST /reset-password`
- `GET /verify-email`, `GET /verify-email/{id}/{hash}`

## Client Dashboard (`/client/*`)
- Overview, bookings index/show, invoices download
- Wishlist index/store/destroy
- Messages threads/messages
- Reviews create/update
- Profile + notification preferences

## Vendor Dashboard (`/vendor/*`)
- Overview analytics
- Listings CRUD (tour/utility)
- Availability slots CRUD
- Bookings index/show/update status
- Customers index/show
- Messages threads/messages
- Payouts and statements
- Team members and role assignments
- Vendor settings and onboarding completion

## Admin Dashboard (`/admin/*`)
- Overview analytics
- Vendors: pending approvals, suspend/reject
- Listings moderation
- Bookings/disputes/refunds
- Payments/commissions/payout batches
- Users/roles/permissions
- CMS and global settings
- Audit logs and reports

## API (versioned `/api/v1/*`)
- Public browse/search endpoints
- Auth/session/token endpoints
- Client booking/payment/review/message endpoints
- Vendor listing/availability/booking/payout endpoints
- Admin moderation/reporting endpoints
