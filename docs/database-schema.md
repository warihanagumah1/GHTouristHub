# Tourist Hub Database Schema (Blueprint)

## Identity and Access
- `users`: id, name, email, password, phone, nationality, passport_no, emergency_contact_json, email_verified_at, two_factor_enabled, social_provider, social_provider_id, avatar_url.
- `roles`: id, name, scope (`global|tenant`), description.
- `permissions`: id, key, description.
- `role_permissions`: role_id, permission_id.
- `user_roles`: user_id, role_id, tenant_id nullable.

## Multi-tenancy
- `tenants`: id, type (`tour_company|utility_owner`), name, slug, status (`pending|approved|suspended|rejected`), owner_user_id.
- `tenant_members`: id, tenant_id, user_id, role, is_active, invited_by.
- `vendor_profiles`: id, tenant_id, legal_name, registration_no, country, address, tax_id, support_email, support_phone, bank_details_encrypted, kyc_status, kyc_notes.

## Discovery and Catalog
- `destinations`: id, country, city, slug, hero_image, seo_meta_json, is_featured.
- `categories`: id, name, slug, type (`tour|utility`), parent_id nullable.
- `listings`: id, tenant_id, type (`tour|utility`), title, slug, short_description, description, destination_id, status, moderation_status, rating_avg, rating_count, base_currency.
- `tour_listings`: listing_id, duration_value, duration_unit, group_min, group_max, meeting_point, itinerary_json, inclusions_json, exclusions_json, cancellation_policy_id, booking_cutoff_minutes, manual_confirmation.
- `utility_listings`: listing_id, subtype (`hotel|transport|attraction|event_space`), capacity_model, amenities_json, rules_json, checkin_time, checkout_time.
- `listing_media`: id, listing_id, type (`image|video|document`), url, thumb_url, sort_order, alt_text.
- `availability_slots`: id, listing_id, starts_at, ends_at, capacity_total, capacity_reserved, price_override, is_blackout.
- `listing_addons`: id, listing_id, name, price_type (`per_person|per_booking`), price_amount, is_active.

## Bookings and Commerce
- `bookings`: id, booking_no, tenant_id, client_user_id, listing_id, status, starts_at, ends_at, travelers_count, subtotal, addon_total, tax_total, discount_total, total, currency, requires_manual_confirmation, cancellation_policy_snapshot_json.
- `booking_items`: id, booking_id, item_type (`traveler|addon|fee|tax|discount`), reference_id nullable, label, quantity, unit_price, line_total, payload_json.
- `booking_travelers`: id, booking_id, first_name, last_name, dob, nationality, passport_no nullable.
- `payments`: id, booking_id, provider (`stripe|paystack|flutterwave`), payment_intent_id, idempotency_key, amount, currency, status, raw_payload_json, paid_at.
- `refunds`: id, booking_id, payment_id, requested_by_user_id, approved_by_user_id nullable, reason, amount, status, provider_ref, processed_at.
- `commissions`: id, booking_id, tenant_id, rate_applied, gross_amount, commission_amount, net_amount, source (`global|tenant_override|listing_override`).
- `payouts`: id, tenant_id, period_start, period_end, gross_amount, commission_amount, refund_amount, net_amount, status, paid_at, provider_batch_ref.
- `payout_items`: id, payout_id, booking_id, net_amount.

## Reviews, Messaging, Notifications
- `reviews`: id, booking_id, listing_id, tenant_id, client_user_id, rating, comment, photos_json, status, flagged_at, moderated_by.
- `review_replies`: id, review_id, tenant_id, user_id, body.
- `message_threads`: id, tenant_id, client_user_id, subject, last_message_at, status.
- `messages`: id, thread_id, sender_user_id, body, attachment_url, message_type (`user|system`), read_at.
- `user_notifications`: id, user_id, type, title, body, payload_json, read_at, channel (`in_app|email`).

## Administration and Operations
- `audit_logs`: id, actor_user_id, actor_tenant_id nullable, action, auditable_type, auditable_id, old_values_json, new_values_json, ip_address, user_agent, created_at.
- `cms_pages`: id, slug, title, body, seo_meta_json, is_published, published_at.
- `settings`: id, group, key, value_json, is_public.
- `disputes`: id, booking_id, opened_by_user_id, assigned_admin_id, reason, status, resolution_notes.
