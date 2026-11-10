import { useEffect, useState } from 'react'

/** Portrait (phones, narrow windows) switches camera shots and particle budgets. */
const PORTRAIT_QUERY = '(max-aspect-ratio: 1/1)'
const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)'

function useMediaQuery(query: string) {
  const [matches, setMatches] = useState(() => window.matchMedia(query).matches)
  useEffect(() => {
    const mq = window.matchMedia(query)
    const onChange = () => setMatches(mq.matches)
    mq.addEventListener('change', onChange)
    return () => mq.removeEventListener('change', onChange)
  }, [query])
  return matches
}

export const usePortrait = () => useMediaQuery(PORTRAIT_QUERY)
export const useReducedMotion = () => useMediaQuery(REDUCED_MOTION_QUERY)
