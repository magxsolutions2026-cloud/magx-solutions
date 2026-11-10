import { Component, type ReactNode } from 'react'
import { useSceneStore } from '../store/sceneStore'

/**
 * If WebGL is unavailable or a 3D chunk/asset fails, drop the canvas and let the
 * HTML site stand on its own instead of leaving the pre-loader up forever.
 */
export class SceneErrorBoundary extends Component<{ children: ReactNode }, { failed: boolean }> {
  state = { failed: false }

  static getDerivedStateFromError() {
    return { failed: true }
  }

  componentDidCatch(error: unknown) {
    console.warn('[scene] 3D layer disabled:', error)
    useSceneStore.getState().setSceneReady(true)
    document.getElementById('preloader')?.remove()
  }

  render() {
    return this.state.failed ? null : this.props.children
  }
}
