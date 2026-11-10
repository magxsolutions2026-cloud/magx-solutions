import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './styles/globals.css'
import { LazyMotion, domAnimation } from 'motion/react'
import App from './App'

// Dev-only: ?__scroll=0.5 jumps to that fraction of the page after load (headless screenshots).
if (import.meta.env.DEV) {
  const frac = new URLSearchParams(location.search).get('__scroll')
  if (frac !== null) {
    setTimeout(() => window.scrollTo(0, Number(frac) * (document.documentElement.scrollHeight - innerHeight)), 2500)
  }
}

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    {/* `m.*` components + this feature bundle keep Framer Motion's footprint small. */}
    <LazyMotion features={domAnimation} strict>
      <App />
    </LazyMotion>
  </StrictMode>,
)
