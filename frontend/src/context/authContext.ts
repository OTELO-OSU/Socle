import { createContext } from 'react'

export type AuthContextType = {
  token: string | null
  connexion: (email: string, mdp: string) => Promise<void>
  connexionViaOrcid: (code: string) => Promise<void>
  deconnexion: () => Promise<void>
  setToken: (value: string | null) => void
  clearToken: () => void
  isAuthLoading: boolean
  estConnecte: boolean
  estAdmin: boolean
  id: number | null
  email: string | null
  prenom: string | null
  nom: string | null
}

export const STORAGE_KEY = 'auth_token'

export const AuthContext = createContext<AuthContextType | undefined>(undefined)
