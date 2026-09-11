# Turno Technical Test
This is the home assignment for Turno interview process.

## Local setup

Requirements:

- PHP 8.5
- Composer
- Node.js 24 LTS
- npm
- MySQL

Create the local database:

```sql
CREATE DATABASE turno_technical_test;
```

Copy the environment file and update the MySQL credentials if your local user is not `root` with an empty password:

```bash
cp .env.example .env
```

Install dependencies and initialize Laravel:

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
```

Run the app locally:

```bash
npm run dev
php artisan serve
```

The app will be available at `http://127.0.0.1:8000`.

## Backend status

The backend currently includes a simple JSON user API for registration, login, authenticated user lookup, and logout.

Authenticated API requests use Laravel Sanctum bearer tokens returned by login or registration.

## Database structure

The current schema is focused on user-owned shipping labels:

- `users`: basic Laravel auth users with `name`, unique `email`, hashed `password`, `remember_token`, and timestamps. Email verification is intentionally not included.
- `password_reset_tokens`: standard Laravel password reset token table, kept for compatibility with Laravel auth flows.
- `personal_access_tokens`: Laravel Sanctum's token table. Tokens are stored hashed, plain tokens are only returned once after register/login, and tokens belong to users.
- `shipping_labels`: one local record per EasyPost-backed shipment label. It belongs to a user and stores EasyPost shipment, postage label, and rate IDs, label URLs, tracking code, carrier/service, address JSON, parcel JSON, selected rate JSON, raw response JSON, status, and optional error details.

The MVP treats an EasyPost shipment and its purchased label as one user-facing `shipping_labels` record. A separate shipments table can be added later if the app needs pre-purchase shipment states, returns, refunds, batches, or multiple labels per shipment.

## Tests

Run the backend feature tests:

```bash
php artisan test --testsuite=Feature
```

Current coverage includes register, login, invalid credentials, authenticated profile lookup, missing-token rejection, and logout token deletion.

## Assumptions

- This is a prototype focused on the core assignment flow rather than production polish.
- The app uses local PHP, Node, and MySQL for development. Lando config exists but is not required.
- User auth is simple email/password login with bearer tokens. Email verification is intentionally omitted.
- API authentication uses Laravel Sanctum in bearer-token mode, not SPA cookie mode.
- EasyPost labels should be created with a test API key so postage is not charged.
- Shipping labels are stored as one local record per purchased EasyPost shipment label.

## What I'd do next

- Add the EasyPost backend service for creating shipments, selecting a USPS test rate, buying the label, and storing the response.
- Add authenticated shipping-label API endpoints for create, list, detail, and print URL access.
- Add frontend React screens for login, label creation, label history, and printable label details.
- Add validation for US-only addresses and package dimensions before calling EasyPost.
- Add error handling tests for EasyPost failures and authorization tests for user-scoped label history.

## Notes

- Frontend pages and EasyPost integration have not been added yet.
