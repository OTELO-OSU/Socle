import { AppBar, Box, Button, Toolbar, Typography } from '@mui/material'
import { useContext } from 'react'
import { AuthContext } from '../context/authContext'
import ConnexionIcon from '@mui/icons-material/Login';
import InscriptionIcon from '@mui/icons-material/PersonAdd';
import DeconnexionIcon from '@mui/icons-material/Logout';
import HomeWorkIcon from '@mui/icons-material/HomeWork';
import AdminIcon from '@mui/icons-material/AdminPanelSettings';
import { Link, Link as RouterLink } from 'react-router-dom'
import { AccountCircle } from '@mui/icons-material';
import { FRONT_URL } from '../utils/constante';
import { ApplicationSettingsContext } from '../context/applicationSettingsContext';
export default function HeaderAppBar() {

    const authContext = useContext(AuthContext)
    const applicationSettings = useContext(ApplicationSettingsContext)
    const token = authContext?.token ?? null;
    const isConnected = token !== null;

    async function handleDeconnexion() {
        if (sessionStorage.getItem("fedeConnexion") === "true") {
            window.location.href = `${FRONT_URL}/Shibboleth.sso/Logout?return=${FRONT_URL}/deconnexion`;
        } else {
            window.location.href = `${FRONT_URL}/deconnexion`;
        }
    }

    const dev = import.meta.env.DEV;

    return (
        <Box>

            <AppBar position="fixed">
                <Toolbar>
                    <Box
                        component={RouterLink}
                        to="/"
                        sx={{
                            display: 'flex',
                            alignItems: 'center',
                            flexGrow: 1,
                            minWidth: 0,
                            color: 'inherit',
                            textDecoration: 'none',
                        }}
                    >
                        {applicationSettings?.settings.logo_application && (
                            <Box component="img" src={applicationSettings.settings.logo_application.startsWith('data:') ? applicationSettings.settings.logo_application : `${FRONT_URL}${applicationSettings.settings.logo_application}`} alt="" sx={{ height: 32, maxWidth: 120, objectFit: 'contain', mr: 1 }} />
                        )}
                        <Typography variant="h6" component="span" noWrap>
                            {applicationSettings?.settings.nom_application ?? 'POE'} {dev && <Typography variant="caption" component="span">DEV</Typography>}
                        </Typography>
                    </Box>

                    {isConnected &&
                        <Button component={Link} to="/plateforme" color='inherit' startIcon={<HomeWorkIcon />}>
                            Plateformes
                        </Button>
                    }
                    {isConnected &&
                        <Button key={token} component={Link} to="/compte" color='inherit' startIcon={<AccountCircle />}>
                            {authContext?.prenom} {authContext?.nom}
                        </Button>
                    }
                    {authContext?.estAdmin ?
                        <Button component={Link} to="/admin" color='inherit' startIcon={<AdminIcon />}>
                            Admin
                        </Button> : authContext?.estDu ?
                            <Button component={Link} to="/admin/soumission-du" color='inherit' startIcon={<AdminIcon />}>
                                Admin
                            </Button> : <></>
                    }
                    {isConnected ?
                        <Button color='inherit' startIcon={<DeconnexionIcon />} onClick={() => void handleDeconnexion()}>
                            Déconnexion
                        </Button> :
                        <>
                            <Button component={RouterLink} to="/connexion" color='inherit' startIcon={<ConnexionIcon />}>
                                Connexion
                            </Button>
                            <Button component={RouterLink} to="/inscription" color='inherit' startIcon={<InscriptionIcon />}>
                                Inscription
                            </Button>
                        </>
                    }
                </Toolbar>
            </AppBar>
        </Box>
    )
}