import { useEffect, useState } from 'react'
import { fetchPublicContent, type PublicContent } from '../lib/api'
import { fallbackServices, type ServiceCopy } from '../content/site'
import { ScrollTrigger } from '../lib/gsap'

let cache: Promise<PublicContent> | null = null

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

  return { services, portfolio: content?.portfolio ?? [], loaded: content !== null }
}
