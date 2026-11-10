import { create } from 'zustand'

/** Sections the camera rig and particle field react to, in scroll order. */
export type SectionId = 'hero' | 'services' | 'work' | 'testimonials' | 'stack' | 'contact'

/** Avatar animation states; see canvas/workspace/Avatar.tsx. */
export type AvatarState = 'idle' | 'look' | 'wave' | 'point'

/** Where the avatar is in its sit/stand lifecycle; `moving` covers rising and sitting down. */
export type AvatarPose = 'seated' | 'moving' | 'standing'

type SceneState = {
  /** True once every Suspense-wrapped 3D asset has resolved. Drives the pre-loader exit. */
  sceneReady: boolean
  activeSection: SectionId
  avatarState: AvatarState
  avatarPose: AvatarPose
  hoveredProject: number | null
  setSceneReady: (ready: boolean) => void
  setActiveSection: (id: SectionId) => void
  setAvatarState: (state: AvatarState) => void
  setAvatarPose: (pose: AvatarPose) => void
  setHoveredProject: (index: number | null) => void
}

export const useSceneStore = create<SceneState>()((set) => ({
  sceneReady: false,
  activeSection: 'hero',
  avatarState: 'idle',
  avatarPose: 'seated',
  hoveredProject: null,
  setSceneReady: (sceneReady) => set({ sceneReady }),
  setActiveSection: (activeSection) => set({ activeSection }),
  setAvatarState: (avatarState) => set({ avatarState }),
  setAvatarPose: (avatarPose) => set({ avatarPose }),
  setHoveredProject: (hoveredProject) => set({ hoveredProject }),
}))
