-- Base de Datos para Sprint 2 (MariaDB / MySQL)
CREATE DATABASE IF NOT EXISTS sprint2_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sprint2_db;

-- 1. Tabla Roles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (id, nombre) VALUES (1, 'Vendedor'), (2, 'Cliente');

-- 2. Tabla Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Usuarios de prueba (Password para ambos: password123)
INSERT INTO usuarios (id, nombre, email, password, rol_id) VALUES 
(1, 'Vendedor Principal', 'admin@tienda.com', '123456', 1),
(2, 'Juan Pérez', 'cliente@tienda.com', '123456', 2),
(3, 'Ximena Flores', 'xime@tienda.com', '123456', 1);

-- 3. Tabla Categorías y Productos (para Catálogo CRUD)
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

INSERT INTO categorias (id, nombre) VALUES (1, 'Electrónica'), (2, 'Ropa'), (3, 'Hogar');

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    imagen VARCHAR(255) DEFAULT 'default.jpg',
    descripcion TEXT,
    categoria_id INT,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

INSERT INTO productos (nombre, precio, imagen, descripcion, categoria_id) VALUES
('Laptop Gaming Pro', 1200.00, 'laptop.jpg', 'Laptop de alto rendimiento.', 1),
('Mouse Inalámbrico', 25.50, 'mouse.jpg', 'Mouse ergonómico Bluetooth.', 1);

-- 4. Tabla Pedidos (Según especificación exacta del requerimiento)
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,        -- ID (PK Auto incrementable)
    nombre VARCHAR(150) NOT NULL,              -- Nombre del Pedido / Cliente
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,  -- Fecha
    estado ENUM('Pendiente', 'Aceptado', 'Rechazado') DEFAULT 'Pendiente', -- Estado
    nombre_vendedor VARCHAR(100) DEFAULT 'Por asignar', -- NombreVendedor
    usuario_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 5. Tabla Carrito / Detalle (Según especificación exacta)
CREATE TABLE IF NOT EXISTS carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,                  -- ID_Producto(FK)
    id_pedido INT NOT NULL,                    -- ID_Pedido(FK)
    cantidad INT NOT NULL DEFAULT 1,           -- Cantidad
    costo_total DECIMAL(10,2) NOT NULL,        -- CostoTotal -> (Calculado)
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE
);