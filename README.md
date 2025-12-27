# Stores API 🏪

Multi-store e-commerce API built with Laravel 12. Supports digital & physical products, shopping carts, city-based delivery, discount plans, wishlists, order management, and customer notifications via Telegram. Features a React frontend and a fully localized Filament admin panel.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Schema](#database-schema)
- [API Documentation](#api-documentation)
- [Admin Panel](#admin-panel)
- [Services](#services)
- [Enums](#enums)
- [Testing](#testing)
- [Code Standards](#code-standards)
- [Localization](#localization)

## 🎯 Overview

This API enables users to manage multiple stores, sell products (digital or physical), handle shopping carts, apply discount plans, manage wishlists, and process orders with city-based delivery pricing. The system includes automated customer notifications via Telegram, comprehensive admin panel with documentation, and a modern React frontend.

## ✨ Features

### Core Features
- 🏪 **Multi-Store Management** - Users can manage multiple stores with different types (digital/physical)
- 📦 **Product Management** - Support for both digital and physical products with multiple images
- 🛒 **Shopping Cart** - User cart with real-time pricing and quantity management
- 📍 **City-Based Delivery** - Configurable delivery zones and pricing per store
- 💰 **Order Processing** - Complete order lifecycle with multiple statuses and history tracking
- 🔐 **Authentication** - Built on Laravel Sanctum with role-based permissions
- 🎨 **Filament Admin Panel** - Modern, fully localized admin interface
- 📱 **React Frontend** - Modern, responsive frontend with dark mode support

### Advanced Features
- 🏷️ **Discount Plans** - Scheduled and active discount plans with percentage or fixed discounts
- ❤️ **Wishlist** - Save products to wishlist with sharing capabilities
- 📸 **Multiple Product Images** - Main image + secondary images with gallery support
- 📊 **Dashboard Analytics** - Comprehensive statistics and charts for stores and orders
- 📧 **Customer Notifications** - Automated Telegram notifications for order updates
- 📚 **Documentation System** - Built-in documentation pages for each resource
- 🌐 **Full Localization** - English and Arabic support throughout the application
- 🔍 **API Metrics** - Track API requests, response times, and errors
- 👥 **Role & Permission System** - Spatie permissions with granular access control

## 🛠️ Tech Stack

### Backend
- **PHP**: 8.3.28
- **Laravel**: 12.x
- **Filament**: 4.x
- **Livewire**: 3.x
- **Livewire Volt**: 1.x
- **Laravel Sanctum**: 4.x
- **Spatie Permissions**: 6.x
- **Pest**: 4.x (Testing)
- **Laravel Pint**: 1.x (Code Formatting)

### Frontend
- **React**: 19.x
- **Tailwind CSS**: 4.x
- **Vite**: 7.x

### Additional Packages
- **Dedoc Scramble**: API documentation generator
- **Filament Language Switch**: Multi-language support

## 📦 Installation

```bash
# Clone the repository
git clone <repository-url>
cd stores-api

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env
# Then run migrations
php artisan migrate

# Seed database (permissions, cities, sample data)
php artisan db:seed

# Install frontend dependencies
npm install

# Build frontend assets
npm run build

# Start development server
php artisan serve

# In another terminal, start frontend dev server (optional)
npm run dev
```

## ⚙️ Configuration

### Environment Variables

Key environment variables to configure:

```env
# Application
APP_NAME="Stores API"
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stores_api
DB_USERNAME=root
DB_PASSWORD=

# Telegram Bot (for customer notifications)
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_BOT_USERNAME=your_bot_username

# API
API_VERSION=1.0.0
```

### Scheduled Commands

Laravel's scheduler runs scheduled tasks automatically. You need to add a single cron entry that runs every minute.

#### On Ubuntu/Linux Server

1. **Open the crontab editor:**
   ```bash
   crontab -e
   ```

2. **Add this line (replace `/path-to-project` with your actual project path):**
   ```bash
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

   Example:
   ```bash
   * * * * * cd /var/www/stores-api && php artisan schedule:run >> /dev/null 2>&1
   ```

3. **Save and exit** (if using nano: `Ctrl+X`, then `Y`, then `Enter`)

4. **Verify the cron job is set:**
   ```bash
   crontab -l
   ```

5. **Check scheduled tasks:**
   ```bash
   php artisan schedule:list
   ```

#### Scheduled Tasks in This Project

The following tasks are scheduled in `routes/console.php`:

- **Discount Plans Processing** - Runs every minute
  - Activates scheduled discount plans when start date is reached
  - Expires active discount plans when end date is passed

- **Inventory Alerts** - Runs every hour
  - Checks for low stock products
  - Sends alerts to store owners via Telegram

#### Testing the Scheduler

You can test scheduled tasks manually:

```bash
# Run all due scheduled tasks
php artisan schedule:run

# Run a specific command
php artisan discounts:process
php artisan inventory:check-alerts

# See what would run without actually running
php artisan schedule:work
```

#### Using Supervisor (Recommended for Production)

For production, you might want to use Supervisor to ensure the scheduler always runs:

1. **Install Supervisor:**
   ```bash
   sudo apt-get install supervisor
   ```

2. **Create a config file** `/etc/supervisor/conf.d/laravel-scheduler.conf`:
   ```ini
   [program:laravel-scheduler]
   process_name=%(program_name)s
   command=php /path-to-your-project/artisan schedule:work
   autostart=true
   autorestart=true
   user=www-data
   redirect_stderr=true
   stdout_logfile=/path-to-your-project/storage/logs/scheduler.log
   ```

3. **Reload Supervisor:**
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start laravel-scheduler
   ```

**Note:** You don't need Laravel Horizon for the scheduler. Horizon is only for managing queues. The scheduler works with a simple cron entry or Supervisor.

## 🗄️ Database Schema

### Models & Relationships

#### User
- Manages multiple stores (many-to-many via `store_user`)
- Creates products
- Has cart items
- Places orders
- Has roles and permissions (Spatie)

#### Customer
- Separate model for frontend customers
- Has cart items
- Places orders
- Has wishlist items
- Can receive Telegram notifications

#### Store
- Managed by multiple users (many-to-many)
- Has many products
- Has discount plans
- Receives orders
- Delivers to multiple cities with custom pricing (many-to-many via `city_store_delivery`)
- **Types**: `digital`, `physical`
- **Statuses**: `active`, `inactive`

#### Product
- Belongs to a store
- Created by a user
- Has one main image (`image` field)
- Has many secondary images (`product_images` table)
- Available in cart items
- Can be in discount plans (many-to-many)
- Can be ordered
- **Types**: `digital`, `physical`
- **Statuses**: `active`, `inactive`, `draft`

#### ProductImage
- Belongs to a product
- Has `image_path` and `sort_order`
- Used for secondary product images

#### DiscountPlan
- Belongs to a store
- Created by a user
- Has many products (many-to-many via `discount_plan_products`)
- **Statuses**: `scheduled`, `active`, `expired`
- **Discount Types**: `percentage`, `fixed`
- Automatically activates/expires based on dates

#### City
- Has stores that deliver to it (many-to-many)
- Associated with orders for delivery location

#### CityStoreDelivery
- Pivot model for store-city delivery pricing
- Stores delivery price per city per store

#### CartItem
- Links customer to products
- Stores quantity and price snapshot
- Unique constraint: one product per customer cart

#### Order
- Belongs to a customer (buyer)
- Belongs to a store (seller)
- Has a delivery city (nullable for digital products)
- Contains multiple order items
- Has status history
- **Statuses**: `new`, `processing`, `dispatched`, `complete`, `cancelled`

#### OrderItem
- Belongs to an order
- Links to a product
- Stores quantity and price at order time

#### OrderStatusHistory
- Tracks order status changes
- Stores previous and new status with timestamp

#### WishlistItem
- Links customer to products
- Unique constraint: one product per customer wishlist

#### WishlistShare
- Allows customers to share wishlists
- Generates shareable tokens
- Customizable share messages

#### ApiRequest
- Tracks all API requests
- Stores endpoint, method, response time, status
- Used for analytics and monitoring

### Pivot Tables

- **store_user**: Users managing stores
- **city_store_delivery**: Store delivery coverage with pricing per city
- **discount_plan_products**: Products in discount plans
- **model_has_roles**: Spatie roles assignment
- **model_has_permissions**: Spatie permissions assignment

## 📡 API Documentation

### Base URL
```
/api/v1
```

### Authentication
Most endpoints require authentication via Laravel Sanctum. Include the token in the Authorization header:
```
Authorization: Bearer {token}
```

### Public Endpoints

#### Stores
- `GET /stores` - List all stores (paginated, searchable)
- `GET /stores/{id|slug}` - Get single store
- `GET /stores/{id|slug}/products` - Get store products (paginated)
- `GET /stores/{id|slug}/delivery-prices` - Get delivery prices for all cities

#### Products
- `GET /products` - List all products (paginated, filterable, sortable)
- `GET /products/latest` - Get latest products
- `GET /products/{id|slug}` - Get single product with all images

#### Cities
- `GET /cities` - List all cities

#### Wishlist Sharing
- `GET /wishlist/share/{token}` - View shared wishlist (public)

#### Authentication
- `POST /auth/register` - Register new customer
- `POST /auth/login` - Login customer

### Protected Endpoints (Require Authentication)

#### Authentication
- `GET /auth/user` - Get authenticated user
- `POST /auth/logout` - Logout user

#### Cart
- `GET /cart` - Get user's cart
- `POST /cart` - Add item to cart
- `PUT /cart/{cart_item}` - Update cart item quantity
- `DELETE /cart/{cart_item}` - Remove item from cart
- `DELETE /cart` - Clear entire cart

#### Orders
- `GET /orders` - Get user's orders (paginated)
- `GET /orders/{order}` - Get single order details
- `POST /orders` - Create new order

#### Wishlist
- `GET /wishlist` - Get user's wishlist
- `POST /wishlist` - Add product to wishlist
- `DELETE /wishlist/{wishlist_item}` - Remove from wishlist
- `GET /wishlist/check/{product_id}` - Check if product in wishlist
- `GET /wishlist/share` - Get wishlist share info
- `POST /wishlist/share` - Generate share link
- `PUT /wishlist/share/message` - Update share message
- `PUT /wishlist/share/toggle` - Toggle sharing

#### Telegram
- `GET /telegram/activation-link` - Get Telegram bot activation link

### Response Format

All API responses follow a consistent structure:

```json
{
    "status": true,
    "message": "Success message",
    "data": [...],
    "meta": {
        "current_page": 1,
        "last_page": 10,
        "per_page": 15,
        "total": 150
    }
}
```

### Product Images Format

Products include an `images` array with all images (main + secondary):

```json
{
    "id": 1,
    "name": "Product Name",
    "image": "https://...",  // Main image (backward compatible)
    "images": [
        {
            "url": "https://...",
            "is_main": true,
            "sort_order": 0
        },
        {
            "url": "https://...",
            "is_main": false,
            "sort_order": 1
        }
    ]
}
```

### API Documentation

Interactive API documentation is available at `/docs/api` (generated by Scramble).

## 🎛️ Admin Panel

The admin panel is built with Filament 4 and is fully localized (English/Arabic).

### Resources

#### Store Management
- **Stores** - Manage stores, view products, delivery zones
- **Products** - Manage products, images, stock, discounts
- **Discount Plans** - Create and manage discount plans with scheduled activation
- **City Store Deliveries** - Configure delivery prices per city per store

#### Orders Management
- **Orders** - View and manage orders, update status, send customer messages
- **Order Items** - View order details with product images and SKUs

#### User Management
- **Users** - Manage admin users with roles and permissions
- **Customers** - View and manage frontend customers
- **Roles** - Manage user roles
- **Permissions** - Manage granular permissions

#### System
- **API Requests** - Monitor API usage, response times, errors
- **Documentation** - Built-in documentation pages for each resource

### Dashboard Widgets

#### Super Admin
- **Stats Overview** - Total stores, products, orders, revenue
- **Orders Chart** - Orders over time
- **Orders by Status** - Pie chart of order statuses
- **Latest Orders** - Recent orders table
- **API Requests Stats** - API usage statistics
- **API Requests by Endpoint** - Chart of endpoint usage
- **API Response Time Chart** - Performance metrics
- **API Errors Table** - Error tracking

#### Store Owners
- **User Store Stats** - Personalized statistics for their stores
- Sales, orders, products count
- Last update time with manual refresh

### Features

- **Full Localization** - All resources, forms, tables, and messages are localized
- **Role-Based Access** - Users can only access their assigned stores
- **Rich Text Editor** - Send formatted messages to customers via Telegram
- **Image Galleries** - Manage multiple product images
- **Discount Management** - Automated discount plan processing
- **Order Status Tracking** - Complete order history
- **Customer Notifications** - Send notifications on order status changes
- **Documentation Pages** - Helpful guides for each resource
- **Dark Mode** - Full dark mode support
- **Responsive Design** - Works on all devices

## 🔧 Services

### CartService
- Manages shopping cart operations
- Handles price snapshots
- Validates stock availability

### CustomerMessageService
- Sends formatted messages to customers via Telegram
- Converts rich text to Telegram-compatible HTML
- Handles order status change notifications
- Handles delivery price change notifications
- Generates clickable order links

### DiscountService
- Calculates discounted prices
- Activates scheduled discount plans
- Expires active discount plans
- Updates product discounts when plans change
- Applies discounts to newly added products

### OrderService
- Creates orders from cart
- Manages order status transitions
- Tracks order history
- Calculates totals including delivery

### ProductService
- Retrieves products with filtering and sorting
- Formats product images (main + secondary)
- Handles wishlist status
- Supports search and pagination

### StoreService
- Manages store operations
- Handles delivery price lookups
- Supports store search

### StoreOwnerNotificationService
- Notifies store owners of new orders
- Sends Telegram notifications

### StoreStatusService
- Manages store status transitions
- Handles activation/deactivation

### TelegramService
- Direct interaction with Telegram Bot API
- Sends messages with HTML formatting
- Handles bot activation links

### WishlistService
- Manages wishlist operations
- Handles wishlist sharing
- Generates share tokens

## 📊 Enums

### OrderStatus
- `new` - Initial order state
- `processing` - Being prepared
- `dispatched` - Shipped/Dispatched
- `complete` - Delivered/Completed
- `cancelled` - Order cancelled

### ProductStatus
- `active` - Available for purchase
- `inactive` - Not available
- `draft` - Not published

### ProductType & StoreType
- `digital` - Digital goods (no delivery needed)
- `physical` - Physical goods (requires delivery)

### DiscountPlanStatus
- `scheduled` - Scheduled for future activation
- `active` - Currently active
- `expired` - Past end date

### DiscountType
- `percentage` - Percentage discount (e.g., 20%)
- `fixed` - Fixed amount discount (e.g., 5000 IQD)

### StoreStatus
- `active` - Store is operational
- `inactive` - Store is temporarily disabled

## 🧪 Testing

This project uses Pest for testing:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ProductControllerTest.php

# Run with coverage
php artisan test --coverage

# Run with filter
php artisan test --filter=testName
```

### Test Structure
- **Feature Tests** - API endpoints, services, integrations
- **Unit Tests** - Individual components and utilities

## 📝 Code Standards

This project follows Laravel conventions:

- **Variables/Methods**: `snake_case`
- **Classes**: `PascalCase`
- **Enum Keys**: `PascalCase` (e.g., `New`, `Active`)
- **Code Formatting**: Laravel Pint (`vendor/bin/pint`)
- **Type Declarations**: Always use explicit return types and parameter types
- **PHPDoc**: Use PHPDoc blocks for complex methods
- **Constructors**: Use PHP 8 constructor property promotion

### Code Formatting

```bash
# Format all files
vendor/bin/pint

# Format only changed files
vendor/bin/pint --dirty
```

## 🌐 Localization

The application is fully localized in English and Arabic.

### Backend Localization
- All Filament resources, forms, tables, and messages
- Translation files in `lang/en/` and `lang/ar/`
- Resources: `products`, `orders`, `discount-plans`, `city-store-deliveries`, `dashboard`, `documentation`

### Frontend Localization
- React components use `react-i18next`
- Translation files in `resources/js/locales/en/` and `resources/js/locales/ar/`
- Supports product names, cart messages, wishlist, etc.

### Language Switching
- Filament admin panel includes language switcher
- Frontend language persists in localStorage

## 🚀 Key Features in Detail

### Discount Plans
- Create discount plans with start/end dates
- Support percentage or fixed amount discounts
- Automatically activate scheduled plans
- Automatically expire past plans
- Products can belong to multiple plans (only one active at a time)
- Discounts are calculated and stored on products
- Frontend displays discount badges and discounted prices

### Multiple Product Images
- Each product has one main image (stored in `products.image`)
- Products can have multiple secondary images (stored in `product_images` table)
- Images are sorted by `sort_order`
- Frontend displays image gallery with navigation
- Product cards show first available image

### Customer Notifications
- Automated notifications via Telegram on:
  - Order status changes
  - Delivery price changes
  - Custom messages from admin
- Rich text support (bold, italic, underline, links, lists)
- Clickable order links in messages
- Formatted messages with order details

### Wishlist Sharing
- Customers can share wishlists with others
- Generates unique shareable tokens
- Customizable share messages
- Public viewing of shared wishlists

### API Metrics
- Tracks all API requests
- Monitors response times
- Tracks errors and endpoints
- Dashboard widgets for analytics

### Dashboard Cache
- Widget data is cached for performance
- Store owners can manually refresh their stats
- Shows last update time
- Cache clears automatically on order updates

## 📚 Additional Resources

### Console Commands
- `php artisan discounts:process` - Process discount plans (activate/expire)
- `php artisan permissions:seed` - Seed permissions for resources

### Artisan Commands
- Standard Laravel commands available
- Use `php artisan list` to see all commands

### API Documentation
- Visit `/docs/api` for interactive API documentation
- Generated using Dedoc Scramble
- Includes request/response examples

---

**Status**: ✅ Production Ready

**Version**: 1.0.0

**Maintained by**: https://dhurgham.dev

**License**: MIT
