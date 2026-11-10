import { brand, nav } from '../content/site'
import { scrollToId } from '../lib/gsap'

export function Navbar() {
  return (
    <header className="fixed inset-x-0 top-0 z-40 pt-[env(safe-area-inset-top)]">
      <nav
        aria-label="Primary"
        className="glass mx-auto mt-4 flex w-[min(1200px,calc(100%-2rem))] items-center justify-between rounded-full px-5 py-3"
      >
        <a href="#hero" onClick={(e) => (e.preventDefault(), scrollToId('hero'))} className="font-display text-sm font-extrabold tracking-tight [font-stretch:115%]">
          {brand.name}
        </a>
        <ul className="hidden gap-7 md:flex">
          {nav.map((item) => (
            <li key={item.id}>
              <a
                href={`#${item.id}`}
                onClick={(e) => (e.preventDefault(), scrollToId(item.id))}
                className="text-sm text-muted transition-colors hover:text-paper"
              >
                {item.label}
              </a>
            </li>
          ))}
        </ul>
        <a
          href="#contact"
          onClick={(e) => (e.preventDefault(), scrollToId('contact'))}
          className="rounded-full bg-volt px-4 py-2 text-sm font-semibold text-ink"
        >
          Let&apos;s talk
        </a>
      </nav>
    </header>
  )
}
