import { useRef, useState } from 'react'
import type { Project } from '../content/site'
import { ScrollTrigger, scrollToY, useGSAP } from '../lib/gsap'
import { workDeck } from '../canvas/work/workDeckState'
import { useUiStore } from '../store/uiStore'

/** Extra scroll distance per additional project, as a fraction of the viewport. */
const SCROLL_PER_PROJECT_VH = 70

/**
 * Selected work. A tall scroll track with a sticky stage: scrolling through the track
 * slides the 3D deck (canvas/work/WorkDeck.tsx) card by card, and this panel shows the
 * focused project's case study. Every project is in the DOM for screen readers;
 * only the focused one is visible.
 */
export function Work({ projects }: { projects: Project[] }) {
  const track = useRef<HTMLDivElement>(null)
  const [active, setActive] = useState(0)
  const openBooking = useUiStore((s) => s.openBooking)
  const n = projects.length

  useGSAP(
    () => {
      const st = ScrollTrigger.create({
        trigger: track.current,
        start: 'top top',
        end: 'bottom bottom',
        onUpdate: (self) => {
          workDeck.progress = self.progress
          const i = Math.round(self.progress * Math.max(0, n - 1))
          setActive((prev) => (prev === i ? prev : i))
        },
      })
      workDeck.select = (i) => scrollToY(st.start + (n > 1 ? i / (n - 1) : 0) * (st.end - st.start))
      return () => {
        workDeck.select = undefined
      }
    },
    { dependencies: [n], revertOnUpdate: true },
  )

  const go = (i: number) => workDeck.select?.(Math.max(0, Math.min(n - 1, i)))

  return (
    <div ref={track} style={{ height: `calc(100dvh + ${(n - 1) * SCROLL_PER_PROJECT_VH}vh)` }}>
      <div className="sticky top-0 flex h-dvh flex-col justify-end pb-8 pt-28 md:justify-between md:pb-14">
        <div className="hidden md:block">
          <p className="eyebrow mb-4">02 — Selected work</p>
          <h2 id="work-title" className="font-display text-5xl font-extrabold leading-[0.95] tracking-tight [font-stretch:115%] lg:text-6xl">
            Built for real
            <br />
            operations.
          </h2>
        </div>
        <h2 className="sr-only md:hidden">Selected work</h2>

        <div className="glass rounded-3xl p-6 md:max-w-[min(440px,40vw)] md:p-8" aria-live="polite">
          <ol>
            {projects.map((p, i) => (
              <li key={p.title} className={i === active ? 'animate-rise' : 'sr-only'}>
                <p className="font-mono text-xs uppercase tracking-[0.16em] text-volt">
                  {[p.category, p.year].filter(Boolean).join(' · ')}
                </p>
                <h3 className="mt-3 font-display text-2xl font-bold leading-tight tracking-tight [font-stretch:110%] md:text-3xl">{p.title}</h3>
                <p className="mt-3 text-sm leading-relaxed text-muted md:text-base">{p.summary}</p>
                {(p.problem || p.solution) && (
                  <dl className="mt-5 hidden gap-3 text-sm [@media(min-width:768px)_and_(min-height:1000px)]:grid">
                    {p.problem && (
                      <div>
                        <dt className="text-paper">Problem</dt>
                        <dd className="text-muted">{p.problem}</dd>
                      </div>
                    )}
                    {p.solution && (
                      <div>
                        <dt className="text-paper">Solution</dt>
                        <dd className="text-muted">{p.solution}</dd>
                      </div>
                    )}
                  </dl>
                )}
                {p.results.length > 0 && (
                  <ul className="mt-5 hidden space-y-1.5 text-sm md:block">
                    {p.results.map((r) => (
                      <li key={r} className="flex gap-2">
                        <span aria-hidden="true" className="mt-2 size-1 shrink-0 rounded-full bg-volt" />
                        {r}
                      </li>
                    ))}
                  </ul>
                )}
                {p.tags.length > 0 && (
                  <ul className="mt-5 hidden flex-wrap gap-1.5 md:flex" aria-label="Tools">
                    {p.tags.map((t) => (
                      <li key={t} className="rounded-full border border-line px-3 py-1 text-xs text-muted">{t}</li>
                    ))}
                  </ul>
                )}
                <div className="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
                  <button type="button" onClick={() => openBooking()} className="font-semibold text-volt hover:underline">
                    Discuss a project like this →
                  </button>
                  {p.link && (
                    <a href={p.link} target="_blank" rel="noreferrer" className="text-muted underline underline-offset-4 hover:text-paper">
                      Visit project
                    </a>
                  )}
                </div>
              </li>
            ))}
          </ol>

          {n > 1 && (
            <div className="mt-6 flex items-center gap-4 border-t border-line pt-5">
              <span className="font-mono text-xs text-muted" aria-hidden="true">
                {String(active + 1).padStart(2, '0')} / {String(n).padStart(2, '0')}
              </span>
              <div className="h-px flex-1 bg-line" aria-hidden="true">
                <div className="h-px bg-volt transition-[width] duration-500" style={{ width: `${((active + 1) / n) * 100}%` }} />
              </div>
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => go(active - 1)}
                  disabled={active === 0}
                  aria-label="Previous project"
                  className="rounded-full border border-line-strong p-2 transition-colors hover:border-volt disabled:opacity-30"
                >
                  <svg width="14" height="14" viewBox="0 0 18 18" aria-hidden="true"><path d="M15 9H4M8 4.5L3.5 9 8 13.5" stroke="currentColor" strokeWidth="1.8" fill="none" strokeLinecap="round" strokeLinejoin="round" /></svg>
                </button>
                <button
                  type="button"
                  onClick={() => go(active + 1)}
                  disabled={active === n - 1}
                  aria-label="Next project"
                  className="rounded-full border border-line-strong p-2 transition-colors hover:border-volt disabled:opacity-30"
                >
                  <svg width="14" height="14" viewBox="0 0 18 18" aria-hidden="true"><path d="M3 9h11M10 4.5L14.5 9 10 13.5" stroke="currentColor" strokeWidth="1.8" fill="none" strokeLinecap="round" strokeLinejoin="round" /></svg>
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
