-- Hobi Kulübü Yönetim Sistemi - Veritabanı Kurulum Dosyası
-- Bu dosyayı phpMyAdmin'de ya da MySQL konsolunda çalıştırın.

CREATE DATABASE IF NOT EXISTS hobi_kulubu CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

USE hobi_kulubu;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- password_hash() ile saklanır
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Kulüpler tablosu (users'tan AYRI tablo — gereksinim gereği)
CREATE TABLE IF NOT EXISTS clubs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT          NOT NULL,          -- hangi kullanıcı ekledi
    name         VARCHAR(100) NOT NULL,
    category     VARCHAR(50)  NOT NULL,
    description  TEXT,
    founded_date DATE,
    member_count INT          NOT NULL DEFAULT 0,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
