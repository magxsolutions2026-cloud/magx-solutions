import { useEffect, useState } from 'react'
import { brand, nav } from '../content/site'
import { scrollToId } from '../lib/gsap'
import { useSceneStore } from '../store/sceneStore'
import { useUiStore } from '../store/uiStore'

export function Navbar() {
  const active = useSceneStore((s) => s.activeSection)
  const openBooking = useUiStore((s) => s.openBooking)
  const [menuOpen, setMenuOpen] = useState(false)

  useEffect(() => {
    if (!menuOpen) return
    const onKey = (e: KeyboardEvent) => e.key === 'Escape' && setMenuOpen(false)
    window.addEventListener('keydown', onKey)
    return () => window.removeEventListener('keydown', onKey)
  }, [menuOpen])

  const go = (id: string) => (e: React.MouseEvent) => {
    e.preventDefault()
    setMenuOpen(false)
    scrollToId(id)
  }

  return (
    <header className="fixed inset-x-0 top-0 z-40 pt-[env(safe-area-inset-top)]">
      <nav
        aria-label="Primary"
        className="glass mx-auto mt-4 w-[min(1200px,calc(100%-2rem))] rounded-[1.75rem] px-5 py-3"
      >
        <div className="flex items-center justify-between gap-4">
          <a href="#hero" onClick={go('hero')} className="font-display text-sm font-extrabold tracking-tight [font-stretch:115%]">
            {brand.name}
          </a>
          <ul className="hidden gap-1 md:flex">
            {nav.map((item) => (
              <li key={item.id}>
                <a
                  href={`#${item.id}`}
                  onClick={go(item.id)}
                  aria-current={active === item.id ? 'true' : undefined}
                  className="relative rounded-full px-3 py-1.5 text-sm text-muted transition-colors hover:text-paper aria-[current]:text-paper"
                >
                  {item.label}
                  <span
                    aria-hidden="true"
                    className={`absolute inset-x-3 -bottom-0.5 h-px origin-left bg-volt transition-transform duration-500 ${active === item.id ? 'scale-x-100' : 'scale-x-0'}`}
                  />
                </a>
              </li>
            ))}
          </ul>
          <div className="flex items-center gap-2">
            <button
              type="button"
              onClick={() => openBooking()}
              aria-haspopup="dialog"
              className="rounded-full bg-volt px-4 py-2 text-sm font-semibold text-ink"
            >
              Let&apos;s talk
            </button>
            <button
              type="button"
              className="rounded-full border border-line p-2 md:hidden"
              aria-expanded={menuOpen}
              aria-controls="mobile-menu"
              aria-label={menuOpen ? 'Close menu' : 'Open menu'}
              onClick={() => setMenuOpen((o) => !o)}
            >
              <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true">
                {menuOpen ? (
                  <path d="M4 4l10 10M14 4L4 14" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" />
                ) : (
                  <path d="M3 6h12M3 12h12" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" />
                )}
              </svg>
            </button>
          </div>
        </div>
        <ul id="mobile-menu" hidden={!menuOpen} className="mt-4 grid gap-1 border-t border-line pt-4 md:hidden">
          {nav.map((item) => (
            <li key={item.id}>
              <a
                href={`#${item.id}`}
                onClick={go(item.id)}
                aria-current={active === item.id ? 'true' : undefined}
                className="block rounded-xl px-3 py-3 font-display text-2xl font-bold tracking-tight [font-stretch:110%] aria-[current]:text-volt"
              >
                {item.label}
              </a>
            </li>
          ))}
        </ul>
      </nav>
    </header>
  )
}
