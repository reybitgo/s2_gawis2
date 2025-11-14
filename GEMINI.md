# Project Overview

This is a comprehensive e-commerce application built with Laravel 12. It features complete order management, returns/refunds processing, dual delivery methods, and integrated payment processing through a built-in e-wallet system.

**Main Technologies:**

*   **Backend:** Laravel 12, PHP 8.2+
*   **Frontend:** Tailwind CSS 4.0, CoreUI, Vite
*   **Database:** MySQL or PostgreSQL
*   **Authentication:** Laravel Fortify
*   **Permissions:** Spatie Laravel Permission

**Architecture:**

The application follows a standard Laravel MVC architecture. It includes a robust set of features for both customers and administrators, with a focus on a seamless e-commerce experience.

# Building and Running

**1. Installation:**

```bash
# Clone the repository
git clone <repository-url> laravel-ecommerce
cd laravel-ecommerce

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

**2. Environment Configuration:**

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

**3. Database Setup:**

*   Edit the `.env` file with your database credentials.
*   Run migrations and seeders:

```bash
php artisan migrate
php artisan db:seed
```

**4. Running the Application:**

```bash
# Start the development server, queue worker, log viewer, and Vite dev server
composer dev

# Or run them individually:
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

**5. Building for Production:**

```bash
npm run build
```

**6. Testing:**

The project uses PHPUnit for testing. To run the tests, execute the following command:

```bash
./vendor/bin/phpunit
```

# Development Conventions

*   **Coding Style:** The project follows the Laravel Pint coding standards.
*   **Testing:** All new features should be accompanied by tests.
*   **Contribution:** Contributions are welcome. Please follow the guidelines in the `README.md` file.
