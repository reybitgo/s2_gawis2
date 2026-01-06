# GAWIS2 - Laravel MLM E-Commerce Platform

A comprehensive MLM e-commerce platform built with Laravel 12, featuring advanced commission distribution, wallet systems, rank management, and genealogy visualization. Supports complex business logic for multi-level marketing with 5-level commissions, unilevel bonuses, and automated rank advancement.

## 🚀 Core Features

### E-Commerce Platform
- **Product & Package Management**: Full CRUD for products and packages with inventory tracking and SEO-friendly URLs.
- **Shopping Cart**: Session-based cart with real-time AJAX updates.
- **Advanced Checkout Process**: Multi-step checkout with delivery address management and instant e-wallet payment.
- **26-Status Order Lifecycle**: Complete order tracking from payment to delivery, supporting both office pickup and home delivery.
- **Return & Refund System**: Customer-initiated returns with image uploads, admin approval workflow, and automatic wallet refunds.
- **Order Analytics**: Admin dashboard with revenue metrics, status distribution, and fulfillment analytics.

### MLM (Multi-Level Marketing) System
- **5-Level Commission Structure**: Earn commissions from your network (L1: ₱200, L2-L5: ₱50 each).
- **Referral Link System**: Shareable referral links with QR codes and social media integration.
- **Automatic Commission Distribution**: Commissions are processed and distributed to the upline's MLM wallet balance immediately upon a new member's package purchase.
- **Real-time Notifications**: Get notified via database, broadcast, and email (for verified users) when you earn a commission.
- **MLM Dashboard**: Track your network, earnings, and referral statistics.
- **Network Genealogy**: Visualize your downline network with interactive tree views and statistics.
- **Rank Advancement System**: Automatic rank progression based on network size, monthly quotas, and package purchases.
- **Monthly Quota Tracking**: Maintain monthly sales quotas to qualify for unilevel bonuses and rank advancements.

### Unilevel Bonus System
- **Product-Based Bonuses**: Earn bonuses from product purchases made by your downline.
- **Configurable Bonus Structure**: Each product can have its own multi-level bonus structure.
- **Monthly Quota Requirements**: Bonuses are distributed based on meeting monthly sales quotas.
- **Automatic Bonus Distribution**: Bonuses are distributed automatically when a product is purchased and quotas are met.

### E-Wallet System
- **Integrated Digital Wallet**: Every user gets a wallet with segregated balances for MLM earnings and general purchases.
- **Seamless Payments**: Pay for orders instantly using your wallet balance.
- **Deposits & Withdrawals**: Add funds to your wallet or withdraw your earnings (requires admin approval).
- **User-to-User Transfers**: Transfer funds to other users on the platform.
- **Complete Transaction History**: Audit trail for all wallet operations.

### Administration & Security
- **Admin Dashboard**: Centralized management of orders, returns, products, packages, users, and MLM operations.
- **System & Activity Logs**: Comprehensive logging for all system and user activities, with advanced filtering and export options.
- **Role-Based Access Control**: Granular permissions for admins and members using Spatie Laravel Permission.
- **Two-Factor Authentication (2FA)**: Secure your account with 2FA using Laravel Fortify.
- **User Suspension System**: Admins can suspend and activate user accounts.
- **Fraud Detection**: Automated fraud detection for suspicious transactions and activities.
- **Input Sanitization**: Comprehensive input validation and sanitization for security.
- **Email Service**: Configurable email notifications with verification requirements.

## 🛠 Technology Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Tailwind CSS 4.0, CoreUI, Vite
- **Database**: MySQL or PostgreSQL (SQLite for testing)
- **Authentication**: Laravel Fortify with 2FA
- **Permissions**: Spatie Laravel Permission
- **Testing**: PHPUnit with Feature and Unit tests
- **Code Quality**: Laravel Pint for code formatting

## 🏁 Getting Started

### 1. Installation
```bash
# Clone the repository
git clone <repository-url> laravel-ecommerce
cd laravel-ecommerce

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Setup
- Edit the `.env` file with your database credentials.
- Run the migrations to create the database schema:
```bash
php artisan migrate
```

### 4. Database Reset (Recommended for Initial Setup)
For a clean start with all the necessary seed data, use the built-in database reset command. This will set up the database schema, default users, roles, permissions, and sample products/packages.

**To reset the database, navigate to `/reset` in your browser.**

You will be asked for confirmation. After confirming, the system will:
- Run all database migrations.
- Seed the database with default data.
- Clear all caches.

### 5. Running the Application
```bash
# Start the development server, queue worker, log viewer, and Vite dev server
composer dev

# Or run them individually:
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```
The application will be available at `http://localhost:8000` (or as configured).

### 6. Building for Production
```bash
npm run build
```

## 🔑 Default Credentials

After running the database reset, you can use the following default credentials to log in:

**Admin User**
- **Username**: `admin`
- **Email**: `admin@gawisherbal.com`
- **Password**: `Admin123!@#`

**Member User**
- **Username**: `member`
- **Email**: `member@gawisherbal.com`
- **Password**: `Member123!@#`

## 🧪 Testing & Code Quality

The project uses PHPUnit for testing and Laravel Pint for code formatting.

### Running Tests
```bash
# Run all tests
composer test
# Or
php artisan test

# Run specific test
php artisan test --filter testMethodName
php artisan test --filter TestClassName

# Run tests in a specific file
php artisan test tests/Feature/YourTest.php

# Run only unit or feature tests
php artisan test --testsuite Unit
php artisan test --testsuite Feature
```

### Code Formatting
Always run Laravel Pint before committing:
```bash
./vendor/bin/pint
```

## 🤝 Contributing

Contributions are welcome! Please follow these steps:
1. Fork the repository.
2. Create a new feature branch.
3. Make your changes and add tests.
4. Run Laravel Pint to ensure code formatting:
   ```bash
   ./vendor/bin/pint
   ```
5. Run tests to ensure nothing broke:
   ```bash
   composer test
   ```
6. Submit a pull request.

## 📄 License

This project is licensed under the MIT License.