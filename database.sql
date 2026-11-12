CREATE DATABASE IF NOT EXISTS countries_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE countries_db;

CREATE TABLE countries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL,
  capital VARCHAR(100) NOT NULL
);

CREATE TABLE cities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  country_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE
);

INSERT INTO countries (name, slug, capital) VALUES 
('Ukraine', 'ukraine', 'Kyiv'),
('Poland', 'poland', 'Warsaw');

INSERT INTO cities (country_id, name) VALUES 
(1, 'Kyiv'),
(1, 'Lviv'),
(2, 'Warsaw'),
(2, 'Krakow');