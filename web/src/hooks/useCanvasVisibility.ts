import { useEffect, useState } from 'react'

/**
 * Decides whether the render loop should run.
 *
 * The canvas is a fixed backdrop, so it is never literally scrolled away. Instead,
 * sections that fully cover it opt out with `data-scene="off"` (they get an opaque
 * backdrop). When only opted-out sections are on screen — or the tab is hidden —
 * the loop stops and the GPU idles; the last frame stays painted underneath.
 */
export function useCanvasVisibility() {
  const [active, setActive] = useState(true)

  useEffect(() => {
    const sections = Array.from(document.querySelectorAll<HTMLElement>('[data-section]'))
    const visible = new Set<HTMLElement>()
    let tabVisible = document.visibilityState === 'visible'

    const update = () => {
      const sceneOnScreen = [...visible].some((el) => el.dataset.scene !== 'off')
      setActive(tabVisible && (sceneOnScreen || visible.size === 0))
    }

    const io = new IntersectionObserver((entries) => {
      for (const e of entries) {
        if (e.isIntersecting) visible.add(e.target as HTMLElement)
        else visible.delete(e.target as HTMLElement)
      }
      update()
    })
    sections.forEach((el) => io.observe(el))

    const onVisibility = () => {
      tabVisible = document.visibilityState === 'visible'
      update()
    }
    document.addEventListener('visibilitychange', onVisibility)

    return () => {
      io.disconnect()
      document.removeEventListener('visibilitychange', onVisibility)
    }
  }, [])

  return active
}
