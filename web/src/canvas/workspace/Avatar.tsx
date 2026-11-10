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
type ClipName = 'typing' | 'idle' | 'look' | 'wave' | 'point' | 'standUp'

/** Forward travel extracted from a clip's hips (see ROOT_MOTION in build-avatar.mjs). */
type RootMotion = { times: number[]; z: number[] }
type AnimData = { clips: THREE.AnimationClip[]; standUpTravel: RootMotion }

let animPromise: Promise<AnimData> | null = null
function loadAnims() {
  animPromise ??= fetch(ANIMS_URL)
    .then((r) => {
      if (!r.ok) throw new Error(`anims.json: HTTP ${r.status}`)
      return r.json() as Promise<{ clips: THREE.AnimationClipJSON[]; rootMotion: Record<string, RootMotion> }>
    })
    .then(({ clips, rootMotion }) => ({
      clips: clips.map((c) => THREE.AnimationClip.parse(c)),
      standUpTravel: rootMotion.standUp,
    }))
  return animPromise
}

/** Linear interpolation into a root-motion curve. */
function sampleTravel({ times, z }: RootMotion, t: number) {
  if (t <= times[0]) return z[0]
  for (let i = 1; i < times.length; i++) {
    if (t <= times[i]) return z[i - 1] + ((z[i] - z[i - 1]) * (t - times[i - 1])) / (times[i] - times[i - 1])
  }
  return z[z.length - 1]
}

/** One-shot gestures; everything else loops. */
const ONE_SHOT = new Set<ClipName>(['look', 'wave', 'point'])
const FADE = 0.35
/** Blend into the stand-up: long enough to absorb the seat-height difference between clips. */
const RISE_FADE = 0.6
/** Wait for the chair to swivel away from the desk before standing (Workspace AvatarRig). */
const RISE_DELAY_MS = 550
/** Idle "look around" fires at a random interval in this range (seconds). */
const LOOK_EVERY: [number, number] = [9, 16]

/**
 * Posture lifecycle. `rising`/`sitting` play the Mixamo stand-up clip forwards/backwards.
 * Published to the store (as seated / moving / standing) so the rig knows when the
 * chair may swivel back to the desk.
 */
type Phase = 'seated' | 'rising' | 'standing' | 'sitting'

type Props = ThreeElements['group'] & {
  /** Desired posture: `desk` sits and types; `stand` gets up and reacts to the contact form. */
  mode?: 'desk' | 'stand'
}

/**
 * The Avaturn avatar with a small animation state machine.
 *
 * Posture: switching `mode` plays the stand-up clip (reversed to sit down), with its
 * forward travel applied as root motion so the body ends up where the step lands.
 * Gestures: the store's `avatarState` is a request ('point' when a form field is hovered,
 * 'wave' on the submit button); it plays as a one-shot only while standing, then the
 * store is reset to 'idle'. The head also tracks the cursor, layered on top.
 */
export function Avatar({ mode = 'stand', ...props }: Props) {
  const group = useRef<THREE.Group>(null)
  const root = useRef<THREE.Group>(null)
  const { scene } = useGLTF(AVATAR_URL)
  const { clips, standUpTravel } = use(loadAnims())
  const { actions, mixer } = useAnimations(clips, group)

  const requested = useSceneStore((s) => s.avatarState)
  const setAvatarState = useSceneStore((s) => s.setAvatarState)
  const setAvatarPose = useSceneStore((s) => s.setAvatarPose)

  const phase = useRef<Phase>(mode === 'desk' ? 'seated' : 'standing')
  const current = useRef<ClipName>(mode === 'desk' ? 'typing' : 'idle')
  const head = useMemo(() => scene.getObjectByName('Head') ?? null, [scene])
  const travelEnd = standUpTravel.z[standUpTravel.z.length - 1]

  const setPhase = (p: Phase) => {
    phase.current = p
    setAvatarPose(p === 'seated' ? 'seated' : p === 'standing' ? 'standing' : 'moving')
  }
  const baseClip = (): ClipName => (phase.current === 'standing' ? 'idle' : 'typing')

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

  /** Cross-fades from the current clip to `next` (loops and gestures). */
  const play = (next: ClipName, fade = FADE) => {
    const to = actions[next]
    if (!to) return
    const from = actions[current.current]
    to.reset()
    to.timeScale = 1
    if (ONE_SHOT.has(next)) {
      to.setLoop(THREE.LoopOnce, 1)
      to.clampWhenFinished = true
    } else {
      to.setLoop(THREE.LoopRepeat, Infinity)
    }
    to.play()
    if (from && from !== to) to.crossFadeFrom(from, fade, true)
    current.current = next
  }

  /** Plays the stand-up clip forwards (rise) or backwards (sit), reversing in place if already mid-way. */
  const runStandUp = (direction: 1 | -1) => {
    const a = actions.standUp
    if (!a) return
    if (current.current === 'standUp') {
      a.paused = false
      a.timeScale = direction
    } else {
      const from = actions[current.current]
      a.reset()
      a.setLoop(THREE.LoopOnce, 1)
      a.clampWhenFinished = true
      a.timeScale = direction
      a.time = direction === 1 ? 0 : a.getClip().duration
      a.play()
      // No time-warp: warping would overwrite the (possibly negative) timeScale.
      if (from && from !== a) a.crossFadeFrom(from, direction === 1 ? RISE_FADE : FADE, false)
      current.current = 'standUp'
    }
    setPhase(direction === 1 ? 'rising' : 'sitting')
  }

  useEffect(() => () => void mixer.stopAllAction(), [mixer])

  // Start the initial loop for whatever posture we mounted in.
  useEffect(() => {
    play(current.current, 0)
    setPhase(phase.current)
  }, [actions])

  // Posture changes. Stale gesture requests from before the switch are dropped.
  useEffect(() => {
    setAvatarState('idle')
    const p = phase.current
    if (mode === 'stand') {
      if (p === 'sitting') runStandUp(1)
      if (p !== 'seated') return
      const t = window.setTimeout(() => runStandUp(1), RISE_DELAY_MS)
      return () => window.clearTimeout(t)
    }
    if (p === 'standing' || p === 'rising') runStandUp(-1)
  }, [mode])

  // Gesture requests from the UI, honoured only while standing. A repeat of what's
  // already playing is ignored so sliding across several fields doesn't restart it.
  useEffect(() => {
    if (phase.current !== 'standing') return
    const next: ClipName = requested === 'idle' ? 'idle' : (requested as Exclude<AvatarState, 'idle'>)
    if (next === current.current) return
    play(next)
  }, [requested])

  // Clip endings: finish a posture change, or return from a gesture to the base loop.
  useEffect(() => {
    const onFinished = (e: { action: THREE.AnimationAction }) => {
      if (e.action === actions.standUp && current.current === 'standUp') {
        if (phase.current === 'rising') {
          setPhase('standing')
          play('idle', 0.3)
        } else if (phase.current === 'sitting') {
          setPhase('seated')
          play('typing', 0.5)
        }
        setAvatarState('idle')
        return
      }
      if (e.action === actions[current.current] && ONE_SHOT.has(current.current)) {
        play(baseClip())
        setAvatarState('idle')
      }
    }
    mixer.addEventListener('finished', onFinished)
    return () => mixer.removeEventListener('finished', onFinished)
  }, [mixer, actions])

  // Occasional idle "look around" while standing, so the avatar never feels frozen.
  useEffect(() => {
    let timer = 0
    const schedule = () => {
      const delay = (LOOK_EVERY[0] + Math.random() * (LOOK_EVERY[1] - LOOK_EVERY[0])) * 1000
      timer = window.setTimeout(() => {
        if (phase.current === 'standing' && current.current === 'idle') setAvatarState('look')
        schedule()
      }, delay)
    }
    schedule()
    return () => window.clearTimeout(timer)
  }, [setAvatarState])

  const headOffset = useRef(new THREE.Euler())
  useFrame((state, delta) => {
    // Root motion: follow the stand-up's forward travel while it plays; hold it while standing.
    if (root.current) {
      const a = actions.standUp
      root.current.position.z =
        current.current === 'standUp' && a
          ? sampleTravel(standUpTravel, a.time)
          : phase.current === 'standing'
            ? travelEnd
            : 0
    }

    // Head follows the cursor a little. Runs after the mixer (registered later, same
    // priority), so it adds to the clip's pose instead of being overwritten by it.
    if (!head) return
    const engaged = current.current !== 'look' && current.current !== 'standUp' ? 1 : 0
    easing.dampE(headOffset.current, [-state.pointer.y * 0.18 * engaged, state.pointer.x * 0.35 * engaged, 0], 0.25, delta)
    head.rotation.x += headOffset.current.x
    head.rotation.y += headOffset.current.y
  })

  return (
    <group ref={group} {...props} dispose={null}>
      <group ref={root}>
        <primitive object={scene} />
      </group>
    </group>
  )
}

useGLTF.preload(AVATAR_URL)
