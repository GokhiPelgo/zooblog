import * as prismic from "@prismicio/client";

export const repositoryName = "zooblog";

export const createClient = () =>
  prismic.createClient(import.meta.env.PUBLIC_PRISMIC_REPO ?? repositoryName);

// Mapeo entre el lang de la URL y el locale de Prismic
export const langToLocale: Record<string, string> = {
  es: "es-mx",
  en: "en-us",
};

// ── Tipos del Custom Type "post" ──────────────────────────────────────────────
interface PostDocumentData {
  title:       prismic.TitleField;
  description: prismic.RichTextField | prismic.KeyTextField;
  image:       prismic.ImageField;
  tags:        prismic.KeyTextField;
  content:     prismic.RichTextField;
}

export type PostDocument = prismic.PrismicDocumentWithUID<
  PostDocumentData,
  "post"
>;

// ── Tipo normalizado que usan todos los componentes ───────────────────────────
export type NormalizedPost = {
  uid:         string;
  title:       string;
  description: string;
  image:       string;
  imageAlt:    string;
  tags:        string[];
  publishedAt: string;
  content:     string; // HTML renderizado
};

export function normalizePost(doc: PostDocument): NormalizedPost {
  const d = doc.data;

  return {
    uid:         doc.uid,
    title:       prismic.asText(d.title) ?? "",
    description: Array.isArray(d.description)
      ? (prismic.asText(d.description as prismic.RichTextField) ?? "")
      : (d.description ?? ""),
    image:       d.image?.url ?? "",
    imageAlt:    d.image?.alt ?? "",
    tags:
      typeof d.tags === "string"
        ? d.tags.split(",").map((t) => t.trim()).filter(Boolean)
        : [],
    publishedAt: doc.first_publication_date ?? "",
    content:     d.content ? (prismic.asHTML(d.content) ?? "") : "",
  };
}
