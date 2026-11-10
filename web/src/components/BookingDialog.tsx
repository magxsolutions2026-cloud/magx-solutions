import { useEffect, useRef, useState, type FormEvent } from 'react'
import { ApiError, fetchSlots, submitAppointment, type Slot } from '../lib/api'
import { brand } from '../content/site'
import { useUiStore } from '../store/uiStore'

type Props = { serviceOptions: string[] }

type SlotState = { status: 'idle' | 'loading' | 'error'; slots: Slot[]; timezone: string }
type SubmitState = { status: 'idle' | 'sending' | 'done' | 'error'; message?: string; fieldErrors?: Record<string, string> }

/** Local YYYY-MM-DD (toISOString would shift the date across timezones). */
function today() {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

const field =
  'w-full rounded-xl border border-line bg-ink/60 px-4 py-3 text-paper placeholder:text-muted/60 transition-colors focus:border-volt focus:outline-none'
const label = 'mb-1.5 block text-sm text-muted'

/**
 * "Book a consultation" — posts to the existing appointment system
 * (appointments_api.php), which holds the slot and emails the admin for approval.
 */
export function BookingDialog({ serviceOptions }: Props) {
  const ref = useRef<HTMLDialogElement>(null)
  const open = useUiStore((s) => s.bookingOpen)
  const preset = useUiStore((s) => s.bookingService)
  const close = useUiStore((s) => s.closeBooking)

  const [date, setDate] = useState('')
  const [slotState, setSlotState] = useState<SlotState>({ status: 'idle', slots: [], timezone: '' })
  const [submit, setSubmit] = useState<SubmitState>({ status: 'idle' })

  useEffect(() => {
    const dlg = ref.current
    if (!dlg) return
    if (open && !dlg.open) {
      setSubmit({ status: 'idle' })
      dlg.showModal()
    } else if (!open && dlg.open) {
      dlg.close()
    }
  }, [open])

  useEffect(() => {
    if (!date) return
    let cancelled = false
    setSlotState((s) => ({ ...s, status: 'loading' }))
    fetchSlots(date)
      .then(({ slots, timezone }) => !cancelled && setSlotState({ status: 'idle', slots, timezone }))
      .catch(() => !cancelled && setSlotState({ status: 'error', slots: [], timezone: '' }))
    return () => {
      cancelled = true
    }
  }, [date])

  const onSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    const f = new FormData(e.currentTarget)
    const get = (k: string) => String(f.get(k) ?? '').trim()
    setSubmit({ status: 'sending' })
    try {
      const message = await submitAppointment({
        full_name: get('full_name'),
        email: get('email'),
        phone: get('phone'),
        preferred_date: get('preferred_date'),
        preferred_time: get('preferred_time'),
        service_type: get('service_type'),
        notes: get('notes'),
      })
      setSubmit({ status: 'done', message })
    } catch (err) {
      setSubmit({
        status: 'error',
        message: err instanceof ApiError ? err.message : 'Something went wrong. Please try again.',
        fieldErrors: err instanceof ApiError ? err.fieldErrors : undefined,
      })
    }
  }

  const err = submit.fieldErrors ?? {}
  const available = slotState.slots.filter((s) => s.is_available)

  return (
    <dialog
      ref={ref}
      onClose={close}
      onClick={(e) => e.target === ref.current && close()}
      aria-labelledby="booking-title"
      className="glass m-auto w-[min(560px,calc(100%-2rem))] rounded-3xl p-0 text-paper backdrop:bg-ink/70 backdrop:backdrop-blur-sm"
    >
      <div className="max-h-[85dvh] overflow-y-auto p-6 md:p-8">
        <div className="mb-6 flex items-start justify-between gap-4">
          <div>
            <p className="eyebrow mb-2">Discovery call · Online</p>
            <h2 id="booking-title" className="font-display text-3xl font-extrabold tracking-tight [font-stretch:110%]">
              Book a consultation
            </h2>
          </div>
          <button type="button" onClick={close} aria-label="Close" className="rounded-full border border-line p-2 text-muted hover:text-paper">
            <svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
              <path d="M3 3l10 10M13 3L3 13" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" />
            </svg>
          </button>
        </div>

        {submit.status === 'done' ? (
          <div role="status" className="py-6">
            <p className="font-display text-2xl font-bold">You’re booked in.</p>
            <p className="mt-3 text-muted">{submit.message}</p>
            <p className="mt-2 text-muted">You’ll get a confirmation email with the meeting link once it’s approved.</p>
            <button type="button" onClick={close} className="mt-8 rounded-full border border-line-strong px-6 py-3 hover:border-paper">
              Close
            </button>
          </div>
        ) : (
          <form onSubmit={onSubmit} noValidate className="grid gap-4">
            <div className="grid gap-4 md:grid-cols-2">
              <div>
                <label htmlFor="bk-name" className={label}>Full name</label>
                <input id="bk-name" name="full_name" required autoComplete="name" className={field} aria-invalid={!!err.full_name} />
                {err.full_name && <p className="mt-1 text-sm text-red-400">{err.full_name}</p>}
              </div>
              <div>
                <label htmlFor="bk-email" className={label}>Email</label>
                <input id="bk-email" name="email" type="email" required autoComplete="email" className={field} aria-invalid={!!err.email} />
                {err.email && <p className="mt-1 text-sm text-red-400">{err.email}</p>}
              </div>
            </div>
            <div className="grid gap-4 md:grid-cols-2">
              <div>
                <label htmlFor="bk-date" className={label}>Date</label>
                <input
                  id="bk-date"
                  name="preferred_date"
                  type="date"
                  required
                  min={today()}
                  value={date}
                  onChange={(e) => setDate(e.target.value)}
                  className={`${field} [color-scheme:dark]`}
                  aria-invalid={!!err.preferred_date}
                />
                {err.preferred_date && <p className="mt-1 text-sm text-red-400">{err.preferred_date}</p>}
              </div>
              <div>
                <label htmlFor="bk-time" className={label}>
                  Time {slotState.timezone && <span className="text-muted/70">({slotState.timezone})</span>}
                </label>
                <select
                  id="bk-time"
                  name="preferred_time"
                  required
                  disabled={!date || slotState.status === 'loading'}
                  className={`${field} disabled:opacity-50`}
                  aria-invalid={!!err.preferred_time}
                  defaultValue=""
                  key={date}
                >
                  <option value="" disabled>
                    {!date
                      ? 'Pick a date first'
                      : slotState.status === 'loading'
                        ? 'Loading times…'
                        : slotState.status === 'error'
                          ? 'Times unavailable'
                          : available.length
                            ? 'Choose a time'
                            : 'No times left this day'}
                  </option>
                  {available.map((s) => (
                    <option key={s.value} value={s.value}>{s.label}</option>
                  ))}
                </select>
                {slotState.status === 'error' && (
                  <p className="mt-1 text-sm text-red-400">
                    Couldn’t load available times. Try another date, or email <span className="text-paper">{brand.email}</span>.
                  </p>
                )}
                {err.preferred_time && <p className="mt-1 text-sm text-red-400">{err.preferred_time}</p>}
              </div>
            </div>
            <div className="grid gap-4 md:grid-cols-2">
              <div>
                <label htmlFor="bk-service" className={label}>What do you need?</label>
                <select id="bk-service" name="service_type" className={field} defaultValue={preset} key={preset}>
                  <option value="">Not sure yet</option>
                  {serviceOptions.map((s) => (
                    <option key={s} value={s}>{s}</option>
                  ))}
                </select>
              </div>
              <div>
                <label htmlFor="bk-phone" className={label}>Phone <span className="text-muted/70">(optional)</span></label>
                <input id="bk-phone" name="phone" type="tel" autoComplete="tel" className={field} />
              </div>
            </div>
            <div>
              <label htmlFor="bk-notes" className={label}>Anything I should know? <span className="text-muted/70">(optional)</span></label>
              <textarea id="bk-notes" name="notes" rows={3} className={`${field} resize-none`} />
            </div>

            {submit.status === 'error' && (
              <p role="alert" className="text-sm text-red-400">
                {submit.message} You can also email <span className="text-paper">{brand.email}</span>.
              </p>
            )}

            <button
              type="submit"
              disabled={submit.status === 'sending'}
              className="mt-2 rounded-full bg-volt px-7 py-4 font-semibold text-ink transition-opacity disabled:opacity-60"
            >
              {submit.status === 'sending' ? 'Sending…' : 'Request this time'}
            </button>
            <p className="text-center text-xs text-muted">I review every request personally and confirm by email.</p>
          </form>
        )}
      </div>
    </dialog>
  )
}
