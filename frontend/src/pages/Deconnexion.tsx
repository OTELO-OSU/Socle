import { useContext, useEffect } from 'react'
import { AuthContext } from '../context/authContext'
import { useNavigate } from 'react-router-dom'
import { SnackbarContext } from '../context/snackbarContext';
import { Box, CircularProgress, Typography } from '@mui/material';
export default function Deconnexion() {

    const authContext = useContext(AuthContext)
    const snackbarContext = useContext(SnackbarContext)
    const navigate = useNavigate();

    async function handleDeconnexion() {
        await authContext?.deconnexion()
        snackbarContext?.trigger({
            message: "Déconnexion réussie",
            couleur: "success",
        })
        navigate('/', { replace: true })
    }

    useEffect(() => {
        handleDeconnexion();
    }, []);

    return (
        <Box
            sx={{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                height: '100%',
                minHeight: (theme) => `calc(100vh - ${theme.mixins.toolbar.minHeight}px)`
            }}
        >
            <CircularProgress size={50} />
            <Typography variant="h3" component="div" sx={{ ml: 2 }}>
                Déconnexion en cours
            </Typography>
        </Box>
    )
}