import type { ReactNode } from 'react'
import type { SectionId } from '../store/sceneStore'

type Props = {
  id: SectionId
  eyebrow?: string
  title?: string
  className?: string
  children?: ReactNode
}

/**
 * Semantic wrapper for every scroll section. `data-section` is what the camera rig
 * and section-change observers key off, so keep ids in sync with SectionId.
 */
export function Section({ id, eyebrow, title, className = '', children }: Props) {
  const headingId = title ? `${id}-title` : undefined
  return (
    <section
      id={id}
      data-section={id}
      aria-labelledby={headingId}
      className={`relative mx-auto w-[min(1200px,calc(100%-2rem))] py-28 md:py-40 ${className}`}
    >
      {eyebrow && <p className="eyebrow mb-4">{eyebrow}</p>}
      {title && (
        <h2 id={headingId} className="font-display text-4xl font-extrabold tracking-tight [font-stretch:110%] md:text-6xl">
          {title}
        </h2>
      )}
      {children}
    </section>
  )
}
