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

## Notes

- Lando config is currently present but not required for local development.
- No app routes, pages, auth, or EasyPost integration have been added yet.
