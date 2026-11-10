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
