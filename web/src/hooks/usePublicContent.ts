import { useEffect, useMemo, useState } from 'react'
import { fetchPublicContent, mediaUrl, type PortfolioItem, type PublicContent } from '../lib/api'
import { fallbackProjects, fallbackServices, type Project, type ServiceCopy } from '../content/site'
import { ScrollTrigger } from '../lib/gsap'

let cache: Promise<PublicContent> | null = null

/** Admin portfolio rows -> Project. Items without an image can't be shown on a 3D card, so they're skipped. */
function toProjects(items: PortfolioItem[]): Project[] {
  const str = (v: unknown) => (v == null ? '' : String(v))
  return items
    .filter((i) => i.image)
    .map((i) => ({
      title: str(i.title),
      category: str(i.category),
      year: str(i.year),
      summary: str(i.subtitle) || str(i.description),
      solution: i.subtitle ? str(i.description) : undefined,
      results: [],
      tags: str(i.tags).split(',').map((t) => t.trim()).filter(Boolean),
      cover: mediaUrl(str(i.image))!,
      link: str(i.link) || undefined,
    }))
}

/**
 * Admin-managed content (services, portfolio). Falls back to the static copy in
 * content/site.ts when the API is unreachable or has no active entries, so the
 * page never renders empty.
 */
export function usePublicContent() {
  const [content, setContent] = useState<PublicContent | null>(null)

  useEffect(() => {
    let alive = true
    cache ??= fetchPublicContent()
    cache
      .then((c) => alive && setContent(c))
      .catch(() => alive && setContent({ services: [], portfolio: [], footer: null }))
    return () => {
      alive = false
    }
  }, [])

  // Section heights change once content arrives; re-measure every scroll trigger.
  useEffect(() => {
    if (content) requestAnimationFrame(() => ScrollTrigger.refresh())
  }, [content])

  const services: ServiceCopy[] = content?.services.length
    ? content.services.map((s) => ({ title: String(s.title ?? ''), description: String(s.description ?? '') }))
    : fallbackServices

  const projects: Project[] = useMemo(() => {
    const fromAdmin = content ? toProjects(content.portfolio) : []
    return fromAdmin.length ? fromAdmin : fallbackProjects
  }, [content])

  return { services, projects, loaded: content !== null }
}
