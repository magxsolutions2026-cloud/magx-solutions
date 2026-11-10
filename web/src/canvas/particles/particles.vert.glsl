// Swirling particle field. All motion happens here on the GPU; the CPU only
// advances a few uniforms per frame, so the cost is flat regardless of count.

uniform float uSwirl;      // integrated swirl angle, advanced on the CPU (speeds up on pulses)
uniform float uTime;
uniform float uPulse;      // 0..1, spikes when the active section changes, then decays
uniform float uSize;
uniform float uPixelRatio;

attribute vec3 aSeed;      // x: color mix, y: angular speed factor, z: size/brightness

varying float vAlpha;
varying float vMix;

void main() {
  vec3 p = position;

  // Orbit around the vertical axis; inner particles turn faster (a loose vortex).
  float r = length(p.xz);
  float angle = uSwirl * (0.35 + aSeed.y) / (0.6 + r * 0.12);
  float c = cos(angle);
  float s = sin(angle);
  p.xz = mat2(c, -s, s, c) * p.xz;

  // Slow vertical drift so the field breathes instead of spinning rigidly.
  p.y += sin(uTime * 0.25 + aSeed.z * 6.2831 + r * 0.4) * 0.35;

  // Section-change pulse: a soft outward bloom.
  p += normalize(p + vec3(1e-4)) * uPulse * (0.4 + aSeed.x * 0.8);

  vec4 mv = modelViewMatrix * vec4(p, 1.0);
  gl_Position = projectionMatrix * mv;

  float depth = -mv.z;
  gl_PointSize = uSize * (0.35 + aSeed.z) * uPixelRatio / depth;

  // Fade very near (avoid big blobs crossing the lens) and very far particles.
  vAlpha = smoothstep(0.4, 2.0, depth) * smoothstep(45.0, 8.0, depth) * (0.3 + 0.7 * aSeed.z);
  vAlpha *= 1.0 + uPulse * 0.8;
  vMix = aSeed.x;
}
