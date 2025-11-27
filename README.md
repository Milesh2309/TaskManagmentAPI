# TaskManagementAPI

Lightweight Laravel API for task management (Sanctum token authentication).

## Requirements

- PHP 8.2+
- Composer
- SQLite (default in this repo) or a MySQL/Postgres database configured in `.env`

## Quick Setup (local)

1. Install PHP dependencies:

```powershell
composer install
```

2. Copy environment file and generate app key:

```powershell
copy .env.example .env
php artisan key:generate
```

3. (Optional) Use SQLite for quick local setup (recommended for dev):

```powershell
# create sqlite file
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
# update .env: set DB_CONNECTION=sqlite and DB_DATABASE=database/database.sqlite
```

4. Run migrations and seed a test user:

```powershell
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force
```

The default seeded user is:

- Email: `test@example.com`
- Password: `1234`

5. (Optional) Start the dev server:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
# or
php -S 127.0.0.1:8000 -t public
```

## Smoke Test (no network required)

There is a helper script that runs login + create task internally (uses the app kernel):

```powershell
php smoke_test.php
```

This performs a login with the seeded user and creates a task, printing the responses.

## API Routes

All task routes are protected by Sanctum (require `Authorization: Bearer <token>`), except `POST /api/login` and `GET /api/_ping`.

- `POST /api/login` — login and receive a token
  - Body (JSON):
    ```json
    { "email": "test@example.com", "password": "1234" }
    ```
  - Success response (200):
    ```json
    { "token": "<personal-access-token>" }
    ```

- `GET /api/_ping` — quick ping (debug)

- `GET /api/tasks` — list tasks for authenticated user

- `POST /api/tasks` — create task
  - Body (JSON):
    ```json
    {
      "title": "My task",
      "description": "Optional description",
      "priority": "Low", // Low|Medium|High
      "due_date": "2025-12-31" // optional
    }
    ```
  - Success response (201):
    ```json
    { "data": { "id": 1, "title": "My task", "status": "Draft", "user_id": 1, ... } }
    ```

- `PUT /api/tasks/{id}` — update a task (only allowed when status == Draft)
  - Body: same fields as create; fields may be partial.

- `GET /api/tasks/{id}` — show single task (must belong to authenticated user)

- `POST /api/tasks/{id}/in-process` — move Draft → In-Process

- `POST /api/tasks/{id}/complete` — move In-Process → Completed

Validation and status-transition behavior:
- Creating a task requires `title` and `priority` (priority must be `Low|Medium|High`). Missing/invalid fields return 422 with `errors` object.
- Updates are allowed only when the task is in `Draft` state. Attempting to update when in another state returns 400 with message `Only draft tasks can be updated`.
- Completion requires `In-Process` state; otherwise returns 400 with message `Only in-process tasks can be completed`.

## Postman

Import `postman_collection.json` (provided in the repo). Create an environment with `baseUrl` (e.g. `http://127.0.0.1:8000`) and run the `Login` request — the collection's test script will save `token` to the environment variable `token`.

Example: set `Authorization` header to `Bearer {{token}}` for protected requests.

## Running tests

Run the application's test suite:

```powershell
php artisan test
```

## Notes

- Personal access tokens returned by `/api/login` include a prefix `id|token` (e.g. `8|...`). Use the entire string as the Bearer token.
- For production, configure a proper database, caching, session driver, and secure the .env keys.
- If you want updates to be allowed in `In-Process` state or to change error status codes, see `app/Http/Controllers/TaskController.php`.

If you'd like, I can also:
- Add a Postman environment file with `baseUrl` and `token` set.
- Add feature tests mirroring the validation flows.
- Create a short CONTRIBUTING / DEV document describing common tasks.

---
Generated on 2025-11-27.
# TaskManagementAPI

Simple Laravel API for tasks.

Requirements
- PHP and Composer

Quick start

1. Install packages

```powershell
composer install
```

2. Copy env and set app key

```powershell
copy .env.example .env
php artisan key:generate
```

3. Run migrations

```powershell
php artisan migrate
```

4. Start server

```powershell
php artisan serve
```

Folder structure

- app/
  - Http/
    - Controllers/
  - Models/
- bootstrap/
- config/
- database/
  - migrations/
  - seeders/
- public/
- routes/
- storage/

Postman collection

There is a Postman collection file `postman_collection.json` with these main requests:
- Login (POST /api/login)
- Create Task (POST /api/tasks)
- Update Task (PUT /api/tasks/{id})
- Start Task (POST /api/tasks/{id}/status with {"action":"start"})
- Complete Task (POST /api/tasks/{id}/status with {"action":"complete"})

Import `postman_collection.json` into Postman and set `{{baseUrl}}` and `{{token}}` variables.
