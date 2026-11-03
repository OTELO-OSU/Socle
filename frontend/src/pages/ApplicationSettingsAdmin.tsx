import { useContext, useEffect, useState, type FormEvent } from 'react'
import { Box, Button, Card, CardContent, CircularProgress, FormControl, InputLabel, MenuItem, Select, Stack, TextField, Typography } from '@mui/material'
import { ApplicationSettingsContext } from '../context/applicationSettingsContext'
import { SnackbarContext } from '../context/snackbarContext'
import { AdminPageShell } from '../components/admin/AdminPageShell'
import { FRONT_URL } from '../utils/constante'

export default function ApplicationSettingsAdmin() {
    const applicationSettings = useContext(ApplicationSettingsContext)
    const snackbar = useContext(SnackbarContext)
    const [name, setName] = useState('')
    const [primaryColor, setPrimaryColor] = useState('#ba272c')
    const [homeView, setHomeView] = useState<'carte' | 'liste'>('carte')
    const [logo, setLogo] = useState<File | null>(null)
    const [saving, setSaving] = useState(false)

    useEffect(() => {
        if (!applicationSettings) return
        setName(applicationSettings.settings.nom_application)
        setPrimaryColor(applicationSettings.settings.couleur_principale)
        setHomeView(applicationSettings.settings.vue_accueil)
    }, [applicationSettings])

    if (!applicationSettings) return null
    const settingsContext = applicationSettings

    async function handleSubmit(event: FormEvent) {
        event.preventDefault()
        setSaving(true)
        try {
            await settingsContext.saveSettings(name, primaryColor, homeView, logo)
            setLogo(null)
            snackbar?.trigger({ message: 'Les paramètres de l’application ont été enregistrés.', couleur: 'success' })
        } catch (error) {
            console.error('Erreur lors de l’enregistrement des paramètres.', error)
            snackbar?.trigger({ message: 'Impossible d’enregistrer les paramètres.', couleur: 'error' })
        } finally {
            setSaving(false)
        }
    }

    return (
        <AdminPageShell title="Paramètres de l'application" subtitle="Personnalisez le nom, la couleur principale et le logo affichés dans l'application.">
            <Card component="form" onSubmit={(event) => void handleSubmit(event)} sx={{ borderRadius: 3 }}>
                <CardContent>
                    <Stack spacing={2.5} sx={{ maxWidth: 480 }}>
                        <TextField label="Nom de l'application" value={name} onChange={(event) => setName(event.target.value)} required />
                        <TextField label="Couleur principale" type="color" value={primaryColor} onChange={(event) => setPrimaryColor(event.target.value)} required />
                        <FormControl>
                            <InputLabel id="vue-accueil-label">Vue par défaut de l'accueil</InputLabel>
                            <Select
                                labelId="vue-accueil-label"
                                value={homeView}
                                label="Vue par défaut de l'accueil"
                                onChange={(event) => setHomeView(event.target.value as 'carte' | 'liste')}
                            >
                                <MenuItem value="carte">Cartes</MenuItem>
                                <MenuItem value="liste">Liste</MenuItem>
                            </Select>
                        </FormControl>
                        <Box>
                            <Button variant="outlined" component="label">
                                Choisir un logo
                                <input hidden type="file" accept="image/*" onChange={(event) => setLogo(event.target.files?.[0] ?? null)} />
                            </Button>
                            <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
                                {logo?.name ?? 'Aucun nouveau logo sélectionné'}
                            </Typography>
                        </Box>
                        {applicationSettings.settings.logo_application && (
                            <Box component="img" src={applicationSettings.settings.logo_application.startsWith('data:') ? applicationSettings.settings.logo_application : `${FRONT_URL}${applicationSettings.settings.logo_application}`} alt="Logo actuel" sx={{ maxWidth: 240, maxHeight: 100, objectFit: 'contain' }} />
                        )}
                        <Button type="submit" variant="contained" disabled={saving || settingsContext.loading}>
                            {saving ? <CircularProgress size={22} color="inherit" /> : 'Enregistrer'}
                        </Button>
                    </Stack>
                </CardContent>
            </Card>
        </AdminPageShell>
    )
}
