# Plan de pruebas — Tablón de anuncios

**IMPORTANTE**
PARA HACER CASOS DE PRUEBA AUTOMÁTICAMENTE PRIMERO HACER ESTO, PARA DARLE PERMISOS DE EJECUCIÓN
```bash
    chmod +x test.sh 
```

LUEGO EJECUTAR: 
```bash
./test.sh
```

Este documento recoge los casos de prueba del tablón de anuncios (práctica final 2.8 - PHP).
Para cada caso se indica qué debe hacer la aplicación y qué respuesta se espera.
La columna **Resultado real** y **¿Pasa?** se rellenan tras ejecutar el caso manualmente.

Leyenda: ✅ Pasa · ❌ Falla · ⚠️ Pasa con matices

**Nota**: Los casos marcados con **[auto]** se han ejecutado con el script `test.sh`
(curl contra la app) y han pasado todos. Los marcados con **[manual]** se han
ejecutado en el navegador.

---

## 1. Autenticación (registro, login, logout)

| ID | Caso de prueba | Qué debe pasar | Resultado real | ¿Pasa? |
|----|----------------|----------------|----------------|--------|
| A1 | Registro con datos válidos (email nuevo, username nuevo, password) | Redirige a `/login` con mensaje "Registro correcto. Ya puedes iniciar sesión." El usuario aparece en la BD | Redirige a `/login` con el mensaje. El usuario aparece en `users` | ✅ |
| A2 | Registro con email ya existente | Redirige a `/register` con mensaje "Ya existe un usuario con ese email." | Redirige a `/register` con el mensaje | ✅ |
| A3 | Registro con email con formato inválido (`noesemail`) | Redirige a `/register` con mensaje "El email no es válido." | Redirige a `/register` con el mensaje | ✅ |
| A4 | Registro con campos vacíos | Redirige a `/register` con mensaje "Todos los campos son obligatorios." | Redirige a `/register` con el mensaje | ✅ |
| A5 | Login con credenciales correctas | Redirige a `/` con sesión iniciada; la cabecera muestra el email y "Cerrar sesión" | 302 a `/`. Cabecera con email y "Cerrar sesión" | ✅ |
| A6 | Login con contraseña incorrecta | Redirige a `/login` con mensaje "Credenciales incorrectas." | 302 a `/login` con el mensaje | ✅ |
| A7 | Login con email que no existe | Redirige a `/login` con mensaje "Credenciales incorrectas." | 302 a `/login` con el mensaje | ✅ |
| A8 | Login con campos vacíos | Redirige a `/login` con mensaje "Email y contraseña son obligatorios." | 302 a `/login` con el mensaje | ✅ |
| A9 | Logout | Destruye la sesión y redirige a `/`; la cabecera muestra "Iniciar sesión" / "Registrarse" | Redirige a `/` sin sesión; cabecera con "Iniciar sesión" / "Registrarse" | ✅ |
| A10 | Acceso a `/posts/create` sin sesión | Redirige a `/login` con mensaje "Debes iniciar sesión para publicar." | 302 a `/login` con el mensaje | ✅ |

---

## 2. Publicaciones — CRUD

| ID | Caso de prueba | Qué debe pasar | Resultado real | ¿Pasa? |
|----|----------------|----------------|----------------|--------|
| P1 | Crear post con datos válidos (logueado) | Redirige a `/posts` con mensaje "Publicación creada correctamente." El post aparece en el listado | Redirige a `/posts` con el mensaje; el post aparece en el listado | ✅ |
| P2 | Crear post con título vacío | Redirige a `/posts/create` con mensaje "Todos los campos son obligatorios." | Redirige a `/posts/create` con el mensaje | ✅ |
| P3 | Crear post con cuerpo vacío | Redirige a `/posts/create` con mensaje "Todos los campos son obligatorios." | Redirige a `/posts/create` con el mensaje | ✅ |
| P4 | Crear post con categoría vacía | Redirige a `/posts/create` con mensaje "Todos los campos son obligatorios." | Redirige a `/posts/create` con el mensaje | ✅ |
| P5 | Ver listado `/posts` | Muestra todos los posts con título, categoría, autor y fecha | Listado con título, categoría, autor y fecha | ✅ |
| P6 | Ver detalle `/posts/show/1` | Muestra título, categoría, autor, fecha y cuerpo del post 1 | Detalle completo del post 1 | ✅ |
| P7 | Ver detalle de un post inexistente `/posts/show/999` | Devuelve 404 con "Publicación no encontrada." | 404 con el mensaje | ✅ |
| P8 | Editar propio post | Redirige a `/posts/show/N` con mensaje "Publicación actualizada." Los cambios se ven | Redirige a `/posts/show/N` con el mensaje; cambios visibles | ✅ |
| P9 | Editar post con campos vacíos | Redirige a `/posts/edit/N` con mensaje "Todos los campos son obligatorios." | Redirige a `/posts/edit/N` con el mensaje | ✅ |
| P10 | Borrar propio post | Redirige a `/posts` con mensaje "Publicación eliminada." El post ya no aparece | Redirige a `/posts` con el mensaje; el post desaparece | ✅ |

---

## 3. Permisos por propiedad

| ID | Caso de prueba | Qué debe pasar | Resultado real | ¿Pasa? |
|----|----------------|----------------|----------------|--------|
| R1 | Usuario A intenta editar un post de usuario B (GET `/posts/edit/2`) | Devuelve 403 con la vista `errors/403.php` ("No tienes permiso…") | 403 con la vista `errors/403.php` | ✅ |
| R2 | Usuario A intenta actualizar un post de usuario B (POST `/posts/update/2`) | Devuelve 403 | 403 | ✅ |
| R3 | Usuario A intenta borrar un post de usuario B (POST `/posts/delete/2`) | Devuelve 403 | 403 | ✅ |
| R4 | Usuario A edita su propio post | Funciona (redirige a `/posts/show/N` con éxito) | Redirige a `/posts/show/N` con éxito | ✅ |
| R5 | Usuario A borra su propio post | Funciona (redirige a `/posts` con éxito) | Redirige a `/posts` con éxito | ✅ |
| R6 | Sin sesión, POST `/posts/delete/1` | Redirige a `/login` con "Debes iniciar sesión." | 302 a `/login` con el mensaje | ✅ |

---

## 4. Comentarios

| ID | Caso de prueba | Qué debe pasar | Resultado real | ¿Pasa? |
|----|----------------|----------------|----------------|--------|
| C1 | Ver detalle de un post con comentarios | Muestra los comentarios con autor, fecha y cuerpo, ordenados por fecha ascendente | Comentarios con autor, fecha y cuerpo, en orden ascendente | ✅ |
| C2 | Ver detalle de un post sin comentarios | Muestra "Todavía no hay comentarios." | Muestra el mensaje | ✅ |
| C3 | Comentar un post (logueado) | Redirige a `/posts/show/N` con mensaje "Comentario añadido." El comentario aparece | Redirige a `/posts/show/N` con el mensaje; el comentario aparece | ✅ |
| C4 | Comentar con cuerpo vacío | Redirige a `/posts/show/N` con mensaje "El comentario no puede estar vacío." | Redirige a `/posts/show/N` con el mensaje | ✅ |
| C5 | Sin sesión, ver detalle | Al final del post aparece "Inicia sesión para comentar." (sin formulario) | Muestra el enlace a `/login` (sin formulario) | ✅ |
| C6 | Sin sesión, POST `/comments/store` | Redirige a `/login` con "Debes iniciar sesión para comentar." | 302 a `/login` con el mensaje | ✅ |
| C7 | Comentar un post inexistente (POST con `post_id=999`) | Devuelve 404 "Publicación no encontrada." | 404 con el mensaje | ✅ |
| C8 | Intentar manipular `user_id` en el formulario de comentario | El comentario se guarda con el `user_id` de la sesión, no con el manipulado | El `user_id` guardado es el de la sesión, no el manipulado | ✅ |

---

## 5. Búsqueda

| ID | Caso de prueba | Qué debe pasar | Resultado real | ¿Pasa? |
|----|----------------|----------------|----------------|--------|
| S1 | `/posts?q=php` | Muestra solo los posts cuyo título o categoría contengan "php" | Muestra solo el post "Busco grupo para proyecto de PHP" | ✅ |
| S2 | `/posts?q=libro` | Muestra solo los posts que contengan "libro" | Muestra solo el post "Vendo libro de Desarrollo en Entorno Servidor" | ✅ |
| S3 | `/posts?q=xyz` (sin resultados) | Muestra "No hay publicaciones que mostrar." | Muestra el mensaje | ✅ |
| S4 | `/posts` (sin búsqueda) | Muestra todos los posts | Muestra todos los posts | ✅ |
| S5 | `/posts?q=` (búsqueda vacía) | Se comporta como sin búsqueda (muestra todos) | Muestra todos los posts | ✅ |
| S6 | Búsqueda con mayúsculas/minúsculas (`PHP` vs `php`) | Devuelve los mismos resultados (LIKE es case-insensitive con la collation actual) | Mismos resultados | ✅ |

---

## 6. CSRF

| ID | Caso de prueba | Qué debe pasar | Resultado real | ¿Pasa? |
|----|----------------|----------------|----------------|--------|
| X1 | POST `/login` sin token CSRF (con `curl`) | Devuelve 403 con "Token CSRF inválido…" | 403 con el mensaje | ✅ |
| X2 | POST `/login` con token manipulado | Devuelve 403 | 403 | ✅ |
| X3 | POST `/register` sin token | Devuelve 403 | 403 | ✅ |
| X4 | POST `/posts/store` sin token | Devuelve 403 | 403 | ✅ |
| X5 | POST `/comments/store` sin token | Devuelve 403 | 403 | ✅ |
| X6 | Formulario normal (con token) | Funciona correctamente | Funciona correctamente (302 a `/` en login) | ✅ |

---

## 7. Rutas limpias

| ID | Caso de prueba | Qué debe pasar | Resultado real | ¿Pasa? |
|----|----------------|----------------|----------------|--------|
| L1 | `/posts/show/1` | Muestra el detalle del post 1 (ruta limpia) | 200 con el detalle | ✅ |
| L2 | `/posts/edit/1` | Muestra el formulario de edición del post 1 | 200 con el formulario | ✅ |
| L3 | POST `/posts/delete/1` | Borra el post 1 y redirige a `/posts` | 302 a `/posts`; el post desaparece | ✅ |
| L4 | `/posts/show?id=1` (forma antigua) | Sigue funcionando (compatibilidad) | 200 con el detalle | ✅ |
| L5 | `/posts/show/999` | 404 "Publicación no encontrada." | 404 con el mensaje | ✅ |

---

## 8. Seguridad (XSS)

| ID | Caso de prueba | Qué debe pasar | Resultado real | ¿Pasa? |
|----|----------------|----------------|----------------|--------|
| E1 | Crear post con título `<script>alert('xss')</script>` | El título se muestra como texto literal, no se ejecuta el script | El título se muestra literal, sin ejecutar el script | ✅ |
| E2 | Comentar con `<script>alert('xss')</script>` | El comentario se muestra como texto literal | El comentario se muestra literal | ✅ |
| E3 | Crear post con `<b>negrita</b>` en el cuerpo | Se muestra literal (no se interpreta como HTML) | Se muestra literal | ✅ |

---