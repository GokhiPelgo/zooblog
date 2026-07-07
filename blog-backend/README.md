# ZOOBLOG — Backend

API REST para el formulario de contacto de ZOOBLOG. Construida con Laravel 13 y SQLite.

---

## ⚠️ Antes de empezar: ZooBlog son DOS proyectos

ZooBlog está formado por dos carpetas **separadas** que trabajan juntas:

- **`blog-frontend`** (Astro) — el sitio que ve el visitante: el blog, los artículos y el formulario.
- **`blog-backend`** (Laravel) — **esta carpeta**: recibe los mensajes del formulario de contacto y el webhook de Prismic.

No son "dependencias" una de otra: son **dos aplicaciones independientes que se comunican por HTTP**. Pueden correr en servidores distintos.

```
   Visitante
      │
      ▼
┌──────────────────────┐   POST /api/contact   ┌──────────────────────┐
│  Frontend (Astro)    │ ────────────────────▶ │  Backend (Laravel)   │
│  blog-frontend       │                       │  blog-backend  ◀ aquí│
│  · Muestra el blog   │                       │  · Guarda contacto   │
│  · Formulario        │                       │  · Webhook Prismic   │
└──────────────────────┘                       └──────────────────────┘
```

**Qué necesita estar corriendo:**

- Para **leer el blog**: basta el frontend.
- Para el **formulario de contacto**: necesitas el frontend **y** este backend corriendo a la vez.

---

## Requisitos

Antes de instalar, asegúrate de tener:

- **PHP** v8.3 o superior — https://www.php.net
- **Composer** v2 o superior — https://getcomposer.org
- Extensión **php-sqlite3** habilitada

Verifica tu versión con:

```bash
php -v
composer -V
```

---

## Instalación

### 1. Instala las dependencias

```bash
cd blog-backend
composer install
```

### 2. Configura las variables de entorno

Copia el archivo de ejemplo y edítalo:

```bash
cp .env.example .env
```

Genera la clave de la aplicación:

```bash
php artisan key:generate
```

Variables importantes en el `.env`:

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:4321

DB_CONNECTION=sqlite

MAIL_MAILER=log
MAIL_ADMIN_TO=tu@correo.com

PRISMIC_WEBHOOK_SECRET=zooblog_secret_2026
DEPLOY_HOOK_URL=
```

### 3. Crea la base de datos

```bash
touch database/database.sqlite
```

### 4. Ejecuta las migraciones

```bash
php artisan migrate
```

Esto crea las tablas necesarias en SQLite.

### 5. Corre el servidor

```bash
php artisan serve
```

El backend estará disponible en http://localhost:8000

---

## Rutas disponibles

| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/contact` | Recibe el formulario de contacto |
| POST | `/api/webhook/prismic` | Webhook de Prismic al publicar contenido |

---

## Cómo probar el formulario de contacto

Con el servidor corriendo, puedes probar el endpoint con curl:

```bash
curl -X POST http://localhost:8000/api/contact \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"Juan","email":"juan@gmail.com","message":"Hola, esto es una prueba."}'
```

Respuesta esperada:

```json
{
  "message": "Mensaje recibido. ¡Gracias por contactarnos!"
}
```

El mensaje quedará guardado en la tabla `contact_messages` de la base de datos.

---

## Cómo ver los correos en desarrollo

En desarrollo los correos no se envían — se guardan en el log. Para verlos:

```bash
tail -f storage/logs/laravel.log
```

Busca la sección `Message-ID` para ver el contenido del correo.

---

## Webhook de Prismic

Cuando publicas contenido en Prismic, puedes configurarlo para que avise al backend automáticamente.

En Prismic: **Settings → Webhooks → Add a webhook**

- URL: `http://TU-SERVIDOR/api/webhook/prismic`
- Secret: el valor de `PRISMIC_WEBHOOK_SECRET` en tu `.env`

En producción, agrega la URL de deploy de Vercel o Netlify en `DEPLOY_HOOK_URL` para que el sitio se reconstruya automáticamente al publicar.

---

## Estructura del proyecto

```
app/
  Http/
    Controllers/
      ContactController.php   → Recibe y guarda el formulario
      WebhookController.php   → Recibe eventos de Prismic
    Requests/
      ContactRequest.php      → Valida y limpia los datos del formulario
  Models/
    ContactMessage.php        → Modelo del mensaje de contacto
    Article.php               → Modelo de artículo (datos de ejemplo / seeder)
    User.php                  → Usuario del sistema
database/
  migrations/                 → Migraciones de la base de datos
  database.sqlite             → Base de datos SQLite
routes/
  api.php                     → Rutas de la API
```
