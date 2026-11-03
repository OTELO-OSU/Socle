import { useCallback, useEffect, useMemo, useState } from 'react'
import { ApplicationSettingsContext, DEFAULT_APPLICATION_SETTINGS, type ApplicationSettings } from '../context/applicationSettingsContext'
import { fetchApplicationSettings, saveApplicationSettings } from '../services/applicationSettingsService'
import { FRONT_URL } from '../utils/constante'

export function ApplicationSettingsProvider({ children }: { children: React.ReactNode }) {
    const [settings, setSettings] = useState<ApplicationSettings>(DEFAULT_APPLICATION_SETTINGS)
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        fetchApplicationSettings()
            .then(setSettings)
            .catch((error) => console.error('Impossible de charger les paramètres de l’application.', error))
            .finally(() => setLoading(false))
    }, [])

    useEffect(() => {
        let favicon = document.querySelector<HTMLLinkElement>('link[rel="icon"]')
        if (!favicon) {
            favicon = document.createElement('link')
            favicon.rel = 'icon'
            document.head.appendChild(favicon)
        }

        if (settings.logo_application) {
            favicon.removeAttribute('type')
            favicon.href = settings.logo_application.startsWith('data:')
                ? settings.logo_application
                : `${FRONT_URL}${settings.logo_application}`
        } else {
            favicon.type = 'image/png'
            favicon.href = '/favicon.png'
        }
    }, [settings.logo_application])

    const saveSettings = useCallback(async (name: string, primaryColor: string, homeView: 'carte' | 'liste', logo: File | null) => {
        const updatedSettings = await saveApplicationSettings(name, primaryColor, homeView, logo)
        setSettings(updatedSettings)
    }, [])

    const value = useMemo(() => ({ settings, loading, saveSettings }), [settings, loading, saveSettings])
    return <ApplicationSettingsContext.Provider value={value}>{children}</ApplicationSettingsContext.Provider>
}
