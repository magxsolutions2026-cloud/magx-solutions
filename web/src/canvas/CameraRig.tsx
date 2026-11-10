import { useEffect, useRef } from 'react'
import * as THREE from 'three'
import { useFrame, useThree } from '@react-three/fiber'
import { easing } from 'maath'
import { gsap, ScrollTrigger, useGSAP } from '../lib/gsap'
import { useSceneStore, type SectionId } from '../store/sceneStore'
import { shotFor, type Shot } from './cameraPath'

const ORDER: SectionId[] = ['hero', 'services', 'work', 'testimonials', 'stack', 'contact']

/** Vertical FOV on landscape screens. */
const BASE_FOV = 42
/** Aspect at which BASE_FOV is "designed"; narrower screens widen the FOV to keep the same horizontal coverage. */
const DESIGN_ASPECT = 1.25
const MAX_FOV = 68

/** Camera follow smoothing (seconds to ~reach target). Higher = floatier glide. */
const FOLLOW = 0.45
/** How far the cursor nudges the camera, in world units. */
const PARALLAX = { x: 0.35, y: 0.18 }

type Proxy = { px: number; py: number; pz: number; tx: number; ty: number; tz: number; fx: number }
const toProxy = ({ position: [px, py, pz], target: [tx, ty, tz], frameX = 0 }: Shot): Proxy => ({ px, py, pz, tx, ty, tz, fx: frameX })

type Props = { portrait: boolean; reducedMotion: boolean }

/**
 * Scroll-driven camera.
 *
 * Each section gets a GSAP ScrollTrigger that scrubs a plain proxy object from the
 * previous section's shot to its own while the section scrolls from the bottom of
 * the viewport to the top. useFrame then damps the real camera toward the proxy and
 * adds a cursor parallax offset, so motion stays smooth even on jerky wheel input.
 *
 * With reduced motion there are no scrubbed glides: the camera cuts to the active
 * section's shot and parallax is off.
 */
export function CameraRig({ portrait, reducedMotion }: Props) {
  const camera = useThree((s) => s.camera) as THREE.PerspectiveCamera
  const size = useThree((s) => s.size)
  const activeSection = useSceneStore((s) => s.activeSection)

  const proxy = useRef<Proxy>(toProxy(shotFor('hero', portrait)))
  const lookAt = useRef(new THREE.Vector3(...shotFor('hero', portrait).target))
  const frameX = useRef(0)
  const snapped = useRef(false)

  // Responsive FOV: preserve horizontal framing on narrow/portrait screens.
  useEffect(() => {
    const aspect = size.width / size.height
    const fov =
      aspect >= DESIGN_ASPECT
        ? BASE_FOV
        : THREE.MathUtils.radToDeg(
            2 * Math.atan((Math.tan(THREE.MathUtils.degToRad(BASE_FOV / 2)) * DESIGN_ASPECT) / aspect),
          )
    camera.fov = Math.min(fov, MAX_FOV)
    camera.updateProjectionMatrix()
  }, [camera, size])

  useGSAP(
    () => {
      if (reducedMotion) return
      // Start from wherever the page currently is (e.g. reload mid-page).
      for (let i = 1; i < ORDER.length; i++) {
        const el = document.querySelector(`[data-section="${ORDER[i]}"]`)
        if (!el) continue
        gsap.fromTo(proxy.current, toProxy(shotFor(ORDER[i - 1], portrait)), {
          ...toProxy(shotFor(ORDER[i], portrait)),
          ease: 'power1.inOut',
          immediateRender: false,
          scrollTrigger: { trigger: el, start: 'top bottom', end: 'top top', scrub: true },
        })
      }
      ScrollTrigger.refresh()
      snapped.current = false
    },
    { dependencies: [portrait, reducedMotion], revertOnUpdate: true },
  )

  // Reduced motion: hard cut to the active section's shot.
  useEffect(() => {
    if (!reducedMotion) return
    Object.assign(proxy.current, toProxy(shotFor(activeSection, portrait)))
    snapped.current = false
  }, [reducedMotion, activeSection, portrait])

  useFrame((state, rawDelta) => {
    const delta = Math.min(rawDelta, 0.1)
    const p = proxy.current
    const px = reducedMotion ? 0 : state.pointer.x * PARALLAX.x
    const py = reducedMotion ? 0 : state.pointer.y * PARALLAX.y

    if (!snapped.current || reducedMotion) {
      // First frame (or reduced motion): jump straight there instead of gliding in from the origin.
      camera.position.set(p.px + px, p.py + py, p.pz)
      lookAt.current.set(p.tx, p.ty, p.tz)
      frameX.current = p.fx
      snapped.current = true
    } else {
      easing.damp3(camera.position, [p.px + px, p.py + py, p.pz], FOLLOW, delta)
      easing.damp3(lookAt.current, [p.tx, p.ty, p.tz], FOLLOW, delta)
      frameX.current = THREE.MathUtils.damp(frameX.current, p.fx, 1 / FOLLOW * 2.3, delta)
    }
    camera.lookAt(lookAt.current)

    // Lens shift: slide the image sideways (subject moves by +frameX of the width).
    const { width: w, height: h } = state.size
    const offset = Math.round(-frameX.current * w)
    if (camera.view?.offsetX !== offset || camera.view?.fullWidth !== w || camera.view?.fullHeight !== h) {
      camera.setViewOffset(w, h, offset, 0, w, h)
    }
  })

  return null
}
