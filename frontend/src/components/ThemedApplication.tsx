import { createTheme, ThemeProvider } from '@mui/material'
import { useContext, useMemo } from 'react'
import App from '../App'
import { ApplicationSettingsContext } from '../context/applicationSettingsContext'

export default function ThemedApplication() {
    const applicationSettings = useContext(ApplicationSettingsContext)
    const theme = useMemo(() => createTheme({
        palette: {
            primary: { main: applicationSettings?.settings.couleur_principale ?? '#ba272c' },
            secondary: { main: '#49b6ff' },
        },
        typography: { fontFamily: 'Roboto, sans-serif' },
    }), [applicationSettings?.settings.couleur_principale])

    if (applicationSettings?.loading) return null

    return <ThemeProvider theme={theme}><App /></ThemeProvider>
}
