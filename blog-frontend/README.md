# ZOOBLOG — Frontend

Blog educativo sobre animales. Construido con Astro, Tailwind CSS 4, GSAP, Lenis y Prismic CMS.

---

## ⚠️ Antes de empezar: ZooBlog son DOS proyectos

ZooBlog está formado por dos carpetas **separadas** que trabajan juntas:

- **`blog-frontend`** (Astro) — **esta carpeta**: el sitio que ve el visitante (blog, artículos y formulario).
- **`blog-backend`** (Laravel) — recibe los mensajes del formulario de contacto y el webhook de Prismic.

Son **dos aplicaciones independientes que se comunican por HTTP**. Pueden correr en servidores distintos.

```
   Visitante
      │
      ▼
┌──────────────────────┐   POST /api/contact   ┌──────────────────────┐
│  Frontend (Astro)    │ ────────────────────▶ │  Backend (Laravel)   │
│  blog-frontend ◀ aquí│                       │  blog-backend        │
│  · Muestra el blog   │                       │  · Guarda contacto   │
│  · Formulario        │                       │  · Webhook Prismic   │
└──────────────────────┘                       └──────────────────────┘
```

**Qué necesita estar corriendo:**

- Para **leer el blog**: basta esta carpeta (el frontend). Los artículos vienen de Prismic.
- Para el **formulario de contacto**: necesitas este frontend **y** el `blog-backend` corriendo a la vez.

---

## Requisitos

Antes de instalar, asegúrate de tener:

- **Node.js** v22.12 o superior — https://nodejs.org
- **npm** v10 o superior (viene con Node)
- Una cuenta en **Prismic** — https://prismic.io

Verifica tu versión con:

```bash
node -v
npm -v
```

---

## Instalación

### 1. Clona el repositorio e instala dependencias

```bash
cd blog-frontend
npm install
```

### 2. Configura las variables de entorno

Crea un archivo `.env` en la raíz del proyecto:

```env
PUBLIC_API_URL=http://localhost:8000
PUBLIC_SITE_URL=http://localhost:4321
PUBLIC_PRISMIC_REPO=tu-repositorio
```

### 3. Configura Prismic

El proyecto lee los artículos desde un repositorio de Prismic. Necesitas:

1. Crear una cuenta en https://prismic.io
2. Crear un nuevo repositorio — puedes llamarlo como quieras
3. Crear un **Custom Type** de tipo Repeatable llamado `post` con los siguientes campos:

| Campo | Tipo |
|-------|------|
| `uid` | UID (arrástralo al schema) |
| `title` | Title |
| `description` | Key Text |
| `image` | Image |
| `tags` | Key Text |
| `content` | Rich Text |

4. Activar los locales en **Settings → Translations & Locales**:
   - Master locale: `es-mx`
   - Añadir: `en-us`

5. Indicar el nombre de tu repositorio en el `.env` (recomendado):

```env
PUBLIC_PRISMIC_REPO=tu-repositorio
```

> El proyecto lee este valor desde `PUBLIC_PRISMIC_REPO`. Si no lo defines,
> usa por defecto el nombre `zooblog` configurado en `src/prismicio.ts`.

### 4. Corre el proyecto

```bash
npm run dev
```

El sitio estará disponible en http://localhost:4321

---

## Comandos disponibles

| Comando | Descripción |
|---------|-------------|
| `npm run dev` | Servidor de desarrollo en localhost:4321 |
| `npm run build` | Genera el sitio estático en `/dist` |
| `npm run preview` | Previsualiza el build antes de deployar |

---

## Cómo publicar un artículo

1. Entra a tu repositorio en prismic.io
2. Crea un nuevo documento de tipo `post`
3. Llena todos los campos (título, descripción, imagen, tags, contenido)
4. En el campo **UID** escribe el slug del artículo, ej: `perros-inteligentes`
5. Publica el documento
6. Corre `npm run build` para que aparezca en el sitio

Para publicarlo en inglés, abre el documento publicado y usa la opción **Translate** para crear la versión `en-us`.

---

## Cómo funciona el formulario de contacto

El formulario hace un POST al backend de Laravel en `PUBLIC_API_URL/api/contact`. Asegúrate de que el backend esté corriendo antes de probarlo.

---

## Estructura del proyecto

```
src/
  components/     → Componentes reutilizables (Header, Footer, BlogCard, etc.)
  i18n/           → Traducciones y utilidades de idioma
  layouts/        → BaseLayout con OG tags, GSAP y Lenis
  pages/
    [lang]/
      index.astro              → Home
      [slug].astro             → About, Servicios, Contacto
      blog/
        index.astro            → Listado de artículos
        [slug].astro           → Artículo individual
      categoria/[tag].astro    → Posts por etiqueta
    sitemap.xml.ts             → Sitemap dinámico
  prismicio.ts    → Cliente de Prismic y tipo NormalizedPost
public/
  images/         → Imágenes estáticas
  robots.txt
```
