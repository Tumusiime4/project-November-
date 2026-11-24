CREATE DATABASE IF NOT EXISTS university_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE university_db;

CREATE TABLE IF NOT EXISTS students_registration (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    age TINYINT UNSIGNED NOT NULL,
    course VARCHAR(100) NOT NULL,
    registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_students_registration_email (email)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
