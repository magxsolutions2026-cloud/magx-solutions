import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import { useGSAP } from '@gsap/react'
import Lenis from 'lenis'

// Register plugins exactly once; every module imports gsap from here.
gsap.registerPlugin(ScrollTrigger, useGSAP)

let lenis: Lenis | null = null

/**
 * Starts Lenis smooth scrolling and drives it from GSAP's ticker so ScrollTrigger
 * (and the camera timelines bound to it) stay frame-perfectly in sync with scroll.
 * Skipped entirely when the user prefers reduced motion.
 */
export function initSmoothScroll() {
  if (lenis || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return () => {}

  lenis = new Lenis({ lerp: 0.1, wheelMultiplier: 1 })
  lenis.on('scroll', ScrollTrigger.update)
  const tick = (time: number) => lenis?.raf(time * 1000)
  gsap.ticker.add(tick)
  gsap.ticker.lagSmoothing(0)

  return () => {
    gsap.ticker.remove(tick)
    lenis?.destroy()
    lenis = null
  }
}

/** Smooth-scrolls to an element id, falling back to native scrolling. */
export function scrollToId(id: string) {
  const el = document.getElementById(id)
  if (!el) return
  if (lenis) lenis.scrollTo(el, { offset: 0, duration: 1.4 })
  else el.scrollIntoView({ behavior: 'smooth' })
}

/** Smooth-scrolls to an absolute page offset (px). */
export function scrollToY(y: number) {
  if (lenis) lenis.scrollTo(y, { duration: 1.2 })
  else window.scrollTo({ top: y, behavior: 'smooth' })
}

export { gsap, ScrollTrigger, useGSAP }
