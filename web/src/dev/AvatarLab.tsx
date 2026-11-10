import { Suspense, useEffect } from 'react'
import { createRoot } from 'react-dom/client'
import { Canvas } from '@react-three/fiber'
import { OrbitControls } from '@react-three/drei'
import { Avatar } from '../canvas/workspace/Avatar'
import { useSceneStore, type AvatarState } from '../store/sceneStore'

/**
 * Dev harness for checking retargeted clips on the avatar.
 *   ?mode=stand|desk   base loop (idle vs typing)
 *   ?clip=look|wave|point   gesture to trigger after load
 */
const params = new URLSearchParams(location.search)
const mode = params.get('mode') === 'desk' ? 'desk' : 'stand'
const clip = params.get('clip') as AvatarState | null

function Trigger() {
  useEffect(() => {
    if (clip) useSceneStore.getState().setAvatarState(clip)
  }, [])
  return null
}

createRoot(document.getElementById('root')!).render(
  <Canvas camera={{ position: [0, 1.3, 3.2], fov: 40 }}>
    <color attach="background" args={['#1a1d24']} />
    <hemisphereLight args={['#ffffff', '#444455', 1.6]} />
    <directionalLight position={[2, 4, 3]} intensity={2} />
    <gridHelper args={[4, 8, '#555', '#333']} />
    <Suspense fallback={null}>
      <Avatar mode={mode} />
      <Trigger />
    </Suspense>
    <OrbitControls target={[0, 0.95, 0]} />
  </Canvas>,
)
