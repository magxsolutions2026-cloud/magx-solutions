import { useEffect } from 'react'
import { useProgress } from '@react-three/drei'
import { useSceneStore } from '../store/sceneStore'

/** Never flash the pre-loader for less than this, even on a warm cache. */
const MIN_VISIBLE_MS = 900

/**
 * Drives the static #preloader markup in index.html: mirrors drei's asset-loading
 * progress into it, then fades it out once the 3D scene reports ready.
 */
export function usePreloader() {
  const { progress, item } = useProgress()
  const sceneReady = useSceneStore((s) => s.sceneReady)

  useEffect(() => {
    const el = document.getElementById('preloader')
    if (!el) return
    const pct = Math.round(sceneReady ? 100 : progress)
    el.style.setProperty('--p', String(Math.max(0.04, pct / 100)))
    const pctEl = el.querySelector('[data-pl-pct]')
    const labelEl = el.querySelector('[data-pl-label]')
    if (pctEl) pctEl.textContent = `${pct}%`
    if (labelEl && item) labelEl.textContent = item.split('/').pop()?.split('.')[0] ?? 'Loading assets'
  }, [progress, item, sceneReady])

  useEffect(() => {
    if (!sceneReady) return
    const el = document.getElementById('preloader')
    if (!el) return
    const elapsed = performance.now()
    const wait = Math.max(0, MIN_VISIBLE_MS - elapsed)
    const t = window.setTimeout(() => {
      el.classList.add('is-done')
      el.setAttribute('aria-busy', 'false')
      el.addEventListener('transitionend', () => el.remove(), { once: true })
    }, wait)
    return () => window.clearTimeout(t)
  }, [sceneReady])
}
