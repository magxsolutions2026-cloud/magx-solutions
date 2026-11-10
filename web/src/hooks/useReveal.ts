import { gsap, ScrollTrigger, useGSAP } from '../lib/gsap'

/**
 * Fades/slides every `[data-reveal]` element in as it enters the viewport, batched
 * so siblings that arrive together stagger. Skipped entirely with reduced motion.
 * `deps` re-runs it when content (e.g. services from the API) changes the DOM.
 */
export function useReveal(deps: unknown[] = []) {
  useGSAP(
    () => {
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
      const els = gsap.utils.toArray<HTMLElement>('[data-reveal]')
      gsap.set(els, { autoAlpha: 0, y: 32 })
      ScrollTrigger.batch(els, {
        start: 'top 88%',
        once: true,
        onEnter: (batch) =>
          gsap.to(batch, { autoAlpha: 1, y: 0, duration: 1.1, ease: 'expo.out', stagger: 0.08, overwrite: true }),
      })
    },
    { dependencies: deps, revertOnUpdate: true },
  )
}
