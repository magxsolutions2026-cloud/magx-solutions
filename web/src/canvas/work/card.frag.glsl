// Project card surface: fitted image with a hover lens distortion, chromatic
// split, cursor flare and sheen; rounded corners with a glowing edge.

uniform sampler2D uMap;
uniform float uPlaneAspect;
uniform float uImageAspect;
uniform float uContain;   // 1 = letterbox the image, 0 = crop to fill
uniform vec3 uBg;         // letterbox color
uniform vec3 uAccent;
uniform float uHover;
uniform float uDim;       // 0 = focused card, 1 = far side card
uniform float uOpacity;
uniform float uTime;
uniform vec2 uMouse;

varying vec2 vUv;

float roundedBox(vec2 p, vec2 b, float r) {
  vec2 q = abs(p) - b + r;
  return length(max(q, 0.0)) + min(max(q.x, q.y), 0.0) - r;
}

// Maps card UV to image UV for "cover" (crop) or "contain" (letterbox) fitting.
vec2 fitUv(vec2 uv) {
  float pa = uPlaneAspect;
  float ia = uImageAspect;
  vec2 s;
  if (uContain > 0.5) {
    s = pa > ia ? vec2(pa / ia, 1.0) : vec2(1.0, ia / pa);
  } else {
    s = pa > ia ? vec2(1.0, ia / pa) : vec2(pa / ia, 1.0);
  }
  return (uv - 0.5) * s + 0.5;
}

void main() {
  vec2 uv = vUv;

  // Subtle zoom-in on hover, then a rippling lens push away from the cursor.
  vec2 c = (uv - 0.5) * (1.0 - 0.06 * uHover) + 0.5;
  vec2 toMouse = c - uMouse;
  float d = length(toMouse);
  c += normalize(toMouse + 1e-5) * 0.025 * uHover * smoothstep(0.35, 0.0, d) * sin(d * 18.0 - uTime * 3.0);

  vec2 iuv = fitUv(c);
  float shift = 0.007 * uHover * smoothstep(0.55, 0.0, d);
  vec3 col = vec3(
    texture2D(uMap, iuv + vec2(shift, 0.0)).r,
    texture2D(uMap, iuv).g,
    texture2D(uMap, iuv - vec2(shift, 0.0)).b
  );
  if (any(lessThan(iuv, vec2(0.0))) || any(greaterThan(iuv, vec2(1.0)))) col = uBg;

  // Lighting flare under the cursor plus a diagonal sheen that sweeps while hovered.
  float flare = smoothstep(0.5, 0.0, d) * 0.32 * uHover;
  float band = (uv.x + uv.y) * 0.5 - (fract(uTime * 0.22) * 1.8 - 0.4);
  float sheen = smoothstep(0.07, 0.0, abs(band)) * 0.16 * uHover;
  col += uAccent * flare + vec3(sheen);

  col *= mix(1.0, 0.32, uDim);

  // Rounded corners and an edge that glows brighter on hover.
  vec2 p = (vUv - 0.5) * vec2(uPlaneAspect, 1.0);
  float dist = roundedBox(p, vec2(uPlaneAspect * 0.5, 0.5), 0.045);
  float alpha = 1.0 - smoothstep(-0.003, 0.0, dist);
  float edge = smoothstep(0.012, 0.0, abs(dist + 0.006)) * (0.18 + 0.82 * uHover);
  col = mix(col, uAccent, edge * 0.85);

  gl_FragColor = vec4(col, alpha * uOpacity);
  #include <colorspace_fragment>
}
