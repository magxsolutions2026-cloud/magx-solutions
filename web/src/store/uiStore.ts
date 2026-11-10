import { create } from 'zustand'

/** UI state shared across sections (not the 3D scene). */
type UiState = {
  bookingOpen: boolean
  /** Pre-selected service when the dialog is opened from a service card. */
  bookingService: string
  openBooking: (service?: string) => void
  closeBooking: () => void
}

export const useUiStore = create<UiState>()((set) => ({
  bookingOpen: false,
  bookingService: '',
  openBooking: (service = '') => set({ bookingOpen: true, bookingService: service }),
  closeBooking: () => set({ bookingOpen: false }),
}))
