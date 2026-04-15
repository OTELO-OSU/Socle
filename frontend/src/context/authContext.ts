import { createContext } from 'react'

export type TokenContextType = {
  token: string | null
  connexion: (email: string, mdp: string) => Promise<void>
  deconnexion: () => Promise<void>
  connexionViaOrcid: (code: string) => Promise<void>
  setToken: (value: string | null) => void
  clearToken: () => void
  estConnecte: boolean
  estAdmin: boolean
  id: number | null
  email: string | null
  prenom: string | null
  nom: string | null
}

export const STORAGE_KEY = 'auth_token'

export const AuthContext = createContext<TokenContextType | undefined>(undefined)
