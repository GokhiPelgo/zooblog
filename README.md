# ZOOBLOG

Blog educativo bilingüe (español / inglés) sobre animales. Este repositorio contiene
**las dos partes del proyecto** en un monorepo:

```
zooblog/
├── blog-frontend/   → Sitio web (Astro + Tailwind + Prismic)
└── blog-backend/    → API + panel (Laravel + Filament + SQLite en local)
```

No son "dependencias" una de otra: son **dos aplicaciones independientes que se
comunican por HTTP**. En local necesitas levantar **las dos**.

```
   Visitante
      │
      ▼
┌──────────────────────┐   POST /api/contact   ┌──────────────────────┐
│  Frontend (Astro)    │ ────────────────────▶ │  Backend (Laravel)   │
│  blog-frontend       │   GET  /api/tutorials │  blog-backend        │
│  · Blog + Tutoriales │ ◀──────────────────── │  · Panel /admin      │
│  · Formulario        │        JSON           │  · API + base datos  │
└──────────────────────┘                       └──────────────────────┘
```

---

## Requisitos

| Herramienta | Versión | Para |
|-------------|---------|------|
| PHP | 8.3 o superior | Backend |
| Composer | 2 o superior | Backend |
| Extensión `php-sqlite3` | habilitada | Backend (BD local) |
| Node.js | 22.12 o superior | Frontend |
| npm | 10 o superior | Frontend |
| Cuenta en [Prismic](https://prismic.io) | plan gratuito | Contenido del blog |

---

## Cómo correr en local

Levanta primero el **backend** y luego el **frontend**, cada uno en su propia terminal.
Ambos comandos se corren **desde la raíz del monorepo** (la carpeta `zooblog/`).

### 1. Backend (`blog-backend`)

```bash
cd blog-backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite      # base de datos local (SQLite)
php artisan migrate                 # crea las tablas
php artisan db:seed                 # (opcional) datos de ejemplo + usuario admin
php artisan serve                   # http://localhost:8000
```

> Panel de administración: **http://localhost:8000/admin**
> Usuario por defecto (si corriste el seed): `chelo@zooblog.com` / `password123`

### 2. Frontend (`blog-frontend`)

En **otra** terminal, vuelve a la raíz del monorepo y entra al frontend:

```bash
cd blog-frontend
npm install
cp .env.example .env                # ajusta las variables (abajo)
npm run dev                         # http://localhost:4321
```

Abre **http://localhost:4321** en el navegador.

---

## Variables de entorno mínimas

**`blog-backend/.env`**
```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:4321
DB_CONNECTION=sqlite
MAIL_MAILER=log
MAIL_ADMIN_TO=tu@correo.com
PRISMIC_REPO=tu-repositorio
UPLOADS_DISK=public
```

**`blog-frontend/.env`**
```env
PUBLIC_API_URL=http://localhost:8000
PUBLIC_SITE_URL=http://localhost:4321
PUBLIC_PRISMIC_REPO=tu-repositorio
```

---

## Notas para desarrollo local

- **Correos:** en local `MAIL_MAILER=log` — los correos no se envían, se escriben en
  `blog-backend/storage/logs/laravel.log`. Míralos con `tail -f`.
- **Imágenes de tutoriales:** en local se guardan en el disco público
  (`UPLOADS_DISK=public`); corre `php artisan storage:link` una vez para servirlas.
- **Blog:** el contenido viene de Prismic. Configura tu repositorio de Prismic
  (ver `blog-frontend/README.md`).
- **Tutoriales:** se crean en el panel `/admin` y aparecen en vivo en el sitio.

---

## Documentación detallada

- **Frontend:** [`blog-frontend/README.md`](./blog-frontend/README.md) — Prismic, comandos, publicación.
- **Backend:** [`blog-backend/README.md`](./blog-backend/README.md) — endpoints, pruebas, webhook.
- **Documentación técnica completa:** `blog-backend/DOCUMENTACION-COMPLETA.md`.
