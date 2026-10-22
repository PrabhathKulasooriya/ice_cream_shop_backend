# Ice Cream Shop Backend API

A robust RESTful API backend for an Ice Cream Shop management system built with **Laravel 11**, **MySQL**, and **Laravel Passport** (OAuth2 Bearer Token Authentication) featuring **Role-Based Access Control (RBAC)**.

---

## Table of Contents
- [Project Overview](#project-overview)
- [System Requirements](#system-requirements)
- [Installation & Setup](#installation--setup)
- [Environment Configuration](#environment-configuration)
- [Database Setup & Seeding](#database-setup--seeding)
- [Default Seed Data & Credentials](#default-seed-data--credentials)
- [Running the Backend](#running-the-backend)
- [API Reference & Role Permissions](#api-reference--role-permissions)

---

## Project Overview

This backend powers the point-of-sale and back-office operations of an ice cream parlor. Key features include:
- **Authentication**: Token-based authentication using Laravel Passport (`auth:api` guard).
- **Role-Based Access Control (RBAC)**: Fine-grained middleware authorization supporting `owner`, `manager`, and `cashier`.
- **Inventory & Item Management**: Manage ice cream products categorized by flavor profiles with stock tracking and pricing.
- **Customer Management**: Register, update, and manage customer contacts with email uniqueness validation.

---

## System Requirements
- **PHP**: `^8.2` (with `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`)
- **Composer**: `^2.x`
- **Database**: MySQL `^8.0` or MariaDB `^10.4` (WampServer, XAMPP, or standalone)
- **Web Server**: Apache / Nginx or PHP Built-in Server

---

## Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd IceCreamShop-backend
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

---

## Environment Configuration

1. **Create the environment file:**
   Copy the provided `.env.example` template:
   ```bash
   cp .env.example .env
   ```
   *(On Windows Command Prompt: `copy .env.example .env` / On PowerShell: `Copy-Item .env.example .env`)*

2. **Generate the application encryption key:**
   ```bash
   php artisan key:generate
   ```

3. **Configure your Database credentials in `.env`:**
   Open `.env` and verify the database configuration:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ice_cream_shop_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   > Ensure the database `ice_cream_shop_db` exists in your MySQL server before running migrations.

---

## Database Setup & Seeding

1. **Run Database Migrations:**
   ```bash
   php artisan migrate
   ```

2. **Generate Passport OAuth Keys & Personal Access Client:**
   ```bash
   php artisan passport:keys
   php artisan passport:client --personal --name="IceCreamShop Personal Access Client"
   ```
   *(Or simply run `php artisan passport:install`)*

3. **Seed Database with Initial Data:**
   ```bash
   php artisan db:seed
   ```
   This executes the following seeders in order:
   - `UserSeeder`: Creates default staff accounts (`owner`, `manager`, `cashier`).
   - `CategorySeeder`: Creates 5 core ice cream flavor categories.
   - `ItemSeeder`: Creates 2 initial ice cream items for each flavor category (10 total products).

---

## Default Seed Data & Credentials

### Initial Staff Accounts
All default accounts share the password: **`123456`**

| Role | Name | Email | Password | Permissions Summary |
| :--- | :--- | :--- | :--- | :--- |
| **Owner** | Shop Owner | `owner@icecream.com` | `123456` | Full administrative control (create, update, and delete any resource). |
| **Manager** | Shop Manager | `manager@icecream.com` | `123456` | Can view, create, and update items & customers. Cannot delete. |
| **Cashier** | Shop Cashier | `cashier@icecream.com` | `123456` | Can view items, create/view/update customers. Cannot manage inventory. |

### Seeded Flavor Categories & Sample Items

1. **Vanilla Flavors**
   - *Madagascar Vanilla Bean* — $3.50 (Qty: 50)
   - *French Vanilla Custard* — $3.80 (Qty: 40)
2. **Chocolate Flavors**
   - *Belgian Dark Chocolate* — $4.20 (Qty: 45)
   - *Chocolate Fudge Brownie* — $4.50 (Qty: 35)
3. **Fruit & Berry Flavors**
   - *Wild Strawberry Swirl* — $3.90 (Qty: 40)
   - *Alphonso Mango Sorbet* — $4.00 (Qty: 30)
4. **Nutty & Caramel Flavors**
   - *Salted Caramel Crunch* — $4.50 (Qty: 35)
   - *Sicilian Roasted Pistachio* — $4.80 (Qty: 25)
5. **Coffee & Mint Flavors**
   - *Espresso Mocha Chip* — $4.20 (Qty: 30)
   - *Cool Mint Chocolate Chip* — $3.90 (Qty: 40)

---

## Running the Backend

Start the local Laravel development server:
```bash
php artisan serve
```

The API will be accessible at:
```
http://127.0.0.1:8000
```
API Base URL:
```
http://127.0.0.1:8000/api
```

---

## API Reference & Role Permissions

All protected endpoints require the following headers:
- `Accept`: `application/json`
- `Content-Type`: `application/json`
- `Authorization`: `Bearer <token>`

### 1. Authentication Endpoints

| Method | Endpoint | Access | Description |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/auth/login` | Public | Authenticates user credentials and returns a Personal Access Token. |
| `POST` | `/api/auth/logout` | Authenticated | Revokes current access token. |
| `GET` | `/api/auth/user` | Authenticated | Returns profile of the logged-in user. |

### 2. Item & Inventory Endpoints

| Method | Endpoint | Allowed Roles | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/items` | `owner`, `manager`, `cashier` | Retrieve all items with their category relationship. |
| `GET` | `/api/items/{item}` | `owner`, `manager`, `cashier` | Retrieve details of a specific item. |
| `POST` | `/api/items` | `owner`, `manager` | Create a new ice cream item. |
| `PUT` | `/api/items/{item}` | `owner`, `manager` | Update an existing item's details, stock, or price. |
| `DELETE` | `/api/items/{item}` | `owner` | Permanently delete an item from inventory. |

### 3. Customer Endpoints

| Method | Endpoint | Allowed Roles | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/customers` | `owner`, `manager`, `cashier` | Retrieve list of all registered customers. |
| `GET` | `/api/customers/{customer}` | `owner`, `manager`, `cashier` | Retrieve details of a specific customer. |
| `POST` | `/api/customers` | `owner`, `manager`, `cashier` | Register a new customer record. |
| `PUT` | `/api/customers/{customer}` | `owner`, `manager`, `cashier` | Update customer contact information. |
| `DELETE` | `/api/customers/{customer}` | `owner` | Permanently delete a customer record. |

