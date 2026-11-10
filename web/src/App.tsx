import { lazy, Suspense, useEffect } from 'react'
import { initSmoothScroll } from './lib/gsap'
import { useActiveSection } from './hooks/useActiveSection'
import { SceneErrorBoundary } from './components/SceneErrorBoundary'
import { Section } from './components/Section'
import { Navbar } from './sections/Navbar'

// three.js + R3F load in their own chunk, after the HTML shell is interactive.
const SceneCanvas = lazy(() => import('./canvas/SceneCanvas'))

export default function App() {
  useEffect(() => initSmoothScroll(), [])
  useActiveSection()

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
        <Section id="hero" className="flex min-h-dvh flex-col justify-start pt-32 md:justify-center md:pt-28">
          <p className="eyebrow mb-6">MAGX Solutions — Freelance developer</p>
          <h1 className="max-w-[11ch] font-display text-6xl font-black leading-[0.88] tracking-tight [font-stretch:120%] md:text-8xl">
            Software that moves your business.
          </h1>
        </Section>
        <Section id="services" eyebrow="01 — Services" title="Services" className="min-h-dvh" />
        <Section id="work" eyebrow="02 — Selected work" title="Work" className="min-h-dvh" />
        <Section id="testimonials" eyebrow="03 — Clients" title="Testimonials" scene="off" className="min-h-dvh" />
        <Section id="stack" eyebrow="04 — Stack" title="Tech stack" scene="off" className="min-h-dvh" />
        <Section id="contact" eyebrow="05 — Contact" title="Let's work together" className="min-h-dvh" />
      </main>
    </>
  )
}
