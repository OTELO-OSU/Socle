import { AppBar, Box, Button, Toolbar, Typography } from '@mui/material'
import { useContext } from 'react'
import { AuthContext } from '../context/authContext'
import ConnexionIcon from '@mui/icons-material/Login';
import DeconnexionIcon from '@mui/icons-material/Logout';
import AdminIcon from '@mui/icons-material/AdminPanelSettings';
import { Link as RouterLink, useNavigate } from 'react-router-dom'
export default function HeaderAppBar() {

    const authContext = useContext(AuthContext)
    const navigate = useNavigate();

    function handleDeconnexion() {
        authContext?.deconnexion()
        navigate('/')
    }

    return (
        <Box>
            <AppBar position="static">
                <Toolbar>
                    <Typography variant="h6" component="div" sx={{ flexGrow: 1 }}>POE</Typography>
                    {authContext?.estAdmin &&
                        <Button color='inherit' startIcon={<AdminIcon />}>
                            Admin
                        </Button>
                    }
                    {authContext?.estConnecte ?
                        <Button color='inherit' startIcon={<DeconnexionIcon />} onClick={handleDeconnexion}>
                            Déconnexion
                        </Button> :
                        <Button component={RouterLink} to="/connexion" color='inherit' startIcon={<ConnexionIcon />}>
                            Connexion
                        </Button>
                    }
                </Toolbar>
            </AppBar>
        </Box>
    )
}