CREATE DATABASE IF NOT EXISTS iv;
USE iv;

CREATE TABLE IF NOT EXISTS  users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(500) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    usdt_trc20 VARCHAR(255),
    eth_erc20 VARCHAR(255),
    bitcoin VARCHAR(255),
    email VARCHAR(255) UNIQUE NOT NULL,
    recovery_question VARCHAR(255) NOT NULL,
    recovery_answer VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);




