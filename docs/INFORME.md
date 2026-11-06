# SENA — EVIDENCIA GA7-220501096-AA5-EV03
## Diseño y desarrollo de servicios web — Proyecto MOVIE
**Programa:** Análisis y desarrollo de software  
**Proyecto formativo:** Construcción de software integrador de tecnologías orientadas a servicios  
**Fase:** Ejecución  
**Resultado de aprendizaje:** 220501096-04. Codificar el software de acuerdo con el diseño establecido  
**Aprendiz:** [Nombre del aprendiz]  
**Fecha:** [Fecha de entrega]

## Introducción
La evidencia documenta el diseño e implementación de una API REST para MOVIE, sistema orientado a gestionar un catálogo de películas, el acceso de usuarios y sus películas favoritas. La solución expone servicios HTTP con cuerpos JSON y persistencia en MySQL. Se presenta una implementación PHP de práctica alineada con la clase; el proyecto MOVIE anterior basado en Node.js permanece independiente.

## Objetivo general
Desarrollar una API REST del proyecto MOVIE que proporcione métodos reutilizables, autenticación de usuarios, CRUD de películas, validaciones y almacenamiento persistente.

## Objetivos específicos
1. Implementar registro, inicio de sesión y autorización de usuarios.
2. Exponer endpoints REST para crear, consultar, modificar y eliminar películas.
3. Incorporar validaciones, códigos de estado HTTP y manejo seguro de contraseñas.
4. Documentar y preparar pruebas en Postman y control de versiones con Git.

## Requerimientos y solución
El usuario puede registrarse e iniciar sesión; consultar películas y gestionar favoritos propios. El administrador puede crear, modificar y eliminar películas y consultar cuentas. Se utilizan `POST`, `GET`, `PUT`, `PATCH` y `DELETE`. Los clientes envían y reciben JSON. Se utilizan PDO y consultas preparadas para acceder a MySQL.

## Arquitectura
Cliente Postman / aplicación MOVIE → HTTP JSON → enrutador `public/index.php` → utilidades `src/bootstrap.php` → base `movie_ev03` (tablas `users`, `movies`, `favorites`). Autenticación mediante tokens Bearer JWT HS256, con vencimiento por defecto de 2 horas. Contraseñas con hash seguro; operaciones de escritura de películas reservadas a ADMIN.

## Endpoints
| Método | Ruta | Función | Acceso |
|---|---|---|---|
| POST | /api/auth/register | Registrar usuario | Público |
| POST | /api/auth/login | Autenticación y token | Público |
| GET | /api/users/me | Consultar perfil | Autenticado |
| GET | /api/users | Listar usuarios | ADMIN |
| PATCH | /api/users/{id}/status | Activar/desactivar | ADMIN |
| GET | /api/movies | Consultar catálogo | Público |
| GET | /api/movies/{id} | Consultar película | Público |
| POST | /api/movies | Crear película | ADMIN |
| PUT | /api/movies/{id} | Actualizar película | ADMIN |
| DELETE | /api/movies/{id} | Eliminar película | ADMIN |
| GET | /api/favorites | Listar favoritos | Autenticado |
| POST | /api/favorites | Añadir favorito | Autenticado |
| DELETE | /api/favorites/{id} | Eliminar favorito | Autenticado |

## Validaciones y respuestas
Los campos obligatorios de películas se validan por longitud, año y tipo. El correo se valida por formato, la contraseña exige mínimo ocho caracteres, el correo repetido se rechaza y los tokens caducados o ausentes no permiten acceder a rutas protegidas. Códigos esperados: 200 (consulta/modificación), 201 (creación), 401 (sin autenticación), 403 (sin permiso), 404 (no existe), 409 (duplicado), 422 (validación), 500 (error interno sin divulgar detalles de la BD).

## Pruebas funcionales
Se suministra colección importable en Postman y casos de prueba detallados en README. **Estado:** implementación y casos preparados; ejecución real en el MySQL del aprendiz pendiente. Las capturas deben incorporarse en `docs/REGISTRO_EVIDENCIAS.md` y carpeta `evidencias/`. No se declaran pruebas no ejecutadas.

## Versionamiento
Git registra y documenta cambios. Se aporta `.gitignore` para excluir secretos. Para acreditar el requisito, el aprendiz debe inicializar el repositorio, realizar commits, publicarlo y anexar URL y captura del historial.

## Conclusiones
La solución propone servicios reutilizables que separan el acceso a películas de la interfaz cliente. La API incorpora seguridad básica, autenticación, roles y validaciones acordes con una evidencia de formación. Queda pendiente la verificación funcional en el entorno local y la publicación de las evidencias reales y del video.

## Lista de chequeo institucional
1. Servicios según requerimientos: implementado en código; prueba pendiente.
2. API REST según necesidades: endpoints desarrollados; prueba pendiente.
3. Validaciones de verificación: controles implementados; resultados de Postman pendientes.
4. Herramientas de versionamiento: instrucciones y `.gitignore` incluidos; repositorio remoto real pendiente.

## Entregables
Código fuente + SQL + colección Postman + README + informe + guion de video + registro de evidencias. Agregar enlaces reales de video y Git antes de entregar.
