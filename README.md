# Vitrina

Una app Laravel que sirve landings públicas editables para tiendas.

## Requisitos

- PHP 8.3 o superior, con las extensiones `pdo_sqlite`, `gd` y `mbstring`
- Composer 2
- Node 24 LTS (Node 25 no es compatible con Vite+). Revisá con `node -v`.

## Instalación

```bash
git clone <repo> vitrina && cd vitrina
composer setup
php artisan storage:link
```

`composer setup` instala dependencias, crea `.env` desde `.env.example`, genera la llave, crea la base SQLite, corre las migraciones y compila el frontend.

## Desarrollo

```bash
composer dev
```

Levanta el servidor en http://localhost:8000, la cola, los logs y Vite.

## Verificación

Correr en este orden, uno por uno (`npm run build` borra `public/build` y rompe los tests si corren a la vez):

```bash
php artisan test
vendor/bin/pint --test
vendor/bin/phpstan analyse --memory-limit=1G
npm run test:unit
npm run build
composer audit
npm audit --audit-level=high
```
