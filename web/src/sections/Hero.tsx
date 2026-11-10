import { useRef } from 'react'
import { SplitText } from 'gsap/SplitText'
import { gsap, scrollToId, useGSAP } from '../lib/gsap'
import { hero, proofStrip } from '../content/site'
import { useSceneStore } from '../store/sceneStore'
import { useUiStore } from '../store/uiStore'
import { MagneticButton } from '../components/MagneticButton'

gsap.registerPlugin(SplitText)

/**
 * Cinematic opener. Text owns the left ~55% of the viewport; the 3D workspace is
 * framed on the right by the camera rig's lens shift (cameraPath.ts `frameX`).
 * On phones the text stacks on top and the avatar sits below it.
 */
export function Hero() {
  const root = useRef<HTMLDivElement>(null)
  const sceneReady = useSceneStore((s) => s.sceneReady)
  const openBooking = useUiStore((s) => s.openBooking)

  // Entrance: runs once the pre-loader starts leaving, so it isn't wasted behind it.
  useGSAP(
    () => {
      if (!sceneReady) return
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        gsap.set('[data-hero-in]', { autoAlpha: 1 })
        return
      }
      let split: SplitText | undefined
      document.fonts.ready.then(() => {
        split = SplitText.create('[data-hero-title]', { type: 'lines,words', mask: 'lines' })
        gsap
          .timeline({ delay: 0.35, defaults: { ease: 'expo.out' } })
          .set('[data-hero-in]', { autoAlpha: 1 })
          .from(split.words, { yPercent: 115, duration: 1.3, stagger: 0.08 })
          .from('[data-hero-fade]', { autoAlpha: 0, y: 24, duration: 1, stagger: 0.1 }, '-=0.9')
      })
      return () => split?.revert()
    },
    { scope: root, dependencies: [sceneReady] },
  )

  return (
    <div ref={root} className="relative flex min-h-dvh flex-col">
      {/* invisible until the entrance runs, so nothing flashes in its final state first */}
      <div data-hero-in className="invisible flex flex-1 flex-col justify-start pt-32 md:justify-center md:pt-24">
        <p data-hero-fade className="eyebrow mb-6 flex items-center gap-3">
          <span className="relative flex size-2">
            <span className="absolute inline-flex size-full animate-ping rounded-full bg-volt opacity-60 motion-reduce:animate-none" />
            <span className="relative inline-flex size-2 rounded-full bg-volt" />
          </span>
          {hero.eyebrow}
        </p>

        <h1
          id="hero-title"
          data-hero-title
          className="font-display text-[clamp(3.1rem,6.4vw,7.5rem)] font-black leading-[0.9] tracking-[-0.03em] [font-stretch:112%] md:whitespace-nowrap"
        >
          {hero.headline[0]}
          <br />
          {hero.headline[1].replace(/\.$/, '')}
          <span className="text-volt">.</span>
        </h1>

        <p data-hero-fade className="mt-6 max-w-[38ch] text-base leading-relaxed text-muted md:mt-8 md:text-xl">
          {hero.sub}
        </p>

        <div data-hero-fade className="mt-8 flex flex-wrap items-center gap-x-4 gap-y-2 md:mt-10">
          <MagneticButton onClick={() => openBooking()} aria-haspopup="dialog">
            {hero.primaryCta}
            <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true" className="transition-transform duration-300 group-hover:translate-x-1">
              <path d="M3 9h11M10 4.5L14.5 9 10 13.5" stroke="currentColor" strokeWidth="1.8" fill="none" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </MagneticButton>
          <button
            type="button"
            onClick={() => scrollToId('work')}
            className="rounded-full px-5 py-4 text-paper underline decoration-line-strong underline-offset-8 transition-colors hover:decoration-volt"
          >
            {hero.secondaryCta}
          </button>
        </div>
      </div>

      {/* Proof strip: concrete, verifiable results instead of adjectives */}
      <ul data-hero-in className="invisible mb-10 mt-[42vh] grid gap-px overflow-hidden rounded-2xl border border-line bg-line md:mb-14 md:mt-16 md:max-w-[54vw] md:grid-cols-3">
        {proofStrip.map((p) => (
          <li key={p.value} data-hero-fade className="bg-ink/70 p-5 backdrop-blur-md">
            <p className="font-display text-3xl font-extrabold tracking-tight [font-stretch:115%]">{p.value}</p>
            <p className="mt-1 text-sm leading-snug text-muted">{p.label}</p>
          </li>
        ))}
      </ul>
    </div>
  )
}
