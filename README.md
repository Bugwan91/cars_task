# Minimal Laravel + Vue.js Web App (Dockerized)

This project is a minimal, production-ready setup for a Laravel application with a Vue.js frontend, running in a Docker environment.

## 🚀 Quick Start

### Prerequisites
- **Docker Desktop** installed and running.

### Installation

Everything boots with a single command:

```bash
docker-compose up
```

The PHP container's entrypoint handles first-time and subsequent setup automatically:
- copies `.env.example` to `.env` (only once) and generates `APP_KEY` when missing
- runs `composer install` and `npm install` (only if `node_modules` is absent or you set `FORCE_NPM_INSTALL=1`)
- builds the production frontend bundle (`npm run build`)
- runs database migrations (followed by `php artisan db:seed`) on every start
- fixes `storage` and `bootstrap/cache` permissions so the app can write logs/uploads

Need to rerun any of those steps? Either restart the `app` service (`docker-compose up -d --build app`) or run the individual commands via `docker-compose exec app ...`.

### Access the App
- **Web App:** [http://localhost:8000](http://localhost:8000)
- **Database:** Port `3306`
  - User: `laravel`
  - Password: `root`
  - Database: `laravel`

### Automation Controls
You can tweak the bootstrap behavior by setting environment variables on the `app` service in `docker-compose.yml`:

- `FORCE_NPM_INSTALL=1` — reinstall Node dependencies even if `node_modules/` exists
- `SKIP_NPM_BUILD=1` — skip the production asset build during container startup
- `SKIP_MIGRATIONS=1` — prevent automatic `php artisan migrate --force`
- `SKIP_DB_SEED=1` — skip `php artisan db:seed --force` (by default seeding runs right after migrations)

## 🛠 Development

### Commands
- **Run Vite (Hot Reload):**
  ```bash
  docker-compose exec app npm run dev
  ```
- **Run Artisan Commands:**
  ```bash
  docker-compose exec app php artisan <command>
  ```
- **Run Composer:**
  ```bash
  docker-compose exec app composer <command>
  ```
- **Run Tests:**
  ```bash
  docker-compose exec app php artisan test
  ```

### Project Structure
- **`docker-compose.yml`**: Orchestrates Nginx, PHP (App), and MySQL services.
- **`docker/`**: Configuration files for PHP and Nginx.
- **`src/`**: The Laravel application source code.

### Features & Notes
- **Single-command bootstrap:** `docker-compose up -d --build` now covers env creation, dependencies, assets, migrations, and seeding within the PHP container.
- **Automatic Permissions:** The container automatically fixes permissions for `storage` and `bootstrap/cache` on startup to prevent common Windows/Docker permission issues.
- **Healthchecks:** The application waits for the database to be fully ready before starting to ensure a smooth boot process.
- **Vite HMR:** Configured to work seamlessly with Docker on port `5173`.
