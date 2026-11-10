/**
 * Per-frame state shared between the Work section (DOM) and the 3D deck.
 * Deliberately a plain mutable object, not React state: scroll progress changes
 * every frame and the deck reads it inside useFrame without re-rendering.
 */
export const workDeck = {
  /** 0..1 scroll progress through the pinned Work section. */
  progress: 0,
  /** Scrolls the page so project `index` is focused; set by the Work section. */
  select: undefined as ((index: number) => void) | undefined,
}
