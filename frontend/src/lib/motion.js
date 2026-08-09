/**
 * Shared Motion (Framer Motion for Vue) presets.
 * Keep view files thin: import presets instead of inlining animation objects.
 */

export const transitions = {
  soft: { duration: 0.28, ease: 'easeOut' },
  snappy: { duration: 0.16, ease: 'easeOut' },
}

export const fade = {
  initial: { opacity: 0 },
  animate: { opacity: 1 },
  exit: { opacity: 0 },
}

export const fadeUp = {
  initial: { opacity: 0, y: 12 },
  animate: { opacity: 1, y: 0 },
  exit: { opacity: 0, y: -8 },
}

export const scaleIn = {
  initial: { opacity: 0, scale: 0.96 },
  animate: { opacity: 1, scale: 1 },
  exit: { opacity: 0, scale: 0.98 },
}

export const slideDown = {
  initial: { opacity: 0, y: -8 },
  animate: { opacity: 1, y: 0 },
  exit: { opacity: 0, y: -8 },
}

/** Stagger delay helper for list/card indexes. */
export function staggerDelay(index, step = 0.05) {
  return index * step
}
