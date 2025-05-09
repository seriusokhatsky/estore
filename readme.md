# ESTORE API

A Laravel-based RESTful API for an e-commerce platform with multi-role support (Admin, Seller, Buyer). This project demonstrates best practices in API development, authentication, and database management using Laravel.

## 🚀 Features

- Multi-role authentication (Admin, Seller, Buyer)
- Product management system
- Order processing
- Payment integration
- Seller dashboard
- Admin dashboard
- RESTful API architecture
- Laravel Sanctum authentication

## 📋 Prerequisites

Before you begin, ensure you have the following installed:
- PHP >= 8.2
- Composer
- PostgreSQL >= 8.0
- Node.js & NPM

## 🔧 Installation

1. Clone the repository
```bash
git clone https://github.com/seriusokhatsky/estore
cd estore
```

2. Install PHP dependencies
```bash
composer install
```

3. Create environment file
```bash
cp .env.example .env
```

4. Generate application key
```bash
php artisan key:generate
```

5. Configure your database in `.env` file
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=estore
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run migrations and seeders
```bash
php artisan migrate --seed
```

7. Start the development server
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## 🔐 Authentication

This API uses Laravel Sanctum for authentication. To access protected routes.

## 📡 API Routes

### Authentication
- `POST /api/login` - Login user
- `POST /api/register` - Register a new user
- `GET /api/logout` - Logout user (requires authentication)

### Admin Routes
- `GET /api/admin/orders` - List all orders
- `DELETE /api/admin/orders/{order}` - Delete an order
- `PATCH /api/admin/orders/{order}/status` - Update order status

### Seller Dashboard
- `GET /api/seller.dashboard/products` - List seller's products
- `POST /api/seller.dashboard/products` - Create new product
- `GET /api/seller.dashboard/products/{id}` - Get product details
- `PUT /api/seller.dashboard/products/{id}` - Update product
- `DELETE /api/seller.dashboard/products/{id}` - Delete product
- `GET /api/seller.dashboard/orders` - List seller's orders
- `GET /api/seller.dashboard/orders/{id}` - Get order details

### Buyer Routes
- `GET /api/products` - List all products
- `GET /api/products/{product}` - Get product details
- `GET /api/orders` - List buyer's orders
- `POST /api/orders` - Create new order
- `POST /api/payments` - Process payment

### Seller Collection (Public)
- `GET /api/seller` - List all sellers
- `GET /api/seller/{id}` - Get seller details
- `GET /api/seller/{id}/products` - Get seller's products
- `GET /api/products/{product}` - Get specific product

## 🗄️ Database Models

### User
- Supports multiple roles (admin, seller, buyer)
- Manages authentication
- Has relationships with products and orders

### Product
- Belongs to a seller (User)
- Contains product details
- Related to orders

### Order
- Belongs to a buyer (User)
- Contains order details
- Related to products and payments

### Payment
- Handles payment processing
- Related to orders

## 🧪 Testing

Run the test suite using:

```bash
php artisan test
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

- Serhii Sokhatskyi

## 🙏 Acknowledgments

- Laravel Documentation
- Laravel Sanctum Documentation
- All contributors who have helped this project grow
