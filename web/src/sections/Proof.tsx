import { credentials, testimonials } from '../content/site'

/**
 * Social proof built only from verifiable facts. The testimonials row appears
 * automatically once real quotes are added to content/site.ts.
 */
export function Proof() {
  return (
    <>
      <div className="max-w-3xl">
        <p className="eyebrow mb-4" data-reveal>03 — Proof</p>
        <h2 id="testimonials-title" data-reveal className="font-display text-5xl font-extrabold leading-[0.95] tracking-tight [font-stretch:115%] md:text-7xl">
          Proof, not promises.
        </h2>
        <p data-reveal className="mt-6 text-lg leading-relaxed text-muted">
          Shipped systems in daily use, and the credentials behind them.
        </p>
      </div>

      {testimonials.length > 0 && (
        <ul className="mt-16 grid gap-6 md:grid-cols-3">
          {testimonials.map((t) => (
            <li key={t.name} data-reveal className="rounded-3xl border border-line p-8">
              <blockquote>
                <p className="text-lg leading-relaxed">“{t.quote}”</p>
                <footer className="mt-6 text-sm">
                  <span className="font-semibold">{t.name}</span>
                  <span className="text-muted"> — {t.role}</span>
                </footer>
              </blockquote>
            </li>
          ))}
        </ul>
      )}

      <ul className="mt-16 grid gap-px overflow-hidden rounded-3xl border border-line bg-line md:grid-cols-2">
        {credentials.map((c) => (
          <li key={c.title} data-reveal className="bg-ink p-8 md:p-10">
            <p className="font-mono text-xs uppercase tracking-[0.16em] text-volt">{c.kicker}</p>
            <h3 className="mt-4 font-display text-2xl font-bold leading-tight tracking-tight [font-stretch:110%] md:text-3xl">{c.title}</h3>
            <p className="mt-3 leading-relaxed text-muted">{c.body}</p>
          </li>
        ))}
      </ul>
    </>
  )
}
