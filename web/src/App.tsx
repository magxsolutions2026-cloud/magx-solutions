import { lazy, Suspense, useEffect } from 'react'
import { initSmoothScroll } from './lib/gsap'
import { SceneErrorBoundary } from './components/SceneErrorBoundary'
import { Section } from './components/Section'
import { Navbar } from './sections/Navbar'

// three.js + R3F load in their own chunk, after the HTML shell is interactive.
const SceneCanvas = lazy(() => import('./canvas/SceneCanvas'))

export default function App() {
  useEffect(() => initSmoothScroll(), [])

  return (
    <>
      <SceneErrorBoundary>
        <Suspense fallback={null}>
          <SceneCanvas />
        </Suspense>
      </SceneErrorBoundary>

      <a href="#main" className="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50">
        Skip to content
      </a>
      <Navbar />

      {/* Phase 3–5 replace these stubs with the designed sections. */}
      <main id="main">
        <Section id="hero" className="flex min-h-dvh flex-col justify-center">
          <h1 className="font-display text-6xl font-black leading-[0.9] tracking-tight [font-stretch:120%] md:text-[9rem]">
            Hero
          </h1>
        </Section>
        <Section id="services" eyebrow="01 — Services" title="Services" />
        <Section id="work" eyebrow="02 — Selected work" title="Work" />
        <Section id="testimonials" eyebrow="03 — Clients" title="Testimonials" />
        <Section id="stack" eyebrow="04 — Stack" title="Tech stack" />
        <Section id="contact" eyebrow="05 — Contact" title="Let's work together" />
      </main>
    </>
  )
}
