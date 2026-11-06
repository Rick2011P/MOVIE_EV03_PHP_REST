# MOVIE — Evidencia GA7-220501096-AA5-EV03
API REST en PHP 8.1+ y MySQL. Implementación independiente del proyecto anterior en Node.js; no reemplaza ni migra sus datos.

## Requisitos
- PHP 8.1 o superior con extensión `pdo_mysql` habilitada.
- MySQL 8+ (o MariaDB compatible), Postman y Git.
- Opcional: XAMPP para iniciar MySQL/PHP.

## Instalación
1. Crear la base importando `sql/movie.sql` en MySQL Workbench. **ATENCIÓN:** la base `movie_ev03` es exclusiva de esta práctica y no modifica las bases existentes `movie` o `movie_api`.
2. Copiar `.env.example` a `.env` y ajustar credenciales. Generar `JWT_SECRET` fuerte (mínimo 32 caracteres).
3. En la carpeta del proyecto ejecutar `php -S localhost:8000 -t public public/router.php`.
4. Probar `http://localhost:8000/api/health` y usar la colección `postman/MOVIE_EV03.postman_collection.json`.

## Crear administrador (solo desarrollo)
El registro público crea usuarios con rol `USUARIO`. Para las pruebas del módulo CRUD, después de registrar una cuenta propia de práctica, ejecutar desde MySQL:
```sql
UPDATE users SET role='ADMIN' WHERE email='admin.movie@example.com';
```
Usar el correo que registraste realmente, no una cuenta real ajena. Volver a iniciar sesión para comprobar. No existe endpoint público para escalar privilegios.

## Endpoints
| Método | Endpoint | Acceso | Resultado |
|---|---|---|---|
| GET | /api/health | Público | 200 estado |
| POST | /api/auth/register | Público | 201, 409, 422 |
| POST | /api/auth/login | Público | 200 token, 401 |
| GET | /api/users/me | Bearer | 200, 401 |
| GET | /api/users | ADMIN | 200, 401, 403 |
| PATCH | /api/users/{id}/status | ADMIN | 200, 404, 422 |
| GET | /api/movies | Público | 200 lista |
| GET | /api/movies/{id} | Público | 200, 404 |
| POST | /api/movies | ADMIN | 201, 401, 403, 422 |
| PUT | /api/movies/{id} | ADMIN | 200, 404, 422 |
| DELETE | /api/movies/{id} | ADMIN | 200, 404 |
| GET | /api/favorites | Bearer | 200 |
| POST | /api/favorites | Bearer | 201, 404, 422 |
| DELETE | /api/favorites/{id} | Bearer | 200, 404 |

## Cuerpos JSON de ejemplo
Registro: `{"name":"Administrador Movie","email":"admin.movie@example.com","password":"ClaveSegura123!"}`
Login: `{"email":"admin.movie@example.com","password":"ClaveSegura123!"}`
Crear/actualizar película: `{"title":"Interstellar","director":"Christopher Nolan","release_year":2014,"genre":"Ciencia ficción","synopsis":"Viaje espacial"}`
Favorito: `{"movie_id":1}`
Cambio de estado: `{"active":false}`

## Pruebas de aceptación
1. `/api/health` → 200.
2. Registro válido → 201; duplicado → 409; clave corta → 422.
3. Login válido → token; incorrecto → 401.
4. Sin token a `/api/users/me` → 401; con token → 200.
5. Usuario normal crea película → 403; ADMIN → 201.
6. GET películas → 200; PUT → 200; DELETE → 200; GET eliminado → 404.
7. Agregar/listar/eliminar favorito → 201/200/200.
8. Cuenta inactiva no puede autenticarse → 401.

## Versionamiento
```bash
git init
git add .
git commit -m "feat: estructura inicial API REST MOVIE"
```
No subir `.env` ni contraseñas. Publicar en un repositorio propio y guardar URL y capturas.

## Seguridad y límites
Código académico de desarrollo local. Usa contraseñas con `password_hash`, consultas preparadas, tokens JWT HS256 con vencimiento de 2 horas, roles ADMIN/USUARIO y verificación de cuentas activas. Antes de producción deben agregarse HTTPS, CORS restringido, limitación de intentos y auditoría, entre otros controles. No se ha verificado la ejecución contra el MySQL del usuario.
