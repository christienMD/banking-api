# Banking API

A RESTful API for a banking system built with PHP and Laravel.

## Overview

This Banking API provides secure endpoints for managing customers, accounts, and transactions. It's designed for internal use by bank employees and includes authentication, role-based access control, and comprehensive API key security.

## Features

- **Authentication System**: Secure login/logout for bank employees
- **API Key Security**: All endpoints require valid API keys
- **Customer Management**: Create, view, update, and delete customer records
- **Account Management**: Create accounts with initial deposits and retrieve balances
- **Transaction Processing**: Transfer money between accounts
- **Transaction History**: Retrieve transaction records for any account

## Requirements

- PHP 8.1+
- Laravel 12
- MySQL 8.0+
- Composer

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/banking-api.git
cd banking-api
```

2. Install dependencies:
```bash
composer install
```

3. Configure environment:
```bash
cp .env.example .env
# Update database settings in .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Run migrations:
```bash
php artisan migrate
```

6. Generate an API key:
```bash
php artisan api:key:generate "Development Key"
```

## API Endpoints

### Authentication
- `POST /api/auth/login` - Authenticate and get a token
- `POST /api/auth/logout` - Invalidate token

### Customers
- `GET /api/customers` - List all customers
- `GET /api/customers/{id}` - Get a customer's details
- `POST /api/customers` - Create a new customer
- `PUT /api/customers/{id}` - Update a customer
- `DELETE /api/customers/{id}` - Delete a customer

### Accounts
- `GET /api/customers/{customerId}/accounts` - List accounts for a customer
- `POST /api/customers/{customerId}/accounts` - Create a new account
- `GET /api/accounts/{accountId}` - Get account details
- `GET /api/accounts/{accountId}/balance` - Get account balance

### Transactions
- `POST /api/transactions` - Create a money transfer
- `GET /api/accounts/{accountId}/transactions` - Get transaction history

## Security

All endpoints require:
1. A valid API key in the `X-API-Key` header
2. Authentication via bearer token (except login)

## API Documentation

API documentation is available via Postman. You can access it 
[here](https://documenter.getpostman.com/view/23975272/2sAYkHpJZR).
[here](https://documenter.getpostman.com/view/23975272/2sAYkHpJZS).
[here](https://documenter.getpostman.com/view/23975272/2sAYkHpJZV).
[here](https://documenter.getpostman.com/view/23975272/2sAYkHpJdo).

