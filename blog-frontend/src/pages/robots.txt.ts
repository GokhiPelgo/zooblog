import type { APIRoute } from "astro";

export const GET: APIRoute = () => {
  const siteUrl = import.meta.env.PUBLIC_SITE_URL ?? "http://localhost:4321";

  const content = `User-agent: *
Allow: /

Sitemap: ${siteUrl}/sitemap.xml
`;

  return new Response(content, {
    headers: { "Content-Type": "text/plain" },
  });
};
