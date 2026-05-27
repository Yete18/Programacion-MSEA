# Arquitectura MSEA

MSEA es una aplicacion Laravel con PostgreSQL orientada a tres roles: estudiante, profesor y director. El flujo actual mantiene autenticacion manual por sesion para preservar compatibilidad con el frontend existente.

## Estructura principal

- `app/Http/Controllers`: controladores del login, recuperacion de contrasena y dashboards.
- `app/Http/Requests`: validacion de entradas con Form Requests.
- `app/Http/Middleware`: proteccion de sesion y roles.
- `app/Models`: modelos Eloquent alineados con las tablas reales de PostgreSQL.
- `app/Services`: logica de negocio pesada extraida de controladores.
- `database/migrations`: esquema reproducible MSEA.
- `tests/Feature`: pruebas de flujos principales.

## Autenticacion actual

El proyecto no usa `Auth::attempt()` todavia. El login valida correo, contrasena y rol desde `AuthService`, y guarda una sesion manual:

```php
session([
    'usuario_id',
    'nombre',
    'apellido_paterno',
    'correo',
    'rol',
]);
```

Los roles reales son `estudiante`, `profesor` y `director`. El alias `admin` se normaliza a `director`.

## Middleware

- `auth.session`: exige `usuario_id` y `rol` en sesion.
- `role`: normaliza roles y restringe acceso por grupo.

Las rutas protegidas viven agrupadas en `routes/web.php`. No se cambian URLs para mantener compatibilidad con vistas y formularios existentes.

## Services

- `AuthService`: login, validacion legacy/hash de contrasena y registro publico de estudiantes.
- `PasswordResetService`: codigos de recuperacion y cambio de contrasena.
- `StudentDashboardService`: payload del dashboard estudiante y perfil.
- `AdminDashboardService`: payload y acciones del director.
- `ProfilePhotoService`: almacenamiento hibrido de fotos de perfil.

## Modelos

`Usuario` representa la tabla real `usuarios`. `User` existe como bridge temporal porque Laravel suele apuntar a `App\Models\User`.

Todos los modelos declaran `$fillable`. `RankingEstudiante` representa una vista y no debe usarse para escrituras de negocio.

## PostgreSQL y migraciones

La migracion `2026_05_26_000001_create_msea_schema.php` reproduce el esquema MSEA con nombres reales de PK/FK. Tiene un guard defensivo: si ya existen `usuarios` o `roles`, no intenta recrear tablas en una base existente.

Para una instalacion nueva:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

## Testing

La suite usa `RefreshDatabase` y migraciones reales. Por defecto `phpunit.xml` corre con SQLite in-memory para velocidad y compatibilidad local. Para PostgreSQL, usar una base separada como `msea_testing` tomando `.env.testing.example` como referencia.

Comando:

```bash
php artisan test
```

Nunca apuntar tests a la base real de desarrollo.

## Avatares

El campo `usuarios.foto` es hibrido:

- si contiene `data:image...`, se trata como foto legacy base64 y se renderiza igual;
- si contiene `avatars/archivo.jpg`, se sirve desde el disco publico con `/storage/...`;
- las nuevas fotos base64 se guardan en `storage/app/public/avatars` y en DB queda solo la ruta.

Debe existir el enlace:

```bash
php artisan storage:link
```
