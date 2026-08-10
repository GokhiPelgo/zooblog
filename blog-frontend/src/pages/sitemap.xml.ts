import type { APIRoute } from "astro";
import { fetchPosts } from "../lib/blog";

const siteUrl = import.meta.env.PUBLIC_SITE_URL ?? "http://localhost:4321";
const API = import.meta.env.PUBLIC_API_URL ?? "http://localhost:8000";

// Pares de páginas fijas (para generar los enlaces hreflang es/en).
const pagePairs = [
  { es: "/es", en: "/en" },
  { es: "/es/blog", en: "/en/blog" },
  { es: "/es/tutoriales", en: "/en/tutorials" },
  { es: "/es/sobre-nosotros", en: "/en/about" },
  { es: "/es/servicios", en: "/en/services" },
  { es: "/es/contacto", en: "/en/contact" },
];

type UrlOpts = { lastmod?: string; alternates?: { es: string; en: string } };

function urlEntry(loc: string, opts: UrlOpts = {}): string {
  const lastmod = opts.lastmod ? `\n    <lastmod>${opts.lastmod}</lastmod>` : "";
  let links = "";
  if (opts.alternates) {
    const es = `${siteUrl}${opts.alternates.es}`;
    const en = `${siteUrl}${opts.alternates.en}`;
    links =
      `\n    <xhtml:link rel="alternate" hreflang="es" href="${es}"/>` +
      `\n    <xhtml:link rel="alternate" hreflang="en" href="${en}"/>` +
      `\n    <xhtml:link rel="alternate" hreflang="x-default" href="${es}"/>`;
  }
  return `  <url>\n    <loc>${loc}</loc>${lastmod}${links}\n  </url>`;
}

// Trae los tutoriales publicados del backend (con respaldo si no responde).
async function fetchTutorials(lang: "es" | "en"): Promise<any[]> {
  try {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 8000);
    const res = await fetch(`${API}/api/tutorials?lang=${lang}`, { signal: controller.signal });
    clearTimeout(timer);
    return res.ok ? await res.json() : [];
  } catch {
    return [];
  }
}

export const GET: APIRoute = async () => {
  // Contenido dinámico: posts del blog y tutoriales (todo del backend).
  const [postsEs, postsEn, tutsEs, tutsEn] = await Promise.all([
    fetchPosts("es"),
    fetchPosts("en"),
    fetchTutorials("es"),
    fetchTutorials("en"),
  ]);

  const entries: string[] = [];

  // 1) Páginas fijas, con sus alternates es/en.
  for (const pair of pagePairs) {
    entries.push(urlEntry(`${siteUrl}${pair.es}`, { alternates: pair }));
    entries.push(urlEntry(`${siteUrl}${pair.en}`, { alternates: pair }));
  }

  // 2) Posts del blog (backend).
  for (const d of postsEs) {
    if (d.uid) entries.push(urlEntry(`${siteUrl}/es/blog/${d.uid}`, { lastmod: (d.publishedAt ?? "").slice(0, 10) || undefined }));
  }
  for (const d of postsEn) {
    if (d.uid) entries.push(urlEntry(`${siteUrl}/en/blog/${d.uid}`, { lastmod: (d.publishedAt ?? "").slice(0, 10) || undefined }));
  }

  // 3) Tutoriales (backend).
  for (const t of tutsEs) {
    if (t.slug) entries.push(urlEntry(`${siteUrl}/es/tutoriales/ver?slug=${t.slug}`, { lastmod: (t.published_at ?? "").slice(0, 10) || undefined }));
  }
  for (const t of tutsEn) {
    if (t.slug) entries.push(urlEntry(`${siteUrl}/en/tutorials/ver?slug=${t.slug}`, { lastmod: (t.published_at ?? "").slice(0, 10) || undefined }));
  }

  const xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
${entries.join("\n")}
</urlset>`;

  return new Response(xml, {
    headers: { "Content-Type": "application/xml; charset=utf-8" },
  });
};
