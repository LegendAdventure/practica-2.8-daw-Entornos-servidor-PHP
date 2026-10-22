CREATE TABLE articles (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    description TEXT NULL,
    price       DECIMAL(8,2) NOT NULL DEFAULT 0,
    stock       INT NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Puedes poner otros artículos aquí, solo son datos de prueba
INSERT INTO articles (title, description, price, stock) VALUES
('Teclado mecánico', 'Teclado mecánico retroiluminado, switches rojos', 59.99, 15),
('Ratón inalámbrico', 'Ratón óptico inalámbrico, 2.4GHz', 19.95, 40),
('Monitor 27"', 'Monitor IPS Full HD de 27 pulgadas, 75Hz', 149.00, 8);