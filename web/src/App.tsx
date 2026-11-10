import { lazy, Suspense, useEffect } from 'react'
import { initSmoothScroll } from './lib/gsap'
import { useActiveSection } from './hooks/useActiveSection'
import { usePublicContent } from './hooks/usePublicContent'
import { useReveal } from './hooks/useReveal'
import { SceneErrorBoundary } from './components/SceneErrorBoundary'
import { Section } from './components/Section'
import { BookingDialog } from './components/BookingDialog'
import { Navbar } from './sections/Navbar'
import { Hero } from './sections/Hero'
import { Services } from './sections/Services'
import { Proof } from './sections/Proof'
import { Work } from './sections/Work'
import { Contact } from './sections/Contact'
import { TechStack } from './sections/TechStack'
import { Footer } from './sections/Footer'

// three.js + R3F load in their own chunk, after the HTML shell is interactive.
const SceneCanvas = lazy(() => import('./canvas/SceneCanvas'))

export default function App() {
  useEffect(() => initSmoothScroll(), [])
  useActiveSection()
  const { services, projects, loaded } = usePublicContent()
  useReveal([loaded])

  return (
    <>
      <SceneErrorBoundary>
        <Suspense fallback={null}>
          <SceneCanvas projects={projects} />
        </Suspense>
      </SceneErrorBoundary>

      <a href="#main" className="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50">
        Skip to content
      </a>
      <Navbar />

      <main id="main">
        <Section id="hero" padded={false}>
          <Hero />
        </Section>
        <Section id="services">
          <Services services={services} />
        </Section>
        <Section id="work" padded={false}>
          <Work projects={projects} />
        </Section>
        <Section id="testimonials" scene="off">
          <Proof />
        </Section>
        <Section id="stack" scene="off">
          <TechStack />
        </Section>
        <Section id="contact" padded={false} className="pb-24">
          <Contact />
        </Section>
      </main>
      <Footer />

      <BookingDialog serviceOptions={services.map((s) => s.title)} />
    </>
  )
}
