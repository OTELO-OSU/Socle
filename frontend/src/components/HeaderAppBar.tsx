import { AppBar, Box, Button, Toolbar, Typography } from '@mui/material'
import { useContext } from 'react'
import { AuthContext } from '../context/authContext'
import ConnexionIcon from '@mui/icons-material/Login';
import InscriptionIcon from '@mui/icons-material/PersonAdd';
import DeconnexionIcon from '@mui/icons-material/Logout';
import HomeWorkIcon from '@mui/icons-material/HomeWork';
import AdminIcon from '@mui/icons-material/AdminPanelSettings';
import { Link, Link as RouterLink, useNavigate } from 'react-router-dom'
import { SnackbarContext } from '../context/snackbarContext';
export default function HeaderAppBar() {

    const authContext = useContext(AuthContext)
    const snackbarContext = useContext(SnackbarContext)
    const navigate = useNavigate();

    function handleDeconnexion() {
        authContext?.deconnexion()
        snackbarContext?.trigger({
            message: "Déconnexion réussie",
            couleur: "success",
        })
        navigate('/')
    }

    return (
        <Box>
            <AppBar position="fixed">
                <Toolbar>
                    <Typography variant="h6" component={RouterLink} to="/" sx={{ flexGrow: 1 }} style={{ textDecoration: 'none', color: 'inherit' }}>
                        POE
                    </Typography>

                    {authContext?.estConnecte &&
                        <Button component={Link} to="/plateformes" color='inherit' startIcon={<HomeWorkIcon />}>
                            Plateformes
                        </Button>
                    }
                    {authContext?.estAdmin &&
                        <Button component={Link} to="/admin" color='inherit' startIcon={<AdminIcon />}>
                            Admin
                        </Button>
                    }
                    {authContext?.estConnecte ?
                        <Button color='inherit' startIcon={<DeconnexionIcon />} onClick={handleDeconnexion}>
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