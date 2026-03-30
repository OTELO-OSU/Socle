import { createContext } from 'react'

export type TokenContextType = {
  token: string | null
  setToken: (value: string | null) => void
  clearToken: () => void
  isAuthenticated: boolean
}

export const STORAGE_KEY = 'auth_token'

export const TokenContext = createContext<TokenContextType | undefined>(undefined)
