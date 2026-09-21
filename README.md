# Liza's Bakeshop POS System

A Point-of-Sale system for Liza's Bakeshop built with Laravel 11.

## Features

- **Authentication** — Admin/Cashier roles with Laravel Breeze
- **POS / Create Order** — Point-of-Sale interface with cart, discount, and payment
- **Order Management** — View, filter, complete, cancel orders
- **Refund Processing** — Process refunds with automatic stock restoration
- **Shift Management** — Cashier shift tracking with cash reconciliation
- **Sales Reports** — Detailed sales analytics and filtering
- **Analytics Dashboard** — Charts and business insights
- **Inventory Management** — Stock tracking and low-stock alerts
- **User Management** — Add, edit, deactivate users
- **Contact Form** — Customer inquiries from landing page

## Tech Stack

- **Backend:** Laravel 11, PHP 8.3
- **Database:** MySQL
- **Frontend:** Tailwind CSS, Alpine.js
- **Charts:** Chart.js
- **Build Tool:** Vite
- **Authentication:** Laravel Breeze

## Installation

```bash
# Clone the repository
git clone https://github.com/clarence-slzr/lizas-bakeshop-laravel.git
cd lizas-bakeshop-laravel

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Setup environment
copy .env.example .env
php artisan key:generate

# Configure database in .env, then run migrations
php artisan migrate

# Build frontend assets
npm run build

# Start the development server
php artisan serve
```
