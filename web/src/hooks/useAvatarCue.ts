import { useCallback, useRef } from 'react'
import { useSceneStore, type AvatarState } from '../store/sceneStore'

/** Minimum gap between UI-triggered gestures, so hovering across fields doesn't spam the avatar. */
const COOLDOWN_MS = 2500

/**
 * Lets DOM elements ask the avatar for a gesture ('point' at the form, 'wave' on submit).
 * Returns `cue(state, force?)`; `force` bypasses the cooldown (e.g. on a successful send).
 */
export function useAvatarCue() {
  const last = useRef(0)
  return useCallback((state: Exclude<AvatarState, 'idle'>, force = false) => {
    const now = performance.now()
    if (!force && now - last.current < COOLDOWN_MS) return
    last.current = now
    useSceneStore.getState().setAvatarState(state)
  }, [])
}
