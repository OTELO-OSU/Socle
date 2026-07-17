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
export default function HeaderAppBar() {

    const authContext = useContext(AuthContext)
    const token = authContext?.token ?? null;
    const isConnected = token !== null;

    async function handleDeconnexion() {
        window.location.href = `https://${FRONT_URL}/Shibboleth.sso/Logout?return=${FRONT_URL}/deconnexion`;
    }

    return (
        <Box>
            <AppBar position="fixed">
                <Toolbar>
                    <Typography variant="h6" component={RouterLink} to="/" sx={{ flexGrow: 1 }} style={{ textDecoration: 'none', color: 'inherit' }}>
                        POE
                    </Typography>

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