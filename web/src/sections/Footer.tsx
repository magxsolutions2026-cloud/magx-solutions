import { brand } from '../content/site'

export function Footer() {
  return (
    <footer className="relative border-t border-line bg-ink">
      <div className="mx-auto flex w-[min(1200px,calc(100%-2rem))] flex-col gap-4 py-10 text-sm text-muted md:flex-row md:items-center md:justify-between">
        <p>
          © {new Date().getFullYear()} {brand.name} · {brand.person}
        </p>
        <p>
          <a href={`mailto:${brand.email}`} className="text-paper underline decoration-line-strong underline-offset-4 hover:decoration-volt">
            {brand.email}
          </a>
        </p>
      </div>
    </footer>
  )
}
