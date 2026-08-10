// Cliente del blog: consume la API de Laravel (reemplaza a Prismic).
const API = import.meta.env.PUBLIC_API_URL ?? "http://localhost:8000";

export type Tag = { name: string; slug: string };
export type Category = { name: string; slug: string };

// Forma que usan todos los componentes del blog.
export type NormalizedPost = {
  uid: string;            // slug
  title: string;
  description: string;    // extracto
  image: string;          // URL de la portada
  imageAlt: string;
  category: Category | null;
  tags: Tag[];
  publishedAt: string;
  content: string;        // HTML
  alternateSlug?: string | null; // versión en el otro idioma
  metaTitle?: string;
  metaDescription?: string;
};

function mapPost(p: any): NormalizedPost {
  return {
    uid: p.slug,
    title: p.title ?? "",
    description: p.excerpt ?? "",
    image: p.cover_image ?? "",
    imageAlt: p.image_alt ?? "",
    category: p.category ? { name: p.category.name, slug: p.category.slug } : null,
    tags: Array.isArray(p.tags) ? p.tags.map((t: any) => ({ name: t.name, slug: t.slug })) : [],
    publishedAt: p.published_at ?? "",
    content: p.content ?? "",
    alternateSlug: p.alternate_slug ?? null,
    metaTitle: p.meta_title ?? undefined,
    metaDescription: p.meta_description ?? undefined,
  };
}

// GET con timeout y respaldo: si el backend no responde, devuelve null.
async function apiGet(path: string): Promise<any> {
  try {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 8000);
    const res = await fetch(`${API}${path}`, { signal: controller.signal });
    clearTimeout(timer);
    return res.ok ? await res.json() : null;
  } catch {
    return null;
  }
}

export async function fetchPosts(lang: "es" | "en"): Promise<NormalizedPost[]> {
  const data = await apiGet(`/api/posts?lang=${lang}`);
  return Array.isArray(data) ? data.map(mapPost) : [];
}

export async function fetchPost(lang: "es" | "en", slug: string): Promise<NormalizedPost | null> {
  const data = await apiGet(`/api/posts/${encodeURIComponent(slug)}?lang=${lang}`);
  return data && data.slug ? mapPost(data) : null;
}
