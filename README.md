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

The backend currently includes JSON APIs for user authentication and shipping-label management.

Authenticated API requests use Laravel Sanctum bearer tokens returned by login or registration.

Shipping-label creation uses EasyPost from the Laravel backend only. Add a test key to `.env` before creating labels:

```env
EASYPOST_API_KEY=EZTK...
```

The label API lists the authenticated user's labels, shows label details, creates a USPS test label by buying the first USPS rate returned by EasyPost, then redirects to the stored PDF label URL for printing. EasyPost test billing is controlled by using a test API key.

The React table and detail pages show the print action only when EasyPost returns a stored `label_pdf_url` or `label_url`. The protected API print route remains available at `/api/labels/{id}/print`; browser requests without a bearer token redirect to the SPA login route instead of throwing a Laravel route exception.

## Frontend status

The React/Vite app is served by Laravel as a single-page app. Login, registration, label history, label detail, and label creation pages are wired to the backend API.

Run the frontend dev server and Laravel server in separate terminals:

```bash
php artisan serve
npm run dev
```

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
php artisan test
```

Backend coverage includes register, login, invalid credentials, authenticated profile lookup, missing-token rejection, and logout token deletion.

Shipping-label backend tests cover authenticated pagination, label details, user scoping, request validation, successful label storage with a fake EasyPost client, EasyPost error handling, print redirects, and USPS rate selection.

When EasyPost returns no rates, the API includes carrier messages from EasyPost in the error response to make address or carrier-account issues easier to diagnose.

## Assumptions

- This is a prototype focused on the core assignment flow rather than production polish.
- The app uses local PHP, Node, and MySQL for development. Lando config exists but is not required.
- User auth is simple email/password login with bearer tokens. Email verification is intentionally omitted.
- API authentication uses Laravel Sanctum in bearer-token mode, not SPA cookie mode.
- EasyPost labels should be created with a test API key so postage is not charged.
- Shipping labels are stored as one local record per purchased EasyPost shipment label.
- The MVP buys the first USPS rate returned by EasyPost instead of asking users to choose one.

## What I'd do next

- Add optional rate selection if users need to compare service levels before buying.
- Add address verification, refunds/voiding, tracking webhooks, and better production observability.

## Notes

- EasyPost integration requires `EASYPOST_API_KEY` to be set before creating real test labels.
- EasyPost API Key used during development was a test environment key
