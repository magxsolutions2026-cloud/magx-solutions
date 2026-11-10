/**
 * Avatar asset pipeline: turns the sources in assets-src/avatar/ into web-ready files.
 *
 *   assets-src/avatar/avatar.glb   (Avaturn export, T-pose)  -> public/models/avatar/avatar.glb
 *   assets-src/avatar/*.fbx        (Mixamo, "Without Skin")  -> public/models/avatar/anims.json
 *
 * Mixamo clips are retargeted by name: "mixamorig:Hips" -> "Hips". Only bone rotations
 * plus the hips height are kept, so the avatar's own proportions (scaled head/arms) survive.
 *
 * Run: npm run build:avatar   (re-run whenever the sources change)
 */
import { readFile, writeFile, mkdir, readdir } from 'node:fs/promises'
import { execFileSync } from 'node:child_process'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import * as THREE from 'three'
import { FBXLoader } from 'three/examples/jsm/loaders/FBXLoader.js'

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const srcDir = path.join(root, 'assets-src/avatar')
const outDir = path.join(root, 'public/models/avatar')

/**
 * Gesture clips whose legs should be replaced by the standing idle stance. Mixamo's
 * "Pointing" is performed in a crouch; we keep its arm/torso/head and stand it up.
 */
const UPPER_BODY_ONLY = new Set(['point'])
const LOWER_BODY = new Set(['Hips', 'LeftUpLeg', 'LeftLeg', 'LeftFoot', 'LeftToeBase', 'RightUpLeg', 'RightLeg', 'RightFoot', 'RightToeBase'])

/**
 * Seconds of a source clip to keep. Mixamo's "Pointing" starts and ends with the arms in
 * its crouch (hands on knees), which reads as a stiff bow once the legs are stood up;
 * the arm is raised from ~0.5 s to ~2.2 s. The avatar's cross-fades cover the edges.
 */
const CLIP_WINDOWS = { point: [0.35, 2.45] }

/** Clip file -> state name used by canvas/workspace/Avatar.tsx. */
const CLIPS = {
  // idle first: its stance is reused by UPPER_BODY_ONLY clips
  'breathing-idle.fbx': 'idle',
  'typing.fbx': 'typing',
  'looking-around.fbx': 'look',
  'waving.fbx': 'wave',
  'pointing.fbx': 'point',
}

/** Reads bone names and the hips rest height from the avatar GLB's JSON chunk. */
async function readAvatarRig(file) {
  const buf = await readFile(file)
  const jsonLen = buf.readUInt32LE(12)
  const gltf = JSON.parse(buf.subarray(20, 20 + jsonLen).toString('utf8'))
  const joints = gltf.skins[0].joints.map((i) => gltf.nodes[i].name)
  const hips = gltf.nodes.find((n) => n.name === 'Hips')
  return { bones: new Set(joints), hipsY: hips.translation[1], hipsZ: hips.translation[2] }
}

/** Drops keyframes that linear interpolation reproduces within `tol`. */
function decimate(track, tol) {
  const { times, values } = track
  const size = values.length / times.length
  if (times.length <= 2) return track
  const keep = [0]
  for (let i = 1; i < times.length - 1; i++) {
    const a = keep[keep.length - 1]
    const t = (times[i] - times[a]) / (times[i + 1] - times[a])
    let err = 0
    for (let k = 0; k < size; k++) {
      const lerp = values[a * size + k] + (values[(i + 1) * size + k] - values[a * size + k]) * t
      err = Math.max(err, Math.abs(lerp - values[i * size + k]))
    }
    if (err > tol) keep.push(i)
  }
  keep.push(times.length - 1)
  const nt = new Float32Array(keep.length)
  const nv = new Float32Array(keep.length * size)
  keep.forEach((src, dst) => {
    nt[dst] = times[src]
    for (let k = 0; k < size; k++) nv[dst * size + k] = values[src * size + k]
  })
  return new track.constructor(track.name, nt, nv)
}

const round = (n) => Math.round(n * 1e4) / 1e4

/** Idle's lower-body stance, captured while converting idle (see UPPER_BODY_ONLY). */
let standPose = null

async function convertClip(file, name, rig) {
  const buf = await readFile(file)
  const group = new FBXLoader().parse(buf.buffer.slice(buf.byteOffset, buf.byteOffset + buf.byteLength), '')
  const clip = group.animations[0]
  if (!clip) throw new Error(`${file}: no animation found`)

  // FBXLoader sanitizes "mixamorig:Hips" to "mixamorigHips".
  const strip = (n) => n.replace(/^mixamorig\d*:?/, '')
  let srcHipsRest = null
  group.traverse((o) => {
    if (o.isBone && strip(o.name) === 'Hips' && !srcHipsRest) srcHipsRest = o.position.clone()
  })
  if (!srcHipsRest) throw new Error(`${file}: no Hips bone found`)
  const hipsScale = rig.hipsY / srcHipsRest.y

  const tracks = []
  for (const track of clip.tracks) {
    const [rawBone, prop] = track.name.split('.')
    const bone = strip(rawBone)
    if (!rig.bones.has(bone)) continue
    if (prop === 'quaternion') {
      const t = track.clone()
      t.name = `${bone}.quaternion`
      tracks.push(decimate(t, 0.0015))
    } else if (prop === 'position' && bone === 'Hips') {
      // Keep the hips offset from rest (e.g. sitting lowers and pushes the hips back),
      // rescaled from Mixamo centimeters to the avatar's own hips height.
      const t = track.clone()
      t.name = 'Hips.position'
      const v = t.values
      for (let i = 0; i < v.length; i += 3) {
        v[i] = (v[i] - srcHipsRest.x) * hipsScale
        v[i + 1] *= hipsScale
        v[i + 2] = (v[i + 2] - srcHipsRest.z) * hipsScale + rig.hipsZ
      }
      tracks.push(decimate(t, 0.002))
    }
  }

  // Optionally keep only the useful window of a clip (see CLIP_WINDOWS).
  let duration = clip.duration
  let finalTracks = tracks
  const win = CLIP_WINDOWS[name]
  if (win) {
    const [start, end] = win
    finalTracks = finalTracks.map((t) => t.clone().trim(start, end).shift(-start))
    duration = end - start
  }

  if (name === 'idle') {
    // Remember the idle's first-frame hips + legs pose for UPPER_BODY_ONLY clips.
    standPose = tracks
      .filter((t) => LOWER_BODY.has(t.name.split('.')[0]))
      .map((t) => ({ name: t.name, Ctor: t.constructor, value: Array.from(t.values.slice(0, t.values.length / t.times.length)) }))
  }
  if (UPPER_BODY_ONLY.has(name)) {
    if (!standPose) throw new Error(`${name}: idle clip must be converted first`)
    const isLower = (t) => LOWER_BODY.has(t.name.split('.')[0])
    const stance = standPose.map(({ name: n, Ctor, value }) => new Ctor(n, [0, duration], [...value, ...value]))
    finalTracks = [...finalTracks.filter((t) => !isLower(t)), ...stance]
  }

  const out = new THREE.AnimationClip(name, duration, finalTracks)
  const json = THREE.AnimationClip.toJSON(out)
  for (const t of json.tracks) {
    t.times = t.times.map(round)
    t.values = t.values.map(round)
  }
  const keys = json.tracks.reduce((s, t) => s + t.times.length, 0)
  console.log(`  ${name.padEnd(7)} ${duration.toFixed(2)}s  ${json.tracks.length} tracks  ${keys} keys  hipsScale=${hipsScale.toFixed(4)}`)
  return json
}

async function main() {
  await mkdir(outDir, { recursive: true })
  const avatarSrc = path.join(srcDir, 'avatar.glb')
  const rig = await readAvatarRig(avatarSrc)
  console.log(`avatar: ${rig.bones.size} bones, hips at ${rig.hipsY.toFixed(3)}m`)

  const present = new Set(await readdir(srcDir))
  const clips = []
  for (const [file, name] of Object.entries(CLIPS)) {
    if (!present.has(file)) {
      console.warn(`  (skipping missing ${file})`)
      continue
    }
    clips.push(await convertClip(path.join(srcDir, file), name, rig))
  }
  const animsOut = path.join(outDir, 'anims.json')
  await writeFile(animsOut, JSON.stringify({ clips }))
  console.log(`wrote ${path.relative(root, animsOut)}`)

  // Mesh: WebP textures capped at 1024px + meshopt geometry compression. No simplification:
  // decimating a skinned face/hands looks bad up close at the desk.
  const avatarOut = path.join(outDir, 'avatar.glb')
  const cli = path.join(root, 'node_modules/.bin/gltf-transform')
  execFileSync(
    cli,
    ['optimize', avatarSrc, avatarOut, '--compress', 'meshopt', '--texture-compress', 'webp', '--texture-size', '1024', '--simplify', 'false', '--join', 'false', '--instance', 'false', '--palette', 'false'],
    { stdio: 'inherit' },
  )
  console.log(`wrote ${path.relative(root, avatarOut)}`)
}

main().catch((err) => {
  console.error(err)
  process.exit(1)
})
