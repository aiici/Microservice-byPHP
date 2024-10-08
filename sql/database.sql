CREATE DATABASE demo_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
use demo_service;
CREATE USER 'user'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON demo_service.* TO 'user'@'%';
FLUSH PRIVILEGES;

-- 用户服务数据库表
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(10) NOT NULL
);
INSERT INTO users(username,password,role) VALUES("admin","$2y$10$NXq8XvDmmryHBL7vp3nMXO43OP40vllJeENNoJioLBbSdUmvHYBQ6","admin");
-- 图书服务数据库表
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    author VARCHAR(100) NOT NULL
);

-- 借阅服务数据库表
CREATE TABLE borrows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date DATETIME NOT NULL,
    return_date DATETIME,
    status ENUM('borrowed', 'returned') DEFAULT 'borrowed', -- 借阅状态
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (book_id) REFERENCES books(id)
);
