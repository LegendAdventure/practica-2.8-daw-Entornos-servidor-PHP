# Tablón de anuncios de la clase

Aplicación web desarrollada en PHP puro (renderizado en el servidor, SSR) como
práctica final de la unidad de PHP (2.8). Permite a usuarios registrados
publicar anuncios organizados por categoría y comentar los anuncios de otros.

## Características

- **Autenticación de usuarios**: registro, inicio de sesión y cierre de sesión.
  Las contraseñas se almacenan con `password_hash()` (bcrypt) y se verifican con
  `password_verify()`. Nunca se guardan en texto plano.
- **Publicaciones (anuncios)**: cada usuario puede crear, editar y borrar sus
  propios anuncios. Los anuncios tienen título, cuerpo y categoría.
- **Permisos por propiedad**: solo el autor de un anuncio puede editarlo o
  borrarlo. Si otro usuario intenta hacerlo, recibe un 403.
- **Comentarios**: los usuarios registrados pueden comentar cualquier anuncio.
  Relación 1:N (un post tiene muchos comentarios). El `user_id` del comentario
  se toma siempre de la sesión, nunca de un campo del formulario.
- **Búsqueda**: filtro por título o categoría con `LIKE`.
- **Protección CSRF**: todos los formularios POST incluyen un token CSRF
  (generado una vez por sesión) que se valida con `hash_equals()`.
- **Rutas limpias**: `/posts/show/1`, `/posts/edit/1`, `/posts/delete/1`. El
  router también acepta la forma antigua con query string (`?id=1`) por
  compatibilidad.
- **Escapado de salida**: todo lo que viene de la BD se pinta con
  `htmlspecialchars()` para evitar XSS.

## Tecnologías

- **PHP 8.3** con PDO y sentencias preparadas.
- **MySQL 8.4**.
- **Apache 2.4** con `mod_rewrite`.
- **Docker** y **Docker Compose** para el entorno de desarrollo.
- **Composer** para el autoload PSR-4.
- **phpMyAdmin** para administrar la BD.

## Estructura del proyecto

```text
tablon-anuncios/
├── composer.json               # Autoload PSR-4: "App\\": "src/"
├── composer.lock               # Versiones exactas de las dependencias
├── config.php                  # Lee .env y devuelve el array de configuración
├── ConversacionIA.md           # Conversación con la IA (entregable)
├── database.sql                # Esquema + datos de prueba
├── docker-compose.yml          # Servicios web, db, phpmyadmin
├── Dockerfile                  # Imagen PHP + Apache con mod_rewrite
├── php/
│   └── custom.ini              # Configuración PHP personalizada
├── public/                     # DocumentRoot de Apache
│   └── index.php               # Front controller + rutas + csrf_field()
├── README.md                   # Documentación del proyecto
├── Repositorio.txt             # Link al repositorio (entregable)
├── src/
│   ├── Controllers/
│   │   ├── AuthController.php      # Registro, login, logout
│   │   ├── CommentController.php   # Añadir comentarios
│   │   └── PostController.php      # CRUD de anuncios + permisos
│   ├── Core/
│   │   ├── Database.php            # Singleton PDO + query/fetchAll/fetchOne
│   │   └── Router.php              # Router con rutas limpias
│   ├── Models/
│   │   ├── BaseModel.php           # CRUD genérico (getAll, getById, insert…)
│   │   ├── Comment.php             # Modelo Comment (hereda de BaseModel)
│   │   ├── Post.php                # Modelo Post (JOINs + búsqueda)
│   │   └── User.php                # Modelo User (findByEmail)
│   └── Views/
│       ├── auth/
│       │   ├── login.php           # Formulario de login
│       │   └── register.php        # Formulario de registro
│       ├── errors/
│       │   └── 403.php             # Vista de acceso prohibido
│       ├── footer.php              # Pie común (cierra <main>, <body>, <html>)
│       ├── layout.php              # Cabecera común (nav, flash, abre <main>)
│       └── posts/
│           ├── create.php          # Formulario de creación
│           ├── edit.php            # Formulario de edición
│           ├── index.php           # Listado + búsqueda
│           └── show.php            # Detalle + comentarios
├── TESTING.md                  # Plan de pruebas (48 casos)
├── test.sh                     # Script de pruebas automáticas con curl
└── vendor/                     # Dependencias de Composer (no se versiona)