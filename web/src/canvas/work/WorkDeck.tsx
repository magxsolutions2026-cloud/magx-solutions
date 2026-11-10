import { useEffect, useMemo, useRef } from 'react'
import * as THREE from 'three'
import { useFrame, useThree, type ThreeEvent } from '@react-three/fiber'
import { useTexture } from '@react-three/drei'
import type { Project } from '../../content/site'
import { useSceneStore } from '../../store/sceneStore'
import { workDeck } from './workDeckState'
import vertexShader from './card.vert.glsl?raw'
import fragmentShader from './card.frag.glsl?raw'

/*
 * Layout: cards sit on an arc whose center of curvature is toward the camera, so the
 * deck gently wraps around the viewer. The focused card is at DECK_CENTER; scroll
 * progress slides every card along the arc. CameraRig's `work` shot looks here from
 * above the desk, so the avatar stays out of frame.
 */
const DECK_CENTER = new THREE.Vector3(0, 2.6, -6)
const RADIUS = 8
/** Angle between neighbouring cards on the arc (radians). */
const SPACING = 0.6
/** How far (world units) each step away from focus pushes a card back. */
const RECEDE = 2.4
const CARD_W = 4.4
const CARD_H = 2.75

/** True when a DOM event landed on a real control; the deck then ignores it. */
function isOverControl(e: ThreeEvent<PointerEvent | MouseEvent>) {
  const t = e.nativeEvent.target as Element | null
  return !!t?.closest?.('a, button, input, select, textarea, label, dialog, nav')
}

type CardProps = {
  index: number
  texture: THREE.Texture
  fit: 'cover' | 'contain'
  /** Shared, damped "which card is centered" value (fractional index). */
  focus: React.RefObject<number>
  /** Deck-wide fade (0 outside the Work section). */
  fade: React.RefObject<number>
}

function Card({ index, texture, fit, focus, fade }: CardProps) {
  const mesh = useRef<THREE.Mesh>(null)
  const hovered = useRef(false)
  const mouseTarget = useRef(new THREE.Vector2(0.5, 0.5))
  const geometry = useMemo(() => new THREE.PlaneGeometry(CARD_W, CARD_H, 48, 30), [])
  const image = texture.image as { width: number; height: number }

  const uniforms = useMemo(
    () => ({
      uMap: { value: texture },
      uPlaneAspect: { value: CARD_W / CARD_H },
      uImageAspect: { value: image.width / image.height },
      uContain: { value: fit === 'contain' ? 1 : 0 },
      uBg: { value: new THREE.Color('#111318') },
      uAccent: { value: new THREE.Color('#4cc2ff') },
      uHover: { value: 0 },
      uDim: { value: 0 },
      uOpacity: { value: 1 },
      uTime: { value: 0 },
      uBend: { value: 0.12 },
      uMouse: { value: new THREE.Vector2(0.5, 0.5) },
    }),
    [texture, image, fit],
  )
  useEffect(() => () => geometry.dispose(), [geometry])

  useFrame((_, rawDelta) => {
    const m = mesh.current
    if (!m) return
    const delta = Math.min(rawDelta, 0.1)
    const k = index - focus.current
    const ak = Math.min(Math.abs(k), 2)
    const a = k * SPACING

    // Position on the arc, facing its center of curvature. Side cards also sink back
    // (RECEDE) so they read as depth behind the focused card; hovered cards lift forward.
    const lift = uniforms.uHover.value * 0.25
    m.position.set(
      DECK_CENTER.x + Math.sin(a) * RADIUS,
      DECK_CENTER.y,
      DECK_CENTER.z + RADIUS - Math.cos(a) * RADIUS - ak * RECEDE + lift,
    )
    m.rotation.y = -a
    m.scale.setScalar(1 - 0.12 * ak)

    const u = uniforms
    u.uTime.value += delta
    u.uHover.value = THREE.MathUtils.damp(u.uHover.value, hovered.current ? 1 : 0, 6, delta)
    u.uMouse.value.lerp(mouseTarget.current, 1 - Math.exp(-10 * delta))
    u.uDim.value = Math.min(ak, 1)
    u.uBend.value = 0.1 + 0.08 * ak
    u.uOpacity.value = fade.current * THREE.MathUtils.clamp(2.6 - Math.abs(k), 0, 1)
    m.visible = u.uOpacity.value > 0.01
  })

  const onMove = (e: ThreeEvent<PointerEvent>) => {
    if (isOverControl(e) || fade.current < 0.5) return out()
    e.stopPropagation()
    if (e.uv) mouseTarget.current.copy(e.uv)
    if (!hovered.current) {
      hovered.current = true
      document.body.style.cursor = 'pointer'
    }
  }
  const out = () => {
    if (!hovered.current) return
    hovered.current = false
    document.body.style.cursor = ''
  }
  const onClick = (e: ThreeEvent<MouseEvent>) => {
    if (isOverControl(e) || fade.current < 0.5) return
    e.stopPropagation()
    workDeck.select?.(index)
  }

  return (
    <mesh ref={mesh} geometry={geometry} onPointerMove={onMove} onPointerOut={out} onClick={onClick}>
      <shaderMaterial
        vertexShader={vertexShader}
        fragmentShader={fragmentShader}
        uniforms={uniforms}
        transparent
        depthWrite={false}
        side={THREE.DoubleSide}
      />
    </mesh>
  )
}

/** The interactive project deck shown during the Work section. */
export function WorkDeck({ projects }: { projects: Project[] }) {
  const gl = useThree((s) => s.gl)
  const textures = useTexture(projects.map((p) => p.cover))
  useEffect(() => {
    const aniso = gl.capabilities.getMaxAnisotropy()
    for (const t of textures) {
      t.colorSpace = THREE.SRGBColorSpace
      t.anisotropy = Math.min(8, aniso)
      t.needsUpdate = true
    }
  }, [textures, gl])

  const focus = useRef(0)
  const fade = useRef(0)
  useFrame((_, rawDelta) => {
    const delta = Math.min(rawDelta, 0.1)
    const target = workDeck.progress * Math.max(0, projects.length - 1)
    focus.current = THREE.MathUtils.damp(focus.current, target, 5, delta)
    const inWork = useSceneStore.getState().activeSection === 'work'
    fade.current = THREE.MathUtils.damp(fade.current, inWork ? 1 : 0, 3, delta)
  })

  // Reset the cursor if the deck unmounts mid-hover.
  useEffect(() => () => void (document.body.style.cursor = ''), [])

  return (
    <group>
      {projects.map((p, i) => (
        <Card key={p.cover + i} index={i} texture={textures[i]} fit={p.fit ?? 'cover'} focus={focus} fade={fade} />
      ))}
    </group>
  )
}

