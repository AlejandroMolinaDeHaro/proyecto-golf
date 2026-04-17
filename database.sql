-- ============================================
-- Golf Pro Apate - Database Schema
-- Ejecutar en phpMyAdmin o MySQL CLI
-- ============================================

CREATE DATABASE IF NOT EXISTS golf_pro_apate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE golf_pro_apate;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Tabla de carrito de compra
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
