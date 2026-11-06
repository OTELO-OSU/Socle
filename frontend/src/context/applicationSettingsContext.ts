import { createContext } from 'react'

export type ApplicationSettings = {
    nom_application: string
    couleur_principale: string
    logo_application: string | null
    vue_accueil: 'carte' | 'liste'
}

export type ApplicationSettingsContextType = {
    settings: ApplicationSettings
    loading: boolean
    saveSettings: (name: string, primaryColor: string, homeView: 'carte' | 'liste', logo: File | null) => Promise<void>
}

export const DEFAULT_APPLICATION_SETTINGS: ApplicationSettings = {
    nom_application: 'POE',
    couleur_principale: '#ba272c',
    logo_application: null,
    vue_accueil: 'carte',
}

export const ApplicationSettingsContext = createContext<ApplicationSettingsContextType | undefined>(undefined)
