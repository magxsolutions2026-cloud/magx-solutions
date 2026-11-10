uniform vec3 uColorA;
uniform vec3 uColorB;

varying float vAlpha;
varying float vMix;

void main() {
  // Soft round sprite with a hot core.
  float d = length(gl_PointCoord - 0.5);
  float a = smoothstep(0.5, 0.0, d);
  a *= a;
  if (a < 0.01) discard;
  vec3 color = mix(uColorA, uColorB, smoothstep(0.55, 1.0, vMix));
  gl_FragColor = vec4(color, a * vAlpha);
}
