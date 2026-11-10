import type { ReactNode } from 'react'
import type { SectionId } from '../store/sceneStore'

type Props = {
  id: SectionId
  eyebrow?: string
  title?: string
  /**
   * `off`: the section gets an opaque backdrop and the 3D render loop pauses while
   * only such sections are on screen (see useCanvasVisibility).
   */
  scene?: 'on' | 'off'
  className?: string
  children?: ReactNode
}

/**
 * Semantic wrapper for every scroll section. `data-section` is what the camera rig,
 * section tracking and canvas visibility key off, so keep ids in sync with SectionId.
 */
export function Section({ id, eyebrow, title, scene = 'on', className = '', children }: Props) {
  const headingId = title ? `${id}-title` : undefined
  return (
    <section
      id={id}
      data-section={id}
      data-scene={scene}
      aria-labelledby={headingId}
      className={scene === 'off' ? 'relative bg-ink' : 'relative'}
    >
      <div className={`mx-auto w-[min(1200px,calc(100%-2rem))] py-28 md:py-40 ${className}`}>
        {eyebrow && <p className="eyebrow mb-4">{eyebrow}</p>}
        {title && (
          <h2 id={headingId} className="font-display text-4xl font-extrabold tracking-tight [font-stretch:110%] md:text-6xl">
            {title}
          </h2>
        )}
        {children}
      </div>
    </section>
  )
}
