import { symfony } from '../utils/axios'
import type { ApplicationSettings } from '../context/applicationSettingsContext'

export async function fetchApplicationSettings() {
    const { data } = await symfony.get<ApplicationSettings>('/parametres/application')
    return data
}

export async function saveApplicationSettings(name: string, primaryColor: string, homeView: 'carte' | 'liste', logo: File | null) {
    const formData = new FormData()
    formData.append('nom_application', name)
    formData.append('couleur_principale', primaryColor)
    formData.append('vue_accueil', homeView)
    if (logo) formData.append('logo', logo)

    const { data } = await symfony.post<ApplicationSettings>('/parametres/application', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data
}
