import { Suspense, useEffect } from 'react'
import { Canvas } from '@react-three/fiber'
import { AdaptiveDpr } from '@react-three/drei'
import { useSceneStore } from '../store/sceneStore'
import { usePreloader } from '../hooks/usePreloader'

/** Mounted inside the Suspense boundary, so its effect runs only after every asset resolved. */
function SceneReady() {
  const setSceneReady = useSceneStore((s) => s.setSceneReady)
  useEffect(() => setSceneReady(true), [setSceneReady])
  return null
}

/**
 * The single, persistent WebGL canvas fixed behind the HTML layer.
 * Loaded lazily from App so three.js stays out of the first-paint bundle.
 */
export default function SceneCanvas() {
  usePreloader()

  return (
    <div className="pointer-events-none fixed inset-0 -z-10" aria-hidden="true">
      <Canvas
        dpr={[1, 1.75]}
        gl={{ antialias: true, powerPreference: 'high-performance', alpha: false }}
        camera={{ position: [0, 0, 8], fov: 42, near: 0.1, far: 200 }}
      >
        <color attach="background" args={['#07080a']} />
        <fog attach="fog" args={['#07080a', 12, 40]} />
        <AdaptiveDpr pixelated={false} />
        <Suspense fallback={null}>
          <ambientLight intensity={0.25} />
          <directionalLight position={[4, 6, 5]} intensity={1.2} />
          {/* Phase 2: <CameraRig/>, <ParallaxGroup/>, <ParticleField/>, workspace + work deck */}
          <SceneReady />
        </Suspense>
      </Canvas>
    </div>
  )
}
