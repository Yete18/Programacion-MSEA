# Deuda tecnica MSEA

Este documento lista pendientes conocidos sin bloquear el estado actual del sistema.

## Autenticacion

- Migrar gradualmente de sesion manual a guard/auth nativo de Laravel.
- Evaluar `Auth::attempt()` cuando el modelo `Usuario` sea el proveedor principal.
- Registrar `ultimo_ingreso_at` o una tabla de auditoria de accesos.

## Autorizacion

- Introducir policies/gates cuando crezcan las acciones por profesor y estudiante.
- Definir reglas finas para recursos propios: tareas, entregas, ejercicios y feedback.
- Mantener validaciones defensivas en servicios mientras no existan policies.

## Arquitectura

- No introducir repositories hasta que haya repeticion real de consultas entre modulos.
- Separar un `ProfesorDashboardService` cuando el panel de profesor empiece a crear tareas, partituras y feedback.
- Revisar si los services deben devolver DTOs/arrays tipados cuando el dominio se estabilice.

## Base de datos

- Evaluar una migracion para `usuarios.trayectoria` si el perfil del director debe guardar biografia real.
- Crear tabla `entregas_tareas` para distinguir tarea asignada de tarea entregada.
- Crear tabla de archivos adjuntos para partituras, tareas y entregas.
- Mantener SQL PostgreSQL complejo donde aporte rendimiento: `string_agg`, `filter`, views y reportes.

## Testing

- Ejecutar periodicamente la suite contra PostgreSQL `msea_testing`, no solo SQLite.
- Agregar pruebas de profesor cuando existan endpoints para crear tareas/partituras.
- Agregar pruebas de limites de archivo si se habilita upload multipart real.

## Frontend

- Reducir dependencia de datos mock en dashboards a medida que backend exponga mas datos reales.
- Revisar textos mojibake/encoding en JavaScript historico cuando se haga una pasada visual.
- Mantener compatibilidad de foto base64 hasta migrar avatares antiguos por lote.

## Produccion

- Configurar backups PostgreSQL.
- Configurar correo transaccional real para recuperacion de contrasena.
- Revisar rate limiting de login y password reset.
- Activar logs y monitoreo basicos de errores.
