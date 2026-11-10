import { use, useEffect, useMemo, useRef } from 'react'
import * as THREE from 'three'
import { useFrame, type ThreeElements } from '@react-three/fiber'
import { useAnimations, useGLTF } from '@react-three/drei'
import { easing } from 'maath'
import { useSceneStore, type AvatarState } from '../../store/sceneStore'

const BASE = import.meta.env.BASE_URL
const AVATAR_URL = `${BASE}models/avatar/avatar.glb`
const ANIMS_URL = `${BASE}models/avatar/anims.json`

/** Clip names produced by scripts/build-avatar.mjs. */
type ClipName = 'typing' | 'idle' | 'look' | 'wave' | 'point'

let clipsPromise: Promise<THREE.AnimationClip[]> | null = null
function loadClips() {
  clipsPromise ??= fetch(ANIMS_URL)
    .then((r) => {
      if (!r.ok) throw new Error(`anims.json: HTTP ${r.status}`)
      return r.json() as Promise<{ clips: THREE.AnimationClipJSON[] }>
    })
    .then(({ clips }) => clips.map((c) => THREE.AnimationClip.parse(c)))
  return clipsPromise
}

/** One-shot gestures; everything else loops. */
const ONE_SHOT = new Set<ClipName>(['look', 'wave', 'point'])
const FADE = 0.35
/** Idle "look around" fires at a random interval in this range (seconds). */
const LOOK_EVERY: [number, number] = [9, 16]

type Props = ThreeElements['group'] & {
  /** `desk`: seated typing loop. `stand`: breathing idle that reacts to the contact form. */
  mode?: 'desk' | 'stand'
}

/**
 * The Avaturn avatar with a small animation state machine.
 *
 * The store's `avatarState` is the request ('point' when a form field is hovered,
 * 'wave' on the submit button); this component plays it as a one-shot, cross-fades
 * back to the base loop and resets the store to 'idle'. The head also tracks the
 * cursor slightly, layered on top of whatever clip is playing.
 */
export function Avatar({ mode = 'stand', ...props }: Props) {
  const group = useRef<THREE.Group>(null)
  const { scene } = useGLTF(AVATAR_URL)
  const clips = use(loadClips())
  const { actions, mixer } = useAnimations(clips, group)

  const requested = useSceneStore((s) => s.avatarState)
  const setAvatarState = useSceneStore((s) => s.setAvatarState)

  const baseClip: ClipName = mode === 'desk' ? 'typing' : 'idle'
  const current = useRef<ClipName>(baseClip)
  const head = useMemo(() => scene.getObjectByName('Head') ?? null, [scene])

  useEffect(() => {
    scene.traverse((o) => {
      if ((o as THREE.Mesh).isMesh) {
        o.castShadow = true
        o.receiveShadow = true
        // Skinned meshes animate outside their bind-pose bounds; don't let them pop out.
        o.frustumCulled = false
      }
    })
  }, [scene])

  /** Cross-fades from the current clip to `next`. */
  const play = (next: ClipName) => {
    const to = actions[next]
    if (!to) return
    const from = actions[current.current]
    to.reset()
    if (ONE_SHOT.has(next)) {
      to.setLoop(THREE.LoopOnce, 1)
      to.clampWhenFinished = true
    } else {
      to.setLoop(THREE.LoopRepeat, Infinity)
    }
    to.play()
    if (from && from !== to) to.crossFadeFrom(from, FADE, true)
    current.current = next
  }

  // Start the base loop.
  useEffect(() => {
    play(baseClip)
    return () => {
      mixer.stopAllAction()
    }
  }, [actions, baseClip])

  // Gesture requests from the UI. Ignore a repeat of what's already playing so
  // sliding the cursor across several fields doesn't restart the clip each time.
  useEffect(() => {
    const next: ClipName = requested === 'idle' ? baseClip : (requested as Exclude<AvatarState, 'idle'>)
    if (next === current.current) return
    // Standing gestures don't blend well from a seated pose; desk mode only reacts by looking.
    if (mode === 'desk' && next !== 'look' && next !== baseClip) return
    play(next)
  }, [requested, baseClip, mode])

  // One-shot finished -> back to the base loop.
  useEffect(() => {
    const onFinished = (e: { action: THREE.AnimationAction }) => {
      if (e.action === actions[current.current] && ONE_SHOT.has(current.current)) {
        play(baseClip)
        setAvatarState('idle')
      }
    }
    mixer.addEventListener('finished', onFinished)
    return () => mixer.removeEventListener('finished', onFinished)
  }, [mixer, actions, baseClip, setAvatarState])

  // Occasional idle "look around" so the avatar never feels frozen.
  useEffect(() => {
    let timer = 0
    const schedule = () => {
      const delay = (LOOK_EVERY[0] + Math.random() * (LOOK_EVERY[1] - LOOK_EVERY[0])) * 1000
      timer = window.setTimeout(() => {
        if (current.current === baseClip && mode === 'stand') setAvatarState('look')
        schedule()
      }, delay)
    }
    schedule()
    return () => window.clearTimeout(timer)
  }, [baseClip, mode, setAvatarState])

  // Head follows the cursor a little. Runs after the mixer (registered later, same priority),
  // so it adds to the clip's pose instead of being overwritten by it.
  const headOffset = useRef(new THREE.Euler())
  useFrame((state, delta) => {
    if (!head) return
    const busy = current.current === 'look'
    const target = busy ? 0 : 1
    easing.dampE(
      headOffset.current,
      [-state.pointer.y * 0.18 * target, state.pointer.x * 0.35 * target, 0],
      0.25,
      delta,
    )
    head.rotation.x += headOffset.current.x
    head.rotation.y += headOffset.current.y
  })

  return (
    <group ref={group} {...props} dispose={null}>
      <primitive object={scene} />
    </group>
  )
}

useGLTF.preload(AVATAR_URL)
