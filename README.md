# ZOOBLOG

Blog educativo bilingüe (español / inglés) sobre animales. Este repositorio contiene
**las dos partes del proyecto** en un monorepo:

```
zooblog/
├── blog-frontend/   → Sitio web (Astro + Tailwind)
└── blog-backend/    → API + panel (Laravel + Filament + SQLite en local)
```

No son "dependencias" una de otra: son **dos aplicaciones independientes que se
comunican por HTTP**. Todo el contenido (blog, tutoriales, portada) se administra
desde el panel de Laravel/Filament. En local necesitas levantar **las dos**.

```
   Visitante
      │
      ▼
┌──────────────────────┐   GET  /api/posts     ┌──────────────────────┐
│  Frontend (Astro)    │   GET  /api/tutorials │  Backend (Laravel)   │
│  blog-frontend       │ ────────────────────▶ │  blog-backend        │
│  · Blog + Tutoriales │   POST /api/contact   │  · Panel /admin      │
│  · Portada + Form    │ ◀──── JSON ─────────  │  · API + base datos  │
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

---

## Cómo correr en local

Necesitas **3 terminales**: el **backend**, el **worker de la cola** (compila el sitio
al pulsar Publicar) y el **frontend**. Todos los comandos se corren **desde la raíz del
monorepo** (`zooblog/`).

### Preparación (solo la primera vez)

**Backend:**
```bash
cd blog-backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite      # base de datos local (SQLite)
php artisan migrate                 # crea las tablas
php artisan db:seed                 # datos de ejemplo + admin + portada (Home)
php artisan storage:link            # para servir las imágenes subidas en el panel
```

**Frontend:**
```bash
cd blog-frontend
npm install
cp .env.example .env                # ajusta las variables (abajo)
```

### Para trabajar (cada vez) — 3 terminales

**Terminal 1 — Backend (panel + API):**
```bash
cd blog-backend
php artisan serve --no-reload       # http://localhost:8000
```

**Terminal 2 — Worker de la cola (compila al Publicar):**
```bash
cd blog-backend
php artisan queue:work              # déjala abierta mientras trabajas
```

**Terminal 3 — Frontend (el sitio):**
```bash
cd blog-frontend
npm run dev                         # http://localhost:4321 (desarrollo)
# npm run preview                   # ver el sitio ya compilado (dist/)
```

> Panel de administración: **http://localhost:8000/admin**
> Usuario por defecto (tras el seed): `chelo@zooblog.com` / `password123`
> Cambia tu contraseña dentro del panel: **menú de usuario (arriba a la derecha) → Perfil**.

---

## Variables de entorno mínimas

**`blog-backend/.env`**
```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:4321
DB_CONNECTION=sqlite
MAIL_MAILER=log
MAIL_ADMIN_TO=tu@correo.com
UPLOADS_DISK=public

# Botón "Publicar" en local (compila Astro en tu máquina, en segundo plano)
PUBLISH_MODE=local
QUEUE_CONNECTION=database
PHP_CLI_SERVER_WORKERS=4
# Ruta a tu instalación de Node/npm (ajústala; necesaria si usas nvm)
FRONTEND_BUILD_COMMAND="PATH=/ruta/a/node/bin:/usr/bin:/bin npm run build"
```

**`blog-frontend/.env`**
```env
PUBLIC_API_URL=http://localhost:8000
PUBLIC_SITE_URL=http://localhost:4321
```

---

## Notas para desarrollo local

- **Publicar (build local):** el botón **🚀 Publicar** del panel compila el sitio (Astro)
  en tu máquina, **en segundo plano**. Requiere la **Terminal 2** (`queue:work`) corriendo;
  si no, el botón se queda en "Compilando…" (nadie procesa la cola). Muestra el avance
  solo (⏳ Compilando… → ✅ / ❌).
- **Cambiar contraseña:** dentro del panel, **menú de usuario → Perfil**.
- **Portada (Home) editable:** en `/admin` → *Inicio (portada)*, editas textos, botones e
  imágenes en pestañas **Español / English**. Los cambios se ven **tras Publicar**.
- **Correos:** en local `MAIL_MAILER=log` — los correos no se envían, se escriben en
  `blog-backend/storage/logs/laravel.log`. Míralos con `tail -f`.
- **Imágenes:** en local se guardan en el disco público (`UPLOADS_DISK=public`); corre
  `php artisan storage:link` una vez para servirlas.
- **Blog:** se administra en el panel `/admin` → **Blog** (artículos, categorías,
  etiquetas, SEO y estatus borrador/publicado). Los cambios se ven **tras Publicar**.
- **Tutoriales:** se crean en el panel `/admin` y aparecen en vivo en el sitio.
- **Registro de publicaciones:** en `/admin` ves quién publicó, cuándo y el resultado.
- **Pruebas:** en `blog-backend`, corre `php artisan test`.

---

## Documentación detallada

- **Frontend:** [`blog-frontend/README.md`](./blog-frontend/README.md) — comandos y publicación.
- **Backend:** [`blog-backend/README.md`](./blog-backend/README.md) — endpoints, pruebas.
- **Documentación técnica completa:** `blog-backend/DOCUMENTACION-COMPLETA.md`.
