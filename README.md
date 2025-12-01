# Minimal Laravel + Vue.js Web App (Dockerized)

This project is a minimal, production-ready setup for a Laravel application with a Vue.js frontend, running in a Docker environment.

## 🚀 Quick Start

### Prerequisites
- **Docker Desktop** installed and running.

### Installation

1. **Clone the repository** (if applicable).

2. **Setup Environment Variables:**
   ```bash
   cd src
   cp .env.example .env
   cd ..
   ```

3. **Start the Application:**
   ```bash
   docker-compose up -d --build
   ```
   *Note: The database service has a healthcheck. The app container will wait for the database to be ready before starting.*

4. **Install Dependencies & Initialize:**
   Run the following commands to install PHP/Node dependencies and set up the database:
   ```bash
   # Install PHP dependencies
   docker-compose exec app composer install

   # Install Node dependencies
   docker-compose exec app npm install --legacy-peer-deps

   # Build frontend assets
   docker-compose exec app npm run build

   # Run database migrations
   docker-compose exec app php artisan migrate
   ```

5. **Access the App:**
   - **Web App:** [http://localhost:8000](http://localhost:8000)
   - **Database:** Port `3306`
     - User: `laravel`
     - Password: `root`
     - Database: `laravel`

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
- **Automatic Permissions:** The container automatically fixes permissions for `storage` and `bootstrap/cache` on startup to prevent common Windows/Docker permission issues.
- **Healthchecks:** The application waits for the database to be fully ready before starting to ensure a smooth boot process.
- **Vite HMR:** Configured to work seamlessly with Docker on port `5173`.
