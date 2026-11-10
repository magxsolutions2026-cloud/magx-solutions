/**
 * Site copy that lives with the design. Services and portfolio items normally come
 * from the admin panel via public_content_api.php; the lists here are fallbacks.
 *
 * Every claim below comes from the legacy homepage's credentials and case studies.
 * Keep it that way: no invented numbers, clients or quotes.
 */
import type { SectionId } from '../store/sceneStore'

export const nav: { id: SectionId; label: string }[] = [
  { id: 'services', label: 'Services' },
  { id: 'work', label: 'Work' },
  { id: 'testimonials', label: 'Proof' },
  { id: 'stack', label: 'Stack' },
  { id: 'contact', label: 'Contact' },
]

export const brand = {
  name: 'MAGX Solutions',
  person: 'Mark Angelo Guillermo',
  role: 'Web developer',
  email: 'magxsolutions2026@gmail.com',
}

export const hero = {
  eyebrow: `${brand.person} — ${brand.role}`,
  headline: ['Paperwork', 'to platforms.'],
  sub: 'I design and build secure web systems that replace spreadsheets and manual workflows — so your team spends less time on records and more time on the business.',
  primaryCta: 'Book a consultation',
  secondaryCta: 'See the work',
}

/** Short proof points shown under the hero. Sourced from the TMC case study and credentials. */
export const proofStrip = [
  { value: '3', label: 'gas station branches running on one system I built' },
  { value: '2025', label: 'Certificate of Implementation — TMC Multi-Industry' },
  { value: 'NC II', label: 'Computer Systems Servicing, TESDA-certified' },
]

export type ServiceCopy = { title: string; description: string }

/** Fallback when the admin panel has no active services (or the API is unreachable, e.g. local dev). */
export const fallbackServices: ServiceCopy[] = [
  {
    title: 'Business systems',
    description:
      'Role-based record and operations platforms that replace paper forms and scattered spreadsheets with one validated, searchable source of truth.',
  },
  {
    title: 'Dashboards & reporting',
    description:
      'Live monitoring dashboards and reports so owners see what is happening across branches without waiting for end-of-month summaries.',
  },
  {
    title: 'Websites that convert',
    description:
      'Fast, polished websites with clear structure and persuasive storytelling — built to turn visitors into enquiries.',
  },
  {
    title: 'Workflow automation',
    description:
      'Integrations and automations that remove repetitive manual steps: notifications, approvals, scheduling and data hand-offs.',
  },
]

/** How an engagement runs; from the TMC case study's delivery process. */
export const processSteps = [
  { title: 'Discover', description: 'Workshops to map how work really flows today and where it breaks.' },
  { title: 'Design', description: 'Data model and screens shaped around your roles and approvals.' },
  { title: 'Build & pilot', description: 'A working pilot in real hands early, hardened with QA.' },
  { title: 'Roll out', description: 'Phased deployment and support while your team adopts it.' },
]

/** Verifiable credentials and results. */
export const credentials = [
  {
    kicker: 'Deployed result',
    title: 'TMC Integrated Record Management System',
    body: 'Live across three gasoline station branches, also supporting commercial leasing and fast-food operations.',
  },
  {
    kicker: 'Award',
    title: 'Certificate of Implementation, 2025',
    body: 'Recognised for delivering a practical, scalable business system for TMC Multi-Industry.',
  },
  {
    kicker: 'Recognition, 2026',
    title: 'Outstanding DRRM Innovator',
    body: 'Plaque of Recognition from the City of Ilagan CDRRMO for creative disaster-risk-reduction solutions and community preparedness.',
  },
  {
    kicker: 'Certification',
    title: 'National Certificate II — Computer Systems Servicing',
    body: 'Validated competencies in systems servicing and technical support (2025).',
  },
  {
    kicker: 'Education',
    title: 'BS Information Technology',
    body: 'Saint Ferdinand College, 2021–2025. Professional reference available on request.',
  },
]

/**
 * Client testimonials. Add real, attributable quotes here; the Proof section shows a
 * testimonials row automatically once this list is non-empty.
 */
export const testimonials: { quote: string; name: string; role: string }[] = []

export const techStack: { group: string; items: string[] }[] = [
  { group: 'Frontend', items: ['HTML & CSS', 'JavaScript', 'TypeScript', 'React', 'Tailwind CSS', 'Bootstrap'] },
  { group: 'Motion & 3D', items: ['Three.js', 'React Three Fiber', 'GSAP'] },
  { group: 'Backend & data', items: ['PHP', 'MySQL', 'PostgreSQL / Supabase', 'REST APIs', 'SMTP / PHPMailer'] },
  { group: 'Delivery', items: ['Git', 'Vercel', 'Role-based auth', 'Graphic design'] },
]

export type Project = {
  title: string
  category: string
  year: string
  summary: string
  problem?: string
  solution?: string
  results: string[]
  tags: string[]
  /** Root-absolute image URL. */
  cover: string
  /** `contain` letterboxes small/odd-ratio images on the card instead of cropping them. */
  fit?: 'cover' | 'contain'
  link?: string
}

/** Case studies from the legacy homepage. Used when the admin panel has no portfolio items. */
export const fallbackProjects: Project[] = [
  {
    title: 'TMC Integrated Record Management System',
    category: 'Flagship build',
    year: '2025',
    summary: 'One secure, searchable platform for a multi-industry business: gasoline stations, leasing and food services.',
    problem: 'Fragmented manual records caused reporting delays, low visibility and inconsistent data.',
    solution: 'A centralized role-based platform with structured forms, validation and monitoring dashboards.',
    results: ['Live across three gasoline station branches', 'Supports commercial leasing and fast-food operations', 'Certificate of Implementation, 2025'],
    tags: ['PHP', 'MySQL', 'Role-based auth', 'Reporting'],
    cover: `${import.meta.env.BASE_URL}work/tmc.webp`,
  },
  {
    title: 'MAGX conversion-focused website',
    category: 'Growth build',
    year: '2026',
    summary: 'A rebuild of the MAGX site to earn trust faster and turn more visitors into enquiries.',
    problem: 'The earlier site lacked hierarchy and persuasive structure, limiting confidence from new prospects.',
    solution: 'Premium visual language, strategic section order, stronger copy hierarchy and micro-interactions.',
    results: ['Clear project storytelling and hierarchy', 'Engagement modules for comments and sharing', 'More credible client-facing presentation'],
    tags: ['UX', 'Interaction design', 'Front-end'],
    cover: `${import.meta.env.BASE_URL}work/magx-site.webp`,
  },
  {
    title: 'Firstgate campaign asset system',
    category: 'Design build',
    year: '2025',
    summary: 'A reusable brand system that standardized campaign visuals across social and print.',
    problem: 'Inconsistent assets across channels lowered campaign quality and slowed production.',
    solution: 'Modular social and print templates with brand-safe composition rules.',
    results: ['Multi-format materials for active campaigns', 'Consistent branding across social and print', 'Faster turnaround on promotions'],
    tags: ['Brand system', 'Templates', 'Graphic design'],
    cover: `${import.meta.env.BASE_URL}work/firstgate.webp`,
    fit: 'contain',
  },
]
