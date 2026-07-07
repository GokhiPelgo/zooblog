# ZOOBLOG — Documentación técnica completa

Blog educativo bilingüe (español / inglés) sobre animales. Este documento explica
**a detalle** cómo está construido y desplegado el proyecto: dónde vive cada parte,
cómo funciona el blog, cómo funcionan los tutoriales, y cómo se conecta todo.

---

## 1. Visión general

ZooBlog está formado por **dos aplicaciones independientes** que se comunican por
internet (HTTP), más varios **servicios externos** que se encargan de tareas
específicas (contenido, imágenes, correo).

- **Frontend (Astro):** el sitio que ve el visitante. Es **estático**.
- **Backend (Laravel + Filament):** la "trastienda" — panel de administración,
  API y base de datos.
- **Servicios externos:** Prismic (CMS del blog), Cloudflare R2 (imágenes),
  Resend (correos).

El frontend y el backend **no son "dependencias" uno del otro**: son dos apps
separadas, en servidores distintos, que se hablan por HTTP.

---

## 2. Arquitectura — dónde vive cada cosa

| Pieza | Tecnología | Dónde vive | URL |
|-------|-----------|------------|-----|
| **Frontend** | Astro (estático) | **Vercel** | `zooblog-frontend.vercel.app` |
| **Backend** | Laravel 13 + Filament 5 | **Render** (Docker) | `zooblog-backend.onrender.com` |
| **Base de datos** | PostgreSQL | **Render** | (interna) |
| **CMS del blog** | Prismic | **Prismic.io** | repo `zooblog` |
| **Imágenes de tutoriales** | Cloudflare R2 (S3) | **Cloudflare** | `pub-…r2.dev` |
| **Correos** | Resend | **Resend.com** | — |

```
            ┌──────────────┐         ┌──────────────┐
   Visitante│  Prismic CMS │         │ Cloudflare R2│ (imágenes)
      │     │  (blog)      │         └──────▲───────┘
      ▼     └──────┬───────┘                │
┌─────────────┐   │ build           ┌───────┴───────────┐
│  FRONTEND   │◀──┘                 │     BACKEND       │
│  Astro      │  ── POST /api/* ──▶ │  Laravel+Filament │──▶ Postgres
│  (Vercel)   │  ◀─ JSON ─────────  │  (Render)         │──▶ Resend (correo)
└─────────────┘                     └───────────────────┘
   estático                          panel /admin + API
```

---

## 3. El frontend (Astro)

**Repositorio:** `github.com/GokhiPelgo/zooblog-frontend`
**Hosting:** Vercel (gratis). Despliega automáticamente con cada `git push` a `main`.

### Tecnologías
- **Astro 6** en modo `output: 'static'` → genera HTML plano, servido desde el CDN
  de Vercel (rapidísimo, sin servidor de por medio).
- **Tailwind CSS 4** (vía `@tailwindcss/postcss` + `postcss.config.mjs`).
- **GSAP** y **Lenis** para animaciones y scroll suave.
- **@prismicio/client** para traer el contenido del blog.
- **i18n nativo** de Astro: idiomas `es` (por defecto) y `en`, con rutas `/[lang]/...`.

### Estructura de páginas (`src/pages/`)
- `[lang]/index.astro` → Home.
- `[lang]/blog/index.astro` → listado del blog (lee de Prismic).
- `[lang]/blog/[slug].astro` → artículo del blog.
- `[lang]/tutoriales/index.astro` → listado de tutoriales (lee del backend, en vivo).
- `[lang]/tutoriales/ver.astro` → detalle del tutorial (lee del backend, en vivo).
- `[lang]/[slug].astro` → páginas estáticas (Sobre nosotros, Servicios, Contacto),
  definidas en `src/data/routes.ts`.
- `[lang]/categoria/[tag].astro` → posts del blog por etiqueta.
- `sitemap.xml.ts`, `robots.txt.ts` → SEO (se pre-generan en el build).

### Dos momentos en que se arma el contenido
- **En el build (`astro build`):** Astro va a **Prismic** y trae los posts del
  blog, y los "hornea" en HTML. Por eso el blog es estático y veloz.
- **En vivo (en el navegador):** los **tutoriales** y el **formulario de contacto**
  se piden al backend **cuando el visitante abre la página** (no en el build).

### Variables de entorno (en Vercel)
- `PUBLIC_API_URL` = URL del backend (`https://zooblog-backend.onrender.com`).
- `PUBLIC_SITE_URL` = URL del sitio (`https://zooblog-frontend.vercel.app`) → para
  canonical, sitemap, OG y hreflang.
- `PUBLIC_PRISMIC_REPO` = nombre del repositorio de Prismic.

---

## 4. El backend (Laravel + Filament)

**Repositorio:** `github.com/GokhiPelgo/zooblog-backend`
**Hosting:** Render (gratis), desplegado con **Docker**. Redespliega con cada `git push`.

### Qué hace
1. **Panel de administración** (Filament) en `/admin`: crear/editar tutoriales,
   ver mensajes de contacto, administrar usuarios.
2. **API REST** que el frontend consume.
3. **Base de datos** PostgreSQL donde vive todo (tutoriales, mensajes, usuarios).

### El Dockerfile (cómo arranca en Render)
La imagen instala PHP 8.4 + extensiones (`pdo_pgsql`, `gd`, `intl`, etc.) + Composer,
y al arrancar ejecuta:
```
php artisan migrate --force        # crea/actualiza las tablas
php artisan db:seed AdminUserSeeder # crea el usuario admin
php artisan storage:link
php artisan config:cache
php artisan serve                  # levanta el servidor en el puerto de Render
```

### Rutas de la API
| Método | Ruta | Para qué |
|--------|------|----------|
| `POST` | `/api/contact` | Recibe el formulario de contacto |
| `GET`  | `/api/tutorials?lang=es` | Lista los tutoriales publicados |
| `GET`  | `/api/tutorials/{slug}?lang=es` | Un tutorial por su slug |
| `GET`  | `/api/contact-messages` | Lista los mensajes (requiere token admin) |
| `POST` | `/api/webhook/prismic` | Webhook de Prismic |
| `POST` | `/publish` | Botón "Publicar" (dispara el deploy hook) |

### Variables de entorno clave (en Render)
`APP_KEY`, `APP_ENV=production`, `DB_*` (Postgres), `FRONTEND_URL` (CORS),
`MAIL_MAILER=resend` + `RESEND_API_KEY`, `DEPLOY_HOOK_URL`, `ADMIN_PASSWORD`,
`UPLOADS_DISK=s3` + `AWS_*` (R2).

---

## 5. Cómo funciona el BLOG

El blog usa **Prismic** como CMS (gestor de contenido en la nube).

**Flujo paso a paso:**
1. El redactor escribe un artículo en **Prismic** (con título, imagen, tags,
   contenido en Rich Text) y lo publica.
2. Para que aparezca en el sitio, hay que **reconstruir** (porque el sitio es
   estático). Eso se hace con el botón **"Publicar"** del panel.
3. Al reconstruir, **Astro va a Prismic**, trae todos los posts y genera el HTML.
4. El visitante ve el blog servido desde el CDN — sin tocar el backend.

**Importante:** el blog es **build-time**. Un post nuevo no aparece hasta el
siguiente build (de ahí el botón "Publicar").

---

## 6. Cómo funcionan los TUTORIALES

Los tutoriales **no usan Prismic** — se administran desde **Filament** (tu panel)
y viven en la **base de datos Postgres** del backend.

### Campos de un tutorial
| Campo | Para qué |
|-------|----------|
| `title` | Título |
| `slug` | URL del tutorial (solo minúsculas, números y guiones — validado) |
| `translation_key` | Enlaza las versiones es/en (mismo valor en ambas) |
| `lang` | Idioma (`es`/`en`) — desplegable |
| `excerpt` | Resumen corto para la tarjeta |
| `content` | Cuerpo del tutorial (editor enriquecido → HTML) |
| `cover_image` | Imagen de portada (guardada en **Cloudflare R2**) |
| `image_alt` | Texto alternativo de la imagen (SEO/accesibilidad) |
| `level` | Nivel (principiante/intermedio/avanzado) → sirve de "categoría" |
| `is_published` | Si está publicado o es borrador |
| `published_at` | Fecha |

### Flujo paso a paso
1. Creas/editas el tutorial en `/admin` y marcas `is_published`.
2. Se guarda en **Postgres**. La imagen se sube a **Cloudflare R2** (no al disco de
   Render, que se borra; R2 es permanente).
3. El frontend, cuando un visitante abre `/es/tutoriales`, **pide los tutoriales al
   backend en vivo** (`GET /api/tutorials?lang=es`) y los muestra.
4. Como es **en vivo (client-side)**, un tutorial nuevo aparece **sin reconstruir**
   el sitio (a diferencia del blog).

### Listado de tutoriales (con identidad propia)
- **Acento índigo** (vs el verde del blog) para diferenciarlos visualmente.
- **Buscador** que filtra por título en tiempo real (en el navegador).
- **Filtro por nivel** (Todos / Principiante / Intermedio / …) en píldoras — son
  las "categorías" de los tutoriales. Se generan solas según los niveles que uses.

### Bilingüe (cambio de idioma)
- Cada idioma tiene su **propio slug** (bueno para SEO): `como-cuidar-un-perro` (es)
  vs `how-to-care-for-a-dog` (en).
- Se enlazan con la **`translation_key`** (mismo valor en ambas versiones).
- Al cambiar de idioma en un tutorial, el sitio busca la versión con la misma
  `translation_key` en el otro idioma y va a **su** slug. Si no existe, muestra
  *"Este tutorial todavía no está disponible en…"*.

### Imágenes en Cloudflare R2
- El disco de Render es **temporal** (se borra al reiniciar/redeploy). Por eso las
  imágenes de tutoriales se guardan en **Cloudflare R2** (almacenamiento S3,
  gratis hasta 10 GB, permanente).
- En Filament, el `FileUpload` usa el disco `s3` (R2). El backend devuelve la URL
  pública (`pub-…r2.dev/…`) y el frontend la muestra.
- Las subidas **temporales** de Livewire siguen usando el disco local (por eso
  el disco por defecto es `local` y solo las imágenes finales van a R2).

### Diferencia BLOG vs TUTORIALES
| | Blog | Tutoriales |
|---|---|---|
| Fuente | Prismic (externo) | Tu base de datos (Filament) |
| Cuándo aparece | Tras reconstruir (build) | En vivo (al instante) |
| Color | Verde esmeralda | Índigo |
| Filtro | Tags (sidebar) | Nivel (píldoras) |

---

## 7. Formulario de contacto + correos reales

**Flujo paso a paso:**
1. El visitante llena el formulario en `/es/contacto`.
2. El **navegador valida** (nombre, correo, mensaje) con las mismas reglas que el
   servidor — es solo comodidad, no seguridad.
3. Si pasa, hace `POST /api/contact` al backend (cruza CORS, ya configurado).
4. Laravel **revalida y limpia** los datos (`ContactRequest`), los **guarda** en la
   base de datos y **manda un correo** al administrador vía **Resend**.
5. Responde `201` y el sitio muestra "¡Gracias por contactarnos!".

**Ver los mensajes:** en `/admin` (panel) o con el endpoint protegido
`GET /api/contact-messages` (requiere el header `X-Admin-Token`).

---

## 8. El botón "Publicar"

En la barra superior del panel hay un botón **"🚀 Publicar"** siempre visible.

**Qué hace:** como Astro es estático y vive en Vercel (otro servidor), el botón
**no corre `astro build` en Laravel**. En su lugar:
1. Llama a la ruta `/publish` del backend.
2. Esa ruta hace un POST al **deploy hook de Vercel** (`DEPLOY_HOOK_URL`).
3. **Vercel** reconstruye y reexporta el sitio (trayendo el contenido nuevo de
   Prismic) y lo despliega.

Sirve sobre todo para **republicar el blog** cuando cambia el contenido de Prismic
(los tutoriales ya aparecen en vivo y no lo necesitan).

---

## 9. Flujos clave (resumen)

**Publicar un post de blog:** Prismic → publicar → botón "Publicar" → Vercel
reconstruye → en vivo.

**Crear un tutorial:** `/admin` → nuevo tutorial (con imagen a R2) → publicar →
aparece en vivo en el sitio (sin reconstruir).

**Enviar contacto:** formulario → `POST /api/contact` → guarda en BD + correo (Resend).

**Cambiar idioma en un tutorial:** busca la `translation_key` en el otro idioma →
va a su slug, o avisa si no existe.

---

## 10. Seguridad

- **Acceso al panel:** el modelo `User` implementa `FilamentUser` (en producción
  solo entra quien debe). Contraseña del admin en variable de entorno
  (`ADMIN_PASSWORD`), no en el código.
- **CORS:** el backend solo acepta peticiones del dominio del frontend (`FRONTEND_URL`).
- **Proxy:** `trustProxies` para que Laravel detecte HTTPS detrás del proxy de Render
  (sin esto, el login no funcionaba).
- **Validación:** doble (cliente + servidor) en el formulario.
- **Rate limiting:** 5 mensajes/hora por IP en el contacto.

---

## 11. Cómo correr en local

**Backend:**
```bash
cd blog-backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # en local usa SQLite
php artisan migrate
php artisan serve                # http://localhost:8000
```

**Frontend:**
```bash
cd blog-frontend
npm install
cp .env.example .env             # PUBLIC_API_URL=http://localhost:8000, etc.
npm run dev                      # http://localhost:4321
```

---

## 12. Servicios y costos

Todo el stack está en **planes gratuitos**:
- **Vercel** (frontend): gratis.
- **Render** (backend + Postgres): gratis (el servidor se "duerme" tras inactividad
  y tarda ~30-50s en despertar la primera vez).
- **Prismic** (blog): gratis.
- **Cloudflare R2** (imágenes): gratis hasta 10 GB.
- **Resend** (correos): gratis (3,000/mes).

El único costo opcional a futuro sería un **dominio propio** (~$10/año), necesario
para enviar correos a cualquier persona y para una URL de marca.

---

*ZOOBLOG — Documentación técnica © 2026*
