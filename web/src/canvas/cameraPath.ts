import type { SectionId } from '../store/sceneStore'

export type Vec3 = [number, number, number]

/**
 * Where the camera sits and what it looks at while a section is in view.
 * `frameX` is a lens shift as a fraction of the viewport width: +0.2 slides the
 * subject 20% right without changing perspective (frees a text column on the left).
 */
export type Shot = { position: Vec3; target: Vec3; frameX?: number }

/**
 * The scroll story, as a shot list. CameraRig scrubs between consecutive shots
 * as each section scrolls into view. World layout: the workspace (desk + avatar)
 * sits at the origin; the work deck floats behind it at z ≈ -6.
 *
 * Desktop shots use `frameX` to keep the 3D subject clear of the text column.
 */
export const shots: Record<SectionId, Shot> = {
  hero: { position: [2.4, 1.6, 3.4], target: [0, 0.85, 0.15], frameX: 0.22 },
  // Text-heavy section: rise and look out into the particle field, workspace out of frame.
  services: { position: [-2.5, 5.5, 3], target: [-6, 6.5, -18] },
  // Above the desk looking out at the project deck (canvas/work/WorkDeck.tsx); avatar falls out of frame.
  work: { position: [0, 3.3, 1.6], target: [0, 2.6, -6], frameX: 0.2 },
  testimonials: { position: [0, 4.5, 9], target: [0, 1.2, -4] },
  stack: { position: [3.5, 2.6, 7], target: [0, 1, -2] },
  // In front of the standing avatar (who stepped out from the swivelled chair, facing -x),
  // desk behind him; shifted left so the form owns the right of the screen.
  contact: { position: [-3.4, 1.45, 1.2], target: [-0.4, 1.0, -0.25], frameX: -0.24 },
}

/**
 * Portrait/phone overrides: text stacks on top of the scene there, so pull back
 * and center the subject instead of framing it to one side.
 */
export const mobileShots: Partial<Record<SectionId, Shot>> = {
  // Look above the avatar so it sits in the lower half, under the stacked headline.
  hero: { position: [1.3, 2.6, 5.6], target: [0, 2.55, 0] },
  // Avatar in the upper half; the form scrolls up over the lower half.
  contact: { position: [-3.8, 1.3, 1.0], target: [-0.4, 0.55, -0.25] },
  // Deck in the upper half; the case-study panel stacks below it.
  work: { position: [0, 3.4, 3.4], target: [0, 1.25, -6] },
}

export function shotFor(id: SectionId, portrait: boolean): Shot {
  return (portrait && mobileShots[id]) || shots[id]
}
