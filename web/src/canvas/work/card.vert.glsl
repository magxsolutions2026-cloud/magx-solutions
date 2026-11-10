// Project card: a subdivided plane that bends slightly like a page and ripples
// outward from the cursor while hovered.

uniform float uHover;  // 0..1, damped on the CPU
uniform vec2 uMouse;   // cursor in card UV space
uniform float uTime;
uniform float uBend;   // page curvature, eased up for side cards

varying vec2 vUv;

void main() {
  vUv = uv;
  vec3 p = position;

  float nx = (uv.x - 0.5) * 2.0;
  p.z -= nx * nx * uBend;

  float d = distance(uv, uMouse);
  p.z += sin(d * 28.0 - uTime * 5.0) * 0.035 * uHover * smoothstep(0.45, 0.0, d);

  gl_Position = projectionMatrix * modelViewMatrix * vec4(p, 1.0);
}
