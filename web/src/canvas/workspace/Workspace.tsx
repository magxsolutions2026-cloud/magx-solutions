import { useRef } from 'react'
import * as THREE from 'three'
import { useFrame, type ThreeElements } from '@react-three/fiber'
import { RoundedBox } from '@react-three/drei'
import { Avatar } from './Avatar'

/*
 * Procedural workspace: no model download, and it matches the site's palette.
 * Dimensions are tuned to the avatar's seated typing clip (hips at ~0.55 m):
 * seat top 0.47 m, desk top 0.74 m, keyboard ~0.38 m in front of the hips.
 */
const DESK_Y = 0.74
const DESK_Z = 0.42
const SEAT_Y = 0.47

const metal = new THREE.MeshStandardMaterial({ color: '#1b1e24', metalness: 0.8, roughness: 0.35 })
const deskTop = new THREE.MeshStandardMaterial({ color: '#0c0d10', metalness: 0.1, roughness: 0.7 })
const aluminium = new THREE.MeshStandardMaterial({ color: '#b9bec7', metalness: 0.9, roughness: 0.28 })

function Desk() {
  const legX = 0.66
  const legZ = 0.3
  return (
    <group position={[0, 0, DESK_Z]}>
      <RoundedBox args={[1.5, 0.035, 0.72]} radius={0.012} position={[0, DESK_Y, 0]} material={deskTop} castShadow receiveShadow />
      {[-legX, legX].map((x) =>
        [-legZ, legZ].map((z) => (
          <mesh key={`${x}${z}`} position={[x, DESK_Y / 2, z]} material={metal} castShadow>
            <boxGeometry args={[0.03, DESK_Y, 0.03]} />
          </mesh>
        )),
      )}
    </group>
  )
}

/** Laptop facing the avatar; the lid's back glows toward the camera. */
function Laptop() {
  const glow = useRef<THREE.MeshStandardMaterial>(null)
  useFrame(({ clock }) => {
    if (glow.current) glow.current.emissiveIntensity = 2.2 + Math.sin(clock.elapsedTime * 1.6) * 0.35
  })
  // The avatar faces +z, so the hinge sits on the laptop's +z edge and the lid
  // leans back toward +z (positive x-rotation tips +y toward +z).
  const LID_TILT = 0.32
  return (
    <group position={[0, DESK_Y + 0.018, DESK_Z - 0.08]}>
      <RoundedBox args={[0.34, 0.012, 0.23]} radius={0.005} position={[0, 0.006, 0]} material={aluminium} castShadow />
      <group position={[0, 0.012, 0.115]} rotation={[LID_TILT, 0, 0]}>
        <RoundedBox args={[0.34, 0.23, 0.008]} radius={0.004} position={[0, 0.115, 0]} material={aluminium} castShadow />
        {/* Screen faces the avatar (-z) */}
        <mesh position={[0, 0.115, -0.0045]} rotation={[0, Math.PI, 0]}>
          <planeGeometry args={[0.31, 0.2]} />
          <meshStandardMaterial color="#0a1a2a" emissive="#4cc2ff" emissiveIntensity={0.6} toneMapped={false} />
        </mesh>
        {/* Logo glow on the back of the lid faces the camera (+z) */}
        <mesh position={[0, 0.125, 0.0045]}>
          <circleGeometry args={[0.025, 32]} />
          <meshStandardMaterial ref={glow} color="#4cc2ff" emissive="#4cc2ff" toneMapped={false} />
        </mesh>
      </group>
    </group>
  )
}

function Chair() {
  return (
    <group position={[0, 0, -0.04]}>
      <RoundedBox args={[0.48, 0.06, 0.46]} radius={0.025} position={[0, SEAT_Y - 0.03, 0]} castShadow receiveShadow>
        <meshStandardMaterial color="#16181d" roughness={0.8} />
      </RoundedBox>
      <RoundedBox args={[0.46, 0.55, 0.05]} radius={0.025} position={[0, SEAT_Y + 0.33, -0.25]} rotation={[-0.1, 0, 0]} castShadow>
        <meshStandardMaterial color="#16181d" roughness={0.8} />
      </RoundedBox>
      <mesh position={[0, SEAT_Y / 2 - 0.03, 0]} material={metal}>
        <cylinderGeometry args={[0.025, 0.025, SEAT_Y - 0.08, 12]} />
      </mesh>
      <mesh position={[0, 0.03, 0]} rotation={[-Math.PI / 2, 0, 0]}>
        <circleGeometry args={[0.28, 5]} />
        <meshStandardMaterial color="#121418" roughness={0.9} />
      </mesh>
    </group>
  )
}

/** Desk, chair, laptop and the avatar typing. Lives at the world origin. */
export function Workspace(props: ThreeElements['group']) {
  return (
    <group {...props}>
      <Desk />
      <Chair />
      <Laptop />
      <Avatar mode="desk" />
      {/* Soft floor disc so the setup doesn't float in the particle void */}
      <mesh rotation={[-Math.PI / 2, 0, 0]} position={[0, 0.001, 0.15]} receiveShadow>
        <circleGeometry args={[1.25, 64]} />
        <meshStandardMaterial color="#08090b" roughness={1} />
      </mesh>
      <mesh rotation={[-Math.PI / 2, 0, 0]} position={[0, 0.002, 0.15]}>
        <ringGeometry args={[1.235, 1.25, 96]} />
        <meshBasicMaterial color="#4cc2ff" transparent opacity={0.35} toneMapped={false} />
      </mesh>
    </group>
  )
}
