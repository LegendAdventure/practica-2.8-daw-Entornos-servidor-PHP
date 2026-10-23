-- =====================================================================
-- Base de datos: practica
-- Proyecto: Tablón de anuncios de la clase
-- Práctica final 2.8 - PHP
-- =====================================================================
-- Este script crea las tablas users, posts y comments, y añade datos
-- de prueba. Está pensado para importarse sobre la base de datos
-- "practica" que crea docker-compose.yml (MYSQL_DATABASE: practica).
-- =====================================================================

USE practica;

-- ---------------------------------------------------------------------
-- Tabla: users
-- Usuarios registrados. La contraseña se guarda con password_hash(),
-- nunca en texto plano ni con md5().
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    email       VARCHAR(255)    NOT NULL,
    username    VARCHAR(50)     NOT NULL,
    password    VARCHAR(255)    NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email    (email),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: posts
-- Anuncios publicados por los usuarios. Cada post pertenece a un
-- único usuario (relación 1:N users -> posts).
-- Si se borra el usuario, se borran sus posts (ON DELETE CASCADE).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS posts (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED    NOT NULL,
    title       VARCHAR(150)    NOT NULL,
    body        TEXT            NOT NULL,
    category    VARCHAR(50)     NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_posts_user_id  (user_id),
    KEY idx_posts_category (category),
    CONSTRAINT fk_posts_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: comments
-- Comentarios de los usuarios sobre los posts (relación 1:N
-- posts -> comments, y también 1:N users -> comments).
-- Si se borra el post o el usuario, se borran sus comentarios.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS comments (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    post_id     INT UNSIGNED    NOT NULL,
    user_id     INT UNSIGNED    NOT NULL,
    body        TEXT            NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_comments_post_id (post_id),
    KEY idx_comments_user_id (user_id),
    CONSTRAINT fk_comments_post
        FOREIGN KEY (post_id) REFERENCES posts (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_comments_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- Datos de prueba
-- =====================================================================
-- Contraseña para TODOS los usuarios de prueba: "1234"
-- Hash generado con:
--   password_hash('1234', PASSWORD_DEFAULT)
-- El hash de abajo es un ejemplo real de bcrypt para "1234".
-- (password_verify('1234', $hash) devuelve true)
-- =====================================================================

INSERT INTO users (email, username, password) VALUES
    ('ana@iescelia.org',      'ana',      '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdB4Z1pUyXy6uVf9K'),
    ('bruno@iescelia.org',    'bruno',    '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdB4Z1pUyXy6uVf9K'),
    ('carla@iescelia.org',    'carla',    '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdB4Z1pUyXy6uVf9K');

INSERT INTO posts (user_id, title, body, category) VALUES
    (1, 'Vendo libro de Desarrollo en Entorno Servidor',
        'Libro en buen estado, sin subrayados. Precio: 20€. Interesados contactar por email.',
        'Libros'),
    (2, 'Busco grupo para proyecto de PHP',
        'Somos dos y buscamos un tercero para el proyecto final. Nivel medio de PHP. Aula 21 los martes.',
        'Proyectos'),
    (3, 'Cargador olvidado en el aula 21',
        'Encontré un cargador de portátil USB-C en el aula 21 el martes por la tarde. Lo he dejado en conserjería.',
        'Objetos perdidos'),
    (1, 'Vendo calculadora científica Casio',
        'Casio fx-991EX, funciona perfectamente. 15€. Ideal para matemáticas.',
        'Material');

INSERT INTO comments (post_id, user_id, body) VALUES
    (1, 2, '¿Sigue disponible el libro? Me interesa.'),
    (1, 3, 'Yo también estoy interesada si no lo coge Bruno.'),
    (2, 1, 'Yo podría unirme al grupo. ¿Hablamos por email?'),
    (3, 2, '¡Gracias por dejarlo en conserjería! Era mío, ya lo he recogido.'),
    (4, 3, '¿Tiene funda? Si la tiene, me la quedo.');