import { processSteps, type ServiceCopy } from '../content/site'
import { useUiStore } from '../store/uiStore'

type Props = { services: ServiceCopy[] }

/**
 * Services as large numbered rows (each with a "book this" shortcut that pre-fills
 * the booking dialog), followed by the four-step delivery process.
 * Rows sit on glass so they read cleanly over the 3D scene behind.
 */
export function Services({ services }: Props) {
  const openBooking = useUiStore((s) => s.openBooking)

  return (
    <>
      <div className="grid gap-8 md:grid-cols-12 md:items-end">
        <div className="md:col-span-7">
          <p className="eyebrow mb-4" data-reveal>01 — Services</p>
          <h2 id="services-title" data-reveal className="text-balance font-display text-5xl font-extrabold leading-[0.95] tracking-tight [font-stretch:115%] md:text-7xl">
            Systems that work for your team.
          </h2>
        </div>
        <p data-reveal className="text-lg leading-relaxed text-muted md:col-span-5">
          I turn manual workflows, spreadsheets and paperwork into secure, data-rich web applications — portals, dashboards and
          integrations that keep everyone working from the same numbers.
        </p>
      </div>

      <ul className="mt-16 overflow-hidden rounded-3xl border border-line">
        {services.map((s, i) => (
          <li key={s.title} data-reveal className="group glass border-0 border-b border-line last:border-b-0">
            <div className="grid items-start gap-4 p-6 transition-colors duration-500 group-hover:bg-paper/[0.03] md:grid-cols-12 md:gap-8 md:p-10">
              <span className="font-mono text-sm text-muted md:col-span-1 md:pt-3">{String(i + 1).padStart(2, '0')}</span>
              <h3 className="font-display text-3xl font-bold tracking-tight transition-transform duration-500 [font-stretch:110%] group-hover:translate-x-2 md:col-span-4 md:text-4xl">
                {s.title}
              </h3>
              <p className="leading-relaxed text-muted md:col-span-5 md:pt-2">{s.description}</p>
              <div className="md:col-span-2 md:flex md:justify-end md:pt-1">
                <button
                  type="button"
                  onClick={() => openBooking(s.title)}
                  aria-label={`Book a consultation about ${s.title}`}
                  className="inline-flex items-center gap-2 rounded-full border border-line-strong px-4 py-2 text-sm transition-colors hover:border-volt hover:text-volt"
                >
                  Discuss
                  <svg width="14" height="14" viewBox="0 0 18 18" aria-hidden="true">
                    <path d="M3 9h11M10 4.5L14.5 9 10 13.5" stroke="currentColor" strokeWidth="1.8" fill="none" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                </button>
              </div>
            </div>
          </li>
        ))}
      </ul>

      <div className="mt-24">
        <p className="eyebrow mb-8" data-reveal>How a project runs</p>
        <ol className="grid gap-px overflow-hidden rounded-3xl border border-line bg-line sm:grid-cols-2 lg:grid-cols-4">
          {processSteps.map((step, i) => (
            <li key={step.title} data-reveal className="bg-ink/75 p-7 backdrop-blur-md">
              <span className="font-mono text-xs text-volt">Step {i + 1}</span>
              <h3 className="mt-4 font-display text-2xl font-bold tracking-tight [font-stretch:110%]">{step.title}</h3>
              <p className="mt-3 text-sm leading-relaxed text-muted">{step.description}</p>
            </li>
          ))}
        </ol>
      </div>
    </>
  )
}
