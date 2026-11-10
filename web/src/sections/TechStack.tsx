import { techStack } from '../content/site'

/** Tools grouped by discipline, as a bordered grid of chips. */
export function TechStack() {
  return (
    <>
      <div className="grid gap-8 md:grid-cols-12 md:items-end">
        <div className="md:col-span-7">
          <p className="eyebrow mb-4" data-reveal>04 — Stack</p>
          <h2 id="stack-title" data-reveal className="font-display text-5xl font-extrabold leading-[0.95] tracking-tight [font-stretch:115%] md:text-7xl">
            Tools I ship with.
          </h2>
        </div>
        <p data-reveal className="text-lg leading-relaxed text-muted md:col-span-5">
          Proven, well-supported technology — chosen so your system stays fast, secure and easy to maintain after launch.
        </p>
      </div>

      <dl className="mt-16 grid gap-px overflow-hidden rounded-3xl border border-line bg-line md:grid-cols-2">
        {techStack.map((g) => (
          <div key={g.group} data-reveal className="bg-ink p-8 md:p-10">
            <dt className="font-mono text-xs uppercase tracking-[0.16em] text-muted">{g.group}</dt>
            <dd className="mt-5 flex flex-wrap gap-2">
              {g.items.map((item) => (
                <span key={item} className="rounded-full border border-line-strong px-4 py-2 text-sm transition-colors hover:border-volt hover:text-volt">
                  {item}
                </span>
              ))}
            </dd>
          </div>
        ))}
      </dl>
    </>
  )
}
