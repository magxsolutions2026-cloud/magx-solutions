import { brand } from '../content/site'
import { useUiStore } from '../store/uiStore'
import { ContactForm } from '../components/ContactForm'

/**
 * "Let's work together". The left half is left open for the 3D avatar, who stands
 * up from the desk when this section is active (canvas/workspace/Workspace.tsx);
 * the form lives on the right. On phones the avatar shows first and the form
 * scrolls up beneath it.
 */
export function Contact() {
  const openBooking = useUiStore((s) => s.openBooking)

  return (
    <div className="grid min-h-dvh items-center pt-[46vh] md:grid-cols-12 md:pt-0">
      <div className="md:col-span-6 md:col-start-7">
        <p className="eyebrow mb-4" data-reveal>05 — Contact</p>
        <h2 id="contact-title" data-reveal className="font-display text-5xl font-extrabold leading-[0.95] tracking-tight [font-stretch:115%] md:text-6xl">
          Let&apos;s work together.
        </h2>
        <p data-reveal className="mt-5 max-w-[46ch] text-lg leading-relaxed text-muted">
          Tell me about the process that’s slowing your team down. I’ll reply with how I’d approach it.
        </p>
        <div data-reveal className="mt-8">
          <ContactForm />
        </div>
        <p data-reveal className="mt-6 text-sm text-muted">
          Prefer to talk it through?{' '}
          <button type="button" onClick={() => openBooking()} className="font-semibold text-volt hover:underline">
            Book a consultation
          </button>{' '}
          or email <span className="text-paper">{brand.email}</span>.
        </p>
      </div>
    </div>
  )
}
