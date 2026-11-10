import { useEffect, useMemo, useRef } from 'react'
import * as THREE from 'three'
import { useFrame, useThree } from '@react-three/fiber'
import { gsap } from '../../lib/gsap'
import { useSceneStore } from '../../store/sceneStore'
import vertexShader from './particles.vert.glsl?raw'
import fragmentShader from './particles.frag.glsl?raw'

type Props = {
  count: number
  /** Freeze the swirl (reduced motion); section pulses still fade in/out gently. */
  still?: boolean
}

/** Particles fill a thick, flattened shell around the scene, denser toward the middle. */
function buildGeometry(count: number) {
  const positions = new Float32Array(count * 3)
  const seeds = new Float32Array(count * 3)
  for (let i = 0; i < count; i++) {
    const r = 2.2 + Math.pow(Math.random(), 1.6) * 22
    const theta = Math.random() * Math.PI * 2
    positions[i * 3] = Math.cos(theta) * r
    positions[i * 3 + 1] = (Math.random() - 0.35) * (3 + r * 0.45)
    positions[i * 3 + 2] = Math.sin(theta) * r - 2
    seeds[i * 3] = Math.random()
    seeds[i * 3 + 1] = Math.random()
    seeds[i * 3 + 2] = Math.random()
  }
  const geo = new THREE.BufferGeometry()
  geo.setAttribute('position', new THREE.BufferAttribute(positions, 3))
  geo.setAttribute('aSeed', new THREE.BufferAttribute(seeds, 3))
  // The shader moves points around; give culling a generous fixed bound.
  geo.boundingSphere = new THREE.Sphere(new THREE.Vector3(0, 0, -2), 40)
  return geo
}

export function ParticleField({ count, still = false }: Props) {
  const dpr = useThree((s) => s.viewport.dpr)
  const geometry = useMemo(() => buildGeometry(count), [count])
  const uniforms = useMemo(
    () => ({
      uSwirl: { value: 0 },
      uTime: { value: 0 },
      uPulse: { value: 0 },
      uSize: { value: 70 },
      uPixelRatio: { value: dpr },
      uColorA: { value: new THREE.Color('#9fb3c8') },
      uColorB: { value: new THREE.Color('#4cc2ff') },
    }),
    [],
  )
  useEffect(() => {
    uniforms.uPixelRatio.value = dpr
  }, [dpr, uniforms])
  useEffect(() => () => geometry.dispose(), [geometry])

  // Pulse on every section change.
  const activeSection = useSceneStore((s) => s.activeSection)
  const first = useRef(true)
  useEffect(() => {
    if (first.current || still) {
      first.current = false
      return
    }
    const tween = gsap.fromTo(uniforms.uPulse, { value: 1 }, { value: 0, duration: 1.8, ease: 'power3.out' })
    return () => {
      tween.kill()
    }
  }, [activeSection, uniforms, still])

  useFrame((_, rawDelta) => {
    // Clamp so resuming after a pause doesn't jump the swirl.
    const delta = Math.min(rawDelta, 0.1)
    uniforms.uTime.value += delta
    if (!still) uniforms.uSwirl.value += delta * 0.12 * (1 + uniforms.uPulse.value * 4)
  })

  return (
    <points geometry={geometry} frustumCulled={false}>
      <shaderMaterial
        vertexShader={vertexShader}
        fragmentShader={fragmentShader}
        uniforms={uniforms}
        transparent
        depthWrite={false}
        blending={THREE.AdditiveBlending}
      />
    </points>
  )
}
