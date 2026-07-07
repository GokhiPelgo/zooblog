import type { APIRoute } from "astro";
import { createClient } from "../prismicio";

export const GET: APIRoute = async () => {
  const client  = createClient();
  const siteUrl = import.meta.env.PUBLIC_SITE_URL ?? "http://localhost:4321";

  const [docsEs, docsEn] = await Promise.all([
    client.getAllByType("post", { lang: "es-mx" }),
    client.getAllByType("post", { lang: "en-us" }),
  ]);

  const staticUrls = [
    "/es", "/en",
    "/es/blog", "/en/blog",
    "/es/sobre-nosotros", "/en/about",
    "/es/servicios", "/en/services",
    "/es/contacto", "/en/contact",
  ].map((path) => `${siteUrl}${path}`);

  const postUrls = [
    ...docsEs.filter((d) => d.uid).map((d) => `${siteUrl}/es/blog/${d.uid}`),
    ...docsEn.filter((d) => d.uid).map((d) => `${siteUrl}/en/blog/${d.uid}`),
  ];

  const urls = [...staticUrls, ...postUrls];

  const xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${urls.map((url) => `  <url><loc>${url}</loc></url>`).join("\n")}
</urlset>`;

  return new Response(xml, {
    headers: { "Content-Type": "application/xml; charset=utf-8" },
  });
};
