-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS tienda_producto;

-- Usar la base de datos
USE tienda_producto;

-- Crear la tabla para los usuarios (login_user)
CREATE TABLE IF NOT EXISTS login_user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- -- TABLA MODIFICADA: log_sistema --
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS log_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY, -- << AÑADIDO: Llave primaria autoincremental
    session_id VARCHAR(64) NOT NULL UNIQUE, -- << CAMBIADO: Ya no es llave primaria, pero sigue siendo único
    username VARCHAR(50) NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL
);

-- Crear la tabla para los productos registrados (productos)
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    cantidad INT NOT NULL,
    imagen_larga VARCHAR(255) DEFAULT NULL,
    imagen_miniatura VARCHAR(255) DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);