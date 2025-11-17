CREATE DATABASE IF NOT EXISTS events_gifts_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE events_gifts_db;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS gifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    CONSTRAINT fk_gifts_event
        FOREIGN KEY (event_id) REFERENCES events(id)
        ON DELETE CASCADE
);
