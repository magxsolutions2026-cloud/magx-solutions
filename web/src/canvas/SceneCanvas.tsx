import { Suspense, useEffect } from 'react'
import { Canvas } from '@react-three/fiber'
import { AdaptiveDpr } from '@react-three/drei'
import { useSceneStore } from '../store/sceneStore'
import { usePreloader } from '../hooks/usePreloader'
import { useCanvasVisibility } from '../hooks/useCanvasVisibility'
import { usePortrait, useReducedMotion } from '../hooks/useViewport'
import { CameraRig } from './CameraRig'
import { ParticleField } from './particles/ParticleField'
import { Workspace } from './workspace/Workspace'
import { WorkDeck } from './work/WorkDeck'
import type { Project } from '../content/site'

/** Mounted inside the Suspense boundary, so its effect runs only after every asset resolved. */
function SceneReady() {
  const setSceneReady = useSceneStore((s) => s.setSceneReady)
  useEffect(() => setSceneReady(true), [setSceneReady])
  return null
}

function Lights() {
  return (
    <>
      <hemisphereLight args={['#cfd8e6', '#07080a', 0.7]} />
      {/* Key light, front-right, casts the only shadow in the scene */}
      <directionalLight
        position={[3, 5, 4]}
        intensity={2.2}
        castShadow
        shadow-mapSize={[1024, 1024]}
        shadow-camera-left={-2}
        shadow-camera-right={2}
        shadow-camera-top={2.5}
        shadow-camera-bottom={-1}
        shadow-bias={-0.0005}
      />
      {/* Portrait light from the contact camera's side: the standing avatar faces -x,
          away from the key light, and would otherwise be a silhouette. */}
      <directionalLight position={[-4, 3.2, 2.4]} intensity={1.7} color="#fff3e6" />
      {/* Cool rim light from behind for silhouette separation against the dark */}
      <directionalLight position={[-3, 2.5, -4]} intensity={1.1} color="#4cc2ff" />
      {/* Screen bounce onto the avatar's face */}
      <pointLight position={[0, 0.92, 0.6]} intensity={0.25} distance={0.9} decay={2} color="#4cc2ff" />
    </>
  )
}

/**
 * The single, persistent WebGL canvas fixed behind the HTML layer.
 * Loaded lazily from App so three.js stays out of the first-paint bundle.
 */
export default function SceneCanvas({ projects }: { projects: Project[] }) {
  usePreloader()
  const active = useCanvasVisibility()
  const portrait = usePortrait()
  const reducedMotion = useReducedMotion()
  const particleCount = reducedMotion ? 2500 : portrait ? 3500 : 8000

  return (
    <div className="pointer-events-none fixed inset-0 -z-10" aria-hidden="true" data-frameloop={active ? 'always' : 'never'}>
      <Canvas
        // The canvas sits behind the page (pointer-events: none); feed it pointer events
        // from the document root so the work deck can still react to hover and clicks.
        eventSource={document.getElementById('root')!}
        eventPrefix="client"
        frameloop={active ? 'always' : 'never'}
        shadows
        dpr={[1, portrait ? 2 : 1.75]}
        gl={{ antialias: true, powerPreference: 'high-performance', alpha: false }}
        camera={{ position: [2.6, 1.55, 3.6], fov: 42, near: 0.1, far: 120 }}
      >
        <color attach="background" args={['#07080a']} />
        <fog attach="fog" args={['#07080a', 10, 38]} />
        <AdaptiveDpr pixelated={false} />
        <Lights />
        <CameraRig portrait={portrait} reducedMotion={reducedMotion} />
        <ParticleField count={particleCount} still={reducedMotion} />
        <Suspense fallback={null}>
          <Workspace />
          <SceneReady />
        </Suspense>
        {/* Separate boundary: project covers stream in after the pre-loader, never blocking it. */}
        <Suspense fallback={null}>
          <WorkDeck projects={projects} />
        </Suspense>
      </Canvas>
    </div>
  )
}
