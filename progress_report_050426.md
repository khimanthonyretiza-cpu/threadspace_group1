# Polyglot E‑Commerce Project – Daily Progress Report

**Date:** 4 May 2026  
**Project:** Polyglot Persistence Web Application (MySQL + MongoDB)  
**Team:** [Your Team Name]  
**Repository:** [Link to your repository]  

---

## 1. Overview

Today’s session was focused on building a fully functional e‑commerce web application using **polyglot persistence** – combining a relational database (MySQL) for transactional data and a document database (MongoDB) for flexible product information. The tech stack is **HTML, CSS, a little JavaScript, and PHP**.

We designed the database schemas, set up the entire development environment (XAMPP, MongoDB, Composer, VS Code), implemented all application pages, and finally committed the codebase to Git.

---

## 2. Architecture & Polyglot Split

**Why two databases?**  
- **MySQL** handles everything that requires ACID compliance: users, orders, order items, inventory, and shopping cart.  
- **MongoDB** stores the product catalogue. Each product category (Shirts, Footwear, Accessories, Bags) has different attributes – a document model avoids rigid table structures and makes adding new product types trivial.

**Data flow example:**  
An order in MySQL contains `product_id` (a string matching the MongoDB `_id`). When displaying order history, we fetch product names and images from MongoDB using those IDs.

**Tables & Collections:**

**MySQL (`ecom_store`):**
- `users`
- `inventory` (includes a `category` column to enable quick stock queries without hitting MongoDB)
- `orders`
- `order_items`
- `cart`

**MongoDB (`ecom_store.products`):**
- Document structure with `name`, `price`, `category`, `description`, `image_url`, `attributes` (dynamic per category)

---

## 3. Environment Setup (Detailed)

### 3.1 XAMPP & Apache
- XAMPP with PHP 8.2.12, Thread Safe, x64, VS16.
- Apache and MySQL started successfully.

### 3.2 MongoDB Community Server
- Installed as a Windows service on port `27017`.
- Verified it was running.

### 3.3 MongoDB PHP Extension (Major Challenge)
This was the biggest hurdle of the day.

**Error:** MongoDB section not appearing in `phpinfo()`.

**Steps taken to resolve:**
1. Identified PHP configuration: PHP 8.2.12, Thread Safe, x64, compiler VS16.
2. Downloaded the matching DLL from PECL: `php_mongodb-1.17.2-8.2-ts-vs16-x64.zip`.
3. Extracted `php_mongodb.dll` into `C:\xampp\php\ext\`.
4. Edited `C:\xampp\php\php.ini` – added `extension=mongodb` (uncommented).
5. Restarted Apache – still no MongoDB section.
6. Checked Apache error log – no specific message.
7. Installed Visual C++ Redistributable (vc_redist.x64.exe) – required by the DLL.
8. Restarted Apache – extension finally appeared in `phpinfo`.

**Lesson learned:** Always check the exact PHP build (TS/NTS, architecture) and ensure the VC++ runtime is installed.

### 3.4 Composer & MongoDB PHP Library
- Composer was installed but initially missing the `zip` PHP extension.
- **Error:** `The zip extension and unzip/7z commands are both missing`.
- **Fix:** Enabled `extension=zip` in `php.ini`, restarted Apache, ran `composer require mongodb/mongodb` successfully.
- Version 1.17.1 of the library was locked (compatible with our driver 1.17.2).

### 3.5 Git Installation
- Git was not installed initially (`'git' is not recognized`).
- Installed Git for Windows, configured PATH, initialized repo, made initial commit and pushed to remote repository.

---

## 4. Database Schema & Seed Data

### 4.1 MySQL Schema
All tables created in `ecom_store`:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer','admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE inventory (
    product_id VARCHAR(24) PRIMARY KEY,
    category ENUM('shirts','footwear','accessories','bags') NOT NULL,
    stock INT NOT NULL DEFAULT 0
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id VARCHAR(24) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id VARCHAR(24) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id)
);