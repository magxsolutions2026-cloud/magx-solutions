import { useRef, type ComponentPropsWithoutRef, type ReactNode } from 'react'
import { m, useMotionValue, useSpring, useTransform, useReducedMotion } from 'motion/react'

type Variant = 'primary' | 'ghost'

type Props = Omit<ComponentPropsWithoutRef<'button'>, 'onDrag' | 'onDragStart' | 'onDragEnd' | 'onAnimationStart'> & {
  variant?: Variant
  /** How far (px) the button follows the cursor at the edge of its pull radius. */
  strength?: number
  children: ReactNode
}

const SPRING = { stiffness: 220, damping: 16, mass: 0.6 }

const styles: Record<Variant, string> = {
  primary:
    'bg-volt text-ink shadow-[0_0_0_1px_rgb(76_194_255/0.5),0_10px_40px_-10px_rgb(76_194_255/0.7)] hover:shadow-[0_0_0_1px_rgb(76_194_255/0.8),0_14px_60px_-8px_rgb(76_194_255/0.9)]',
  ghost: 'border border-line-strong text-paper hover:border-paper',
}

/**
 * CTA that is pulled toward the cursor with spring physics; the label moves a little
 * further than the pill for a sense of depth. Inert on touch devices and with reduced motion.
 */
export function MagneticButton({ variant = 'primary', strength = 18, className = '', children, ...rest }: Props) {
  const ref = useRef<HTMLButtonElement>(null)
  const reduced = useReducedMotion()
  const x = useMotionValue(0)
  const y = useMotionValue(0)
  const sx = useSpring(x, SPRING)
  const sy = useSpring(y, SPRING)
  const lx = useTransform(sx, (v) => v * 0.45)
  const ly = useTransform(sy, (v) => v * 0.45)

  const onPointerMove = (e: React.PointerEvent) => {
    if (reduced || e.pointerType !== 'mouse' || !ref.current) return
    const r = ref.current.getBoundingClientRect()
    x.set(((e.clientX - (r.left + r.width / 2)) / (r.width / 2)) * strength)
    y.set(((e.clientY - (r.top + r.height / 2)) / (r.height / 2)) * strength * 0.6)
  }
  const reset = () => {
    x.set(0)
    y.set(0)
  }

  // The padded wrapper widens the pull radius so the button reaches for the cursor before it arrives.
  return (
    <div className="-m-5 inline-block p-5" onPointerMove={onPointerMove} onPointerLeave={reset}>
      <m.button
        ref={ref}
        type="button"
        style={{ x: sx, y: sy }}
        whileTap={reduced ? undefined : { scale: 0.96 }}
        className={`group relative inline-flex items-center gap-3 rounded-full px-7 py-4 text-base font-semibold transition-[box-shadow,border-color] duration-300 ${styles[variant]} ${className}`}
        {...rest}
      >
        <m.span style={{ x: lx, y: ly }} className="inline-flex items-center gap-3">
          {children}
        </m.span>
      </m.button>
    </div>
  )
}
