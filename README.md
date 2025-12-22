# Stores API

A Laravel-based multi-store e-commerce API that supports both digital and physical product stores with delivery management, shopping cart, and order processing.

## Overview

This API enables users to manage multiple stores, sell products (digital or physical), handle shopping carts, and process orders with city-based delivery pricing.

## Features

- 🏪 **Multi-Store Management** - Users can manage multiple stores
- 📦 **Product Management** - Support for both digital and physical products
- 🛒 **Shopping Cart** - User cart with real-time pricing
- 📍 **City-Based Delivery** - Configurable delivery zones and pricing per store
- 💰 **Order Processing** - Complete order lifecycle with multiple statuses
- 🔐 **Authentication** - Built on Laravel Sanctum (assumed)
- 🎨 **Filament Admin Panel** - Modern admin interface

## Tech Stack

- **PHP**: 8.3.28
- **Laravel**: 12.x
- **Filament**: 4.x
- **Livewire**: 3.x
- **Pest**: 4.x (Testing)
- **Tailwind CSS**: 4.x

## Database Schema

### Models & Relationships

#### User
- Manages multiple stores (many-to-many)
- Creates products
- Has cart items
- Places orders

#### Store
- Managed by multiple users (many-to-many)
- Has many products
- Receives orders
- Delivers to multiple cities with custom pricing (many-to-many)
- **Types**: `digital`, `physical`

#### Product
- Belongs to a store
- Created by a user
- Available in cart items
- Can be ordered
- **Types**: `digital`, `physical`
- **Statuses**: `active`, `inactive`, `draft`

#### City
- Has stores that deliver to it
- Associated with orders for delivery location

#### CartItem
- Links user to products
- Stores quantity and price snapshot
- Unique constraint: one product per user cart

#### Order
- Belongs to a user (buyer)
- Belongs to a store (seller)
- Has a delivery city (nullable for digital products)
- Contains multiple order items
- **Statuses**: `new`, `pending`, `processing`, `completed`, `cancelled`, `refunded`

#### OrderItem
- Belongs to an order
- Links to a product
- Stores quantity and price at order time

### Pivot Tables

- **store_user**: Users managing stores
- **city_store_delivery**: Store delivery coverage with pricing per city

## Installation

```bash
# Clone the repository
git clone <repository-url>
cd stores-api

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env
# Then run migrations
php artisan migrate

# Seed database (if seeders available)
php artisan db:seed

# Start development server
php artisan serve
```

## Database Migrations

Run migrations in order:
```bash
php artisan migrate
```

Migrations include:
1. Users table (Laravel default)
2. Stores table
3. Products table
4. Cities table
5. City-Store delivery pivot table
6. Cart items table
7. Orders table
8. Order items table
9. Store-User pivot table

## Enums

### OrderStatus
- `new` - Initial order state
- `pending` - Payment pending
- `processing` - Being prepared
- `completed` - Delivered/Completed
- `cancelled` - Order cancelled
- `refunded` - Order refunded

### ProductStatus
- `active` - Available for purchase
- `inactive` - Not available
- `draft` - Not published

### ProductType & StoreType
- `digital` - Digital goods (no delivery needed)
- `physical` - Physical goods (requires delivery)


## Testing

This project uses Pest for testing:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/StoreTest.php

# Run with coverage
php artisan test --coverage
```

## Code Standards

This project follows Laravel conventions:
- **Variables/Methods**: `snake_case`
- **Classes**: `PascalCase`
- **Enum Keys**: `PascalCase`
- **Code Formatting**: Laravel Pint



---

**Status**: 🚧 In Development
