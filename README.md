# Barberia

Aplicacion Laravel 12 con Blade y SQLite para gestionar reservas de una barberia.

## Puesta en marcha

Requiere PHP 8.2 o superior.

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

La base SQLite se crea en `database/database.sqlite`. En Windows con XAMPP puede usarse `C:\xampp\php\php.exe` y `C:\xampp\php\composer.phar`.

## Accesos demo

- Administrador: `admin@barberia.test` / `password`
- Barbero: `barbero@barberia.test` / `password`

## Funcionalidad actual

- Reserva publica sin cuenta.
- Seleccion de servicio, barbero, fecha y slots disponibles.
- Respeto de jornada, pausa y turnos ocupados.
- Validacion final contra solapamientos.
- Confirmacion con enlace personalizado de WhatsApp.
- Panel autenticado con agenda diaria y cambio de estado.

Las cuentas de clientes no se almacenan. Los datos del cliente quedan asociados al turno.
