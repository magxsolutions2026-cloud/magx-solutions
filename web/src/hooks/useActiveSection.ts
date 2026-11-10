import { ScrollTrigger, useGSAP } from '../lib/gsap'
import { useSceneStore, type SectionId } from '../store/sceneStore'

/**
 * Publishes which section is crossing the middle of the viewport to the scene store.
 * The particle pulse, reduced-motion camera cuts and (later) the nav highlight key off this.
 */
export function useActiveSection() {
  useGSAP(() => {
    const setActive = useSceneStore.getState().setActiveSection
    document.querySelectorAll<HTMLElement>('[data-section]').forEach((el) => {
      const id = el.dataset.section as SectionId
      ScrollTrigger.create({
        trigger: el,
        start: 'top center',
        end: 'bottom center',
        onToggle: (self) => self.isActive && setActive(id),
      })
    })
  })
}
