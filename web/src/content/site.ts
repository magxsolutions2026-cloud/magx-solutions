/**
 * Static site copy. Services and portfolio items come from the admin panel via
 * public_content_api.php; everything here is copy that lives with the design.
 */
import type { SectionId } from '../store/sceneStore'

export const nav: { id: SectionId; label: string }[] = [
  { id: 'services', label: 'Services' },
  { id: 'work', label: 'Work' },
  { id: 'testimonials', label: 'Clients' },
  { id: 'stack', label: 'Stack' },
  { id: 'contact', label: 'Contact' },
]

export const brand = {
  name: 'MAGX Solutions',
  email: 'magxsolutions2026@gmail.com',
}

/**
 * PLACEHOLDERS: replace with real, attributable client quotes before launch.
 * `placeholder: true` renders a visible "sample" tag so these can't ship by accident.
 */
export const testimonials = [
  { quote: 'Client quote goes here.', name: 'Client Name', role: 'Role, Company', placeholder: true },
  { quote: 'Client quote goes here.', name: 'Client Name', role: 'Role, Company', placeholder: true },
  { quote: 'Client quote goes here.', name: 'Client Name', role: 'Role, Company', placeholder: true },
]

export const techStack = {
  Frontend: ['React', 'TypeScript', 'Three.js / R3F', 'GSAP', 'Tailwind CSS'],
  Backend: ['PHP', 'PostgreSQL / Supabase', 'REST APIs'],
  Delivery: ['Vercel', 'Git', 'Figma'],
} as const
