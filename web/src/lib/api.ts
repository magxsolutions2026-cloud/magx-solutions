/**
 * Thin client for the PHP backend in the repo root. All endpoints are same-origin
 * POST + form-encoded, matching the existing *_api.php conventions.
 */

export type Service = {
  id: number
  title?: string
  description?: string
  [key: string]: unknown
}

export type PortfolioItem = {
  id: number
  title?: string
  description?: string
  image?: string
  [key: string]: unknown
}

export type PublicContent = {
  services: Service[]
  portfolio: PortfolioItem[]
  footer: Record<string, unknown> | null
}

type ApiResponse<T> = {
  success: boolean
  message?: string
  data?: T
  errors?: Record<string, string>
  [key: string]: unknown
}

export class ApiError extends Error {
  constructor(
    message: string,
    readonly fieldErrors: Record<string, string> = {},
  ) {
    super(message)
  }
}

async function post<T>(endpoint: string, fields: Record<string, string>): Promise<ApiResponse<T>> {
  const res = await fetch(`/${endpoint}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(fields),
    credentials: 'same-origin',
  })
  let json: ApiResponse<T>
  try {
    json = await res.json()
  } catch {
    throw new ApiError('The server returned an unexpected response.')
  }
  if (!res.ok || !json.success) {
    throw new ApiError(json.message ?? 'Request failed.', json.errors)
  }
  return json
}

/** Uploaded media paths come back relative (uploads/...); make them root-absolute. */
export function mediaUrl(path?: string) {
  if (!path) return undefined
  return /^(https?:)?\/\//.test(path) || path.startsWith('/') ? path : `/${path}`
}

export async function fetchPublicContent(): Promise<PublicContent> {
  const json = await post<PublicContent>('public_content_api.php', { action: 'ALL' })
  return json.data ?? { services: [], portfolio: [], footer: null }
}

export type InquiryInput = {
  full_name: string
  email: string
  company: string
  budget: string
  message: string
  /** Honeypot; must stay empty. */
  website: string
}

export async function submitInquiry(input: InquiryInput): Promise<string> {
  const json = await post<never>('inquiries_api.php', { action: 'SUBMIT', ...input })
  return json.message ?? 'Message sent.'
}

export type Slot = {
  value: string
  label: string
  is_available: boolean
  remaining: number
}

/** Bookable slots for a date (YYYY-MM-DD) from the existing appointment system. */
export async function fetchSlots(date: string): Promise<{ slots: Slot[]; timezone: string }> {
  // appointments_api.php returns slots at the top level, not under `data`.
  const json = await post<never>('appointments_api.php', { action: 'AVAILABLE_SLOTS', date })
  return { slots: (json.slots as Slot[]) ?? [], timezone: String(json.timezone ?? '') }
}

export type AppointmentInput = {
  full_name: string
  email: string
  phone: string
  preferred_date: string
  preferred_time: string
  service_type: string
  notes: string
}

/** Creates a pending appointment; the admin approves it from the existing admin panel. */
export async function submitAppointment(input: AppointmentInput): Promise<string> {
  const json = await post<never>('appointments_api.php', { action: 'SUBMIT', ...input })
  return json.message ?? 'Request sent.'
}
