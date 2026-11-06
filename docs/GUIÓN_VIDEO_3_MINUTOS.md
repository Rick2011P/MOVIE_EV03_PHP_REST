# Guion de video demostrativo (máximo 3:00)
**0:00–0:20** Portada: «Evidencia GA7-220501096-AA5-EV03, proyecto MOVIE, diseño y desarrollo de servicios web». Presentar propósito de API REST.
**0:20–0:45** En VS Code mostrar `public/index.php`, `src/bootstrap.php`, `sql/movie.sql`, `postman/` y `.gitignore`. Explicar PHP, PDO, MySQL, JWT y Git.
**0:45–1:15** En Postman mostrar registro `POST /api/auth/register`, login `POST /api/auth/login` y token Bearer. Demostrar respuesta exitosa y contraseña equivocada (401).
**1:15–2:20** Mostrar CRUD: `GET /api/movies`, `POST /api/movies` (201), `GET /api/movies/{id}` (200), `PUT` (200), `DELETE` (200); comprobar listado. Antes de grabar crear cuenta admin de práctica por SQL.
**2:20–2:40** Mostrar validación 422 (campos vacíos), 401 (sin token) y 403 (USUARIO intentando crear película). Mostrar favoritos si alcanza tiempo.
**2:40–2:55** Mostrar Git: `git log --oneline`, URL del repositorio y README de instalación.
**2:55–3:00** Cierre: API REST modular con métodos reutilizables, validaciones y acceso restringido.

No inventar resultados: grabar respuestas verdaderas de Postman en el equipo. Alojar el video con enlace público accesible y anexar URL real al informe.
