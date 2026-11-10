import { useRef } from 'react'
import * as THREE from 'three'
import { useFrame, type ThreeElements } from '@react-three/fiber'
import { RoundedBox } from '@react-three/drei'
import { easing } from 'maath'
import { useSceneStore } from '../../store/sceneStore'
import { Avatar } from './Avatar'

/*
 * Procedural workspace: no model download, and it matches the site's palette.
 * Dimensions are measured from the avatar's seated typing clip (avatar-lab ?probe):
 * hips at ~0.55 m, wrists ~0.48 m out, fingertips ~0.58–0.65 m out at ~0.78 m high.
 * So: seat top 0.47 m, desk top 0.74 m, laptop keyboard spanning ~0.45–0.68 m out.
 */
const DESK_Y = 0.74
const DESK_Z = 0.42
const SEAT_Y = 0.47
/** Laptop base: front edge under the wrists, keys under the fingertips. */
const LAPTOP = { z: 0.565, w: 0.37, d: 0.24 }

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
  const { w, d } = LAPTOP
  return (
    <group position={[0, DESK_Y + 0.018, LAPTOP.z]}>
      <RoundedBox args={[w, 0.012, d]} radius={0.005} position={[0, 0.006, 0]} material={aluminium} castShadow />
      {/* Keyboard deck (hinge side) and trackpad (wrist side) */}
      <mesh position={[0, 0.0125, d * 0.14]} rotation={[-Math.PI / 2, 0, 0]}>
        <planeGeometry args={[w * 0.88, d * 0.5]} />
        <meshStandardMaterial color="#0e1013" roughness={0.6} />
      </mesh>
      <mesh position={[0, 0.0125, -d * 0.3]} rotation={[-Math.PI / 2, 0, 0]}>
        <planeGeometry args={[w * 0.32, d * 0.26]} />
        <meshStandardMaterial color="#a4a9b2" metalness={0.6} roughness={0.35} />
      </mesh>
      <group position={[0, 0.012, d / 2]} rotation={[LID_TILT, 0, 0]}>
        <RoundedBox args={[w, d, 0.008]} radius={0.004} position={[0, d / 2, 0]} material={aluminium} castShadow />
        {/* Screen faces the avatar (-z) */}
        <mesh position={[0, d / 2, -0.0045]} rotation={[0, Math.PI, 0]}>
          <planeGeometry args={[w * 0.91, d * 0.86]} />
          <meshStandardMaterial color="#0a1a2a" emissive="#4cc2ff" emissiveIntensity={0.6} toneMapped={false} />
        </mesh>
        {/* Logo glow on the back of the lid faces the camera (+z) */}
        <mesh position={[0, d / 2 + 0.01, 0.0045]}>
          <circleGeometry args={[0.025, 32]} />
          <meshStandardMaterial ref={glow} color="#4cc2ff" emissive="#4cc2ff" toneMapped={false} />
        </mesh>
      </group>
    </group>
  )
}

/** The typing clip's right hand periodically reaches out to the side; give it a mouse there. */
function Mouse() {
  return (
    <group position={[-0.3, DESK_Y + 0.018, 0.63]}>
      <mesh position={[0, 0.001, 0]} rotation={[-Math.PI / 2, 0, 0]} receiveShadow>
        <planeGeometry args={[0.18, 0.17]} />
        <meshStandardMaterial color="#15171c" roughness={0.95} />
      </mesh>
      <mesh position={[0, 0.014, 0]} scale={[0.032, 0.016, 0.055]} castShadow>
        <sphereGeometry args={[1, 24, 16]} />
        <meshStandardMaterial color="#d7dbe1" metalness={0.2} roughness={0.4} />
      </mesh>
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

/** Where the avatar is while seated (chair) and while standing to greet visitors at Contact. */
type Spot = { position: [number, number, number]; yaw: number }
const SEATED: Spot = { position: [0, 0, 0], yaw: 0 }
/** Beside the desk's left end, turned a little toward the contact form (screen right). */
const STANDING: Spot = { position: [-1.05, 0, 0.55], yaw: 0.18 }

/**
 * Seats the avatar at the desk, and stands it up beside the desk while the Contact
 * section is active. The body glides between spots while Avatar cross-fades
 * typing -> idle, which reads as getting up to say hello.
 */
function AvatarRig() {
  const ref = useRef<THREE.Group>(null)
  const standing = useSceneStore((s) => s.activeSection === 'contact')
  useFrame((_, rawDelta) => {
    const g = ref.current
    if (!g) return
    const delta = Math.min(rawDelta, 0.1)
    const spot = standing ? STANDING : SEATED
    easing.damp3(g.position, spot.position, 0.45, delta)
    easing.dampAngle(g.rotation, 'y', spot.yaw, 0.45, delta)
  })
  return (
    <group ref={ref}>
      <Avatar mode={standing ? 'stand' : 'desk'} />
    </group>
  )
}

/** Desk, chair, laptop and the avatar. Lives at the world origin. */
export function Workspace(props: ThreeElements['group']) {
  return (
    <group {...props}>
      <Desk />
      <Chair />
      <Laptop />
      <Mouse />
      <AvatarRig />
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
