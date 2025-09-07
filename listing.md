Awesome — here’s a clear, prioritized instruction list for the backend so we can start frontend integration immediately after it’s done.

Backend implementation checklist for the Listing Wizard

High priority (must-have before FE integration)

1.
Authentication and identity
Derive host_id on the server from the authenticated user; do not require it in payloads.
Require Authorization: Bearer
for all wizard endpoints.

Return 401/403 with a standard error body on unauthenticated/unauthorized access.
Acceptance criteria:
host_id is never required from the client.
All responses include consistent error schema on failure.
1.
Finalize endpoint (canonical)
Add: POST /api/v1/wizard-listings/{id}/finalize
Query/body options:
validate_only?: boolean (default false)
Behavior:
If validate_only=true: run full validation, do not change state; return field_errors if any.
Else: validate + transition to published (or final state).
Idempotent if the listing is already published (return 200 with current listing state).
Headers:
Idempotency-Key (optional, to prevent double submissions).
Responses:
200: { listing, state, allowed_transitions, field_errors?: {}, warnings?: [] }
422: { code, message, field_errors: { field: [{ code, message }] } }
Acceptance criteria:
Frontend can finalize in a single call.
Finalize is safe to retry (idempotent).
1.
Standardize error model across all wizard endpoints
Error body shape:
{ code: string, message: string, field_errors?: { [field: string]: Array<{ code: string; message: string }> } }
Use:
400 for bad request shape
401/403 for auth
404 for missing listing
409 for conflict (ETag/version)
422 for validation errors with field_errors
Acceptance criteria:
Frontend can map errors to fields without guessing.
1.
Validation endpoint
Add: POST /api/v1/wizard-listings/{id}/validate
Body:
{ step?: string } // if omitted, validate full draft
Response:
200: { valid: boolean, field_errors?: {}, warnings?: [] }
422: { code, message, field_errors }
Acceptance criteria:
FE can validate per step and whole draft before finalize.
1.
Wizard listing resource conventions
Keep: POST /api/v1/wizard-listings (create draft)
Change: Use PATCH /api/v1/wizard-listings/{id} for partial updates during the wizard (instead of POST /listings/{id}).
Server computes completion_percentage (remove from client).
Response envelope:
On create/update: { listing, state, allowed_transitions }
Acceptance criteria:
FE only sends fields that changed (PATCH).
completion_percentage is not required from FE.
Media uploads (strongly recommended) 6) Presigned media upload flow

Add: POST /api/v1/media/uploads/init
Body: { count: number, purpose: "listing" }
Response: { uploads: [{ media_id, upload_url, content_type }] }
FE uploads directly to storage using upload_url.
Add: POST /api/v1/wizard-listings/{id}/media/attach
Body: { media_ids: string[], cover_id?: string, order?: string[] }
Response: { media: [{ id, url, variants, is_cover, order }] }
Enforce constraints via metadata (see below).
Acceptance criteria:
FE can upload media reliably and set cover image and ordering.
Metadata and options (makes FE simpler and consistent) 7) Metadata endpoint

Add: GET /api/v1/wizard-listings/meta
Response includes:
property_types, categories, amenities, house_rules, currencies, timezones
constraints: { max_images, max_video_size_mb, allowed_mime_types, check_in_out_rules, pricing_rules }
Acceptance criteria:
FE builds dropdowns and validates inputs using server-provided rules.
Pricing and availability (optional but valuable) 8) Pricing preview

Add: POST /api/v1/wizard-listings/preview-pricing
Body: pricing-related fields (price, currency, discounts, weekend price, additional guest price, etc.)
Response: { subtotal, taxes, fees, discounts, total, breakdown }
Acceptance criteria:
FE can show accurate pricing preview before finalize.
State and concurrency (stability and UX clarity) 9) Server-driven state and transitions

Every listing response includes:
state: "draft" | "ready_for_review" | "published" | "archived"
allowed_transitions: string[]
Acceptance criteria:
FE renders buttons/flows based on server state.
1.
Idempotency and optimistic concurrency
Support Idempotency-Key on finalize.
Add ETag/version to listing responses and require If-Match on PATCH to avoid accidental overwrites.
409 Conflict when If-Match doesn’t match.
Acceptance criteria:
Double-clicks and network retries don’t produce duplicate publishes.
FE can safely update contested drafts.
Compatibility and deprecation 11) Align or deprecate existing endpoints

Keep existing /wizard-listings and /wizard-listings/{id}/publish temporarily.
Choose one canonical finalization path:
Either /finalize (preferred) or keep /publish as the canonical action.
If current backend requires finalize=1 flag on update:
Keep supporting it for backward compatibility but mark as deprecated once /finalize is live.
Provide a deprecation window and logs.
Acceptance criteria:
No ambiguity in finalize path; FE uses a single, stable endpoint.
Documentation and testing 12) on the laravel blade updates

Document all endpoints, params, schemas, error formats, and examples.
Publish Postman collection for QA and FE.
Acceptance criteria:
FE can generate types/clients from OpenAPI and test calls via Postman.
1.
Automated tests
Unit tests for validators, state changes, and media attach logic.
Integration tests for create → patch → validate → finalize.
E2E smoke tests (happy path + common validation failures).
Acceptance criteria:
CI proves the API contract works as documented.
Security and platform 14) Security, rate limiting, and CORS

Enforce content types (application/json; multipart as needed).
Rate limit finalize and media endpoints.
CORS configured for the FE origin(s).
Acceptance criteria:
Safe and stable behavior under typical FE usage.
Implementation notes for backend

Compute completion_percentage server-side from required-field coverage.
Always return the latest canonical listing object in responses.
Keep responses consistent: { listing, state, allowed_transitions, field_errors? }.
Maintain both JSON and multipart handling paths where needed (e.g., media).
What the frontend will do once this is ready

Use PATCH for per-step saves; no completion_percentage sent.
Use POST /wizard-listings/{id}/validate for step checks and pre-submit checks.
Use presigned upload flow and media/attach for images; set cover and order.
Finalize with a single call to POST /wizard-listings/{id}/finalize (with Idempotency-Key).
Map field_errors directly to UI fields; show step-level validation summaries.
Use meta endpoint to populate dropdowns and validate constraints.
If you confirm which items you can implement in the first sprint, I’ll align the frontend plan and start wiring the integration immediately.