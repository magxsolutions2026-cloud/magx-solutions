import { create } from 'zustand'

/** Sections the camera rig and particle field react to, in scroll order. */
export type SectionId = 'hero' | 'services' | 'work' | 'testimonials' | 'stack' | 'contact'

/** Avatar animation states; see canvas/workspace/Avatar.tsx. */
export type AvatarState = 'idle' | 'look' | 'wave' | 'point'

type SceneState = {
  /** True once every Suspense-wrapped 3D asset has resolved. Drives the pre-loader exit. */
  sceneReady: boolean
  activeSection: SectionId
  avatarState: AvatarState
  hoveredProject: number | null
  setSceneReady: (ready: boolean) => void
  setActiveSection: (id: SectionId) => void
  setAvatarState: (state: AvatarState) => void
  setHoveredProject: (index: number | null) => void
}

export const useSceneStore = create<SceneState>()((set) => ({
  sceneReady: false,
  activeSection: 'hero',
  avatarState: 'idle',
  hoveredProject: null,
  setSceneReady: (sceneReady) => set({ sceneReady }),
  setActiveSection: (activeSection) => set({ activeSection }),
  setAvatarState: (avatarState) => set({ avatarState }),
  setHoveredProject: (hoveredProject) => set({ hoveredProject }),
}))
