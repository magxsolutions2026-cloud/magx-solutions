import { useState, type FormEvent } from 'react'
import { ApiError, submitInquiry } from '../lib/api'
import { brand } from '../content/site'
import { useAvatarCue } from '../hooks/useAvatarCue'

type Status = { state: 'idle' | 'sending' | 'done' | 'error'; message?: string; fieldErrors?: Record<string, string> }

const field =
  'w-full rounded-xl border border-line bg-ink/50 px-4 py-3 text-paper placeholder:text-muted/60 transition-[border-color,background-color] duration-300 focus:border-volt focus:bg-ink/70 focus:outline-none'
const label = 'mb-1.5 block text-sm text-muted'

/**
 * "Let's work together" form. Posts to inquiries_api.php (saved to the DB + email alert).
 * Hovering or focusing fields cues the avatar to point at the form; the submit button
 * and a successful send make it wave.
 */
export function ContactForm() {
  const [status, setStatus] = useState<Status>({ state: 'idle' })
  const cue = useAvatarCue()
  const point = () => cue('point')
  const wave = () => cue('wave')

  const onSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    const form = e.currentTarget
    const f = new FormData(form)
    const get = (k: string) => String(f.get(k) ?? '').trim()
    setStatus({ state: 'sending' })
    try {
      const message = await submitInquiry({
        full_name: get('full_name'),
        email: get('email'),
        company: get('company'),
        budget: get('budget'),
        message: get('message'),
        website: get('website'),
      })
      setStatus({ state: 'done', message })
      cue('wave', true)
      form.reset()
    } catch (err) {
      setStatus({
        state: 'error',
        message: err instanceof ApiError ? err.message : 'Something went wrong. Please try again.',
        fieldErrors: err instanceof ApiError ? err.fieldErrors : undefined,
      })
    }
  }

  const err = status.fieldErrors ?? {}
  const fieldCue = { onPointerEnter: point, onFocus: point }

  if (status.state === 'done') {
    return (
      <div role="status" className="glass rounded-3xl p-8 md:p-10">
        <p className="font-display text-3xl font-extrabold tracking-tight [font-stretch:110%]">Message received.</p>
        <p className="mt-3 text-muted">{status.message}</p>
        <button
          type="button"
          onClick={() => setStatus({ state: 'idle' })}
          className="mt-8 rounded-full border border-line-strong px-6 py-3 transition-colors hover:border-paper"
        >
          Send another
        </button>
      </div>
    )
  }

  return (
    <form onSubmit={onSubmit} noValidate className="glass grid gap-4 rounded-3xl p-6 md:p-8" aria-describedby="contact-privacy">
      <div className="grid gap-4 sm:grid-cols-2">
        <div>
          <label htmlFor="ct-name" className={label}>Your name</label>
          <input id="ct-name" name="full_name" required autoComplete="name" className={field} aria-invalid={!!err.full_name} {...fieldCue} />
          {err.full_name && <p className="mt-1 text-sm text-red-400">{err.full_name}</p>}
        </div>
        <div>
          <label htmlFor="ct-email" className={label}>Email</label>
          <input id="ct-email" name="email" type="email" required autoComplete="email" className={field} aria-invalid={!!err.email} {...fieldCue} />
          {err.email && <p className="mt-1 text-sm text-red-400">{err.email}</p>}
        </div>
      </div>
      <div className="grid gap-4 sm:grid-cols-2">
        <div>
          <label htmlFor="ct-company" className={label}>Company <span className="text-muted/70">(optional)</span></label>
          <input id="ct-company" name="company" autoComplete="organization" className={field} {...fieldCue} />
        </div>
        <div>
          <label htmlFor="ct-budget" className={label}>Budget <span className="text-muted/70">(optional)</span></label>
          <input id="ct-budget" name="budget" placeholder="A rough range is fine" className={field} {...fieldCue} />
        </div>
      </div>
      <div>
        <label htmlFor="ct-message" className={label}>What are you looking to build?</label>
        <textarea
          id="ct-message"
          name="message"
          required
          rows={4}
          placeholder="The process you want to fix, who uses it, and any deadline."
          className={`${field} resize-none`}
          aria-invalid={!!err.message}
          {...fieldCue}
        />
        {err.message && <p className="mt-1 text-sm text-red-400">{err.message}</p>}
      </div>

      {/* Honeypot: hidden from people and assistive tech; bots that fill it are silently dropped. */}
      <div aria-hidden="true" className="absolute -left-[9999px] h-0 w-0 overflow-hidden">
        <label htmlFor="ct-website">Website</label>
        <input id="ct-website" name="website" tabIndex={-1} autoComplete="off" />
      </div>

      {status.state === 'error' && (
        <p role="alert" className="text-sm text-red-400">
          {status.message} You can also email <span className="text-paper">{brand.email}</span>.
        </p>
      )}

      <button
        type="submit"
        disabled={status.state === 'sending'}
        onPointerEnter={wave}
        onFocus={wave}
        className="group mt-1 inline-flex items-center justify-center gap-3 rounded-full bg-volt px-7 py-4 font-semibold text-ink shadow-[0_10px_40px_-10px_rgb(76_194_255/0.7)] transition-[box-shadow,opacity] hover:shadow-[0_14px_60px_-8px_rgb(76_194_255/0.9)] disabled:opacity-60"
      >
        {status.state === 'sending' ? 'Sending…' : 'Send message'}
        <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true" className="transition-transform duration-300 group-hover:translate-x-1">
          <path d="M3 9h11M10 4.5L14.5 9 10 13.5" stroke="currentColor" strokeWidth="1.8" fill="none" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
      </button>
      <p id="contact-privacy" className="text-center text-xs text-muted">Your details are only used to reply to you.</p>
    </form>
  )
}
