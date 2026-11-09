import { Box, Button, Card, Divider, FormControl, FormLabel, Link, TextField, Typography } from "@mui/material";
import { useContext, useState } from "react";
import MdpOublie from "../components/MdpOublie";
import { AccountBalance } from "@mui/icons-material";
import { AuthContext } from "../context/authContext";
import type { AxiosError } from "axios";
import { SnackbarContext } from "../context/snackbarContext";
import type { ApiError } from "../utils/axios";
import { useNavigate } from "react-router-dom";

export default function Connexion() {
    const [emailError, setEmailError] = useState(false);
    const [emailErrorMessage, setEmailErrorMessage] = useState('');
    const [passwordError, setPasswordError] = useState(false);
    const [passwordErrorMessage, setPasswordErrorMessage] = useState('');
    const [open, setOpen] = useState(false);
    const authContext = useContext(AuthContext);
    const snackbarContext = useContext(SnackbarContext);
    const naviagate = useNavigate();

    function handleClickOpen() {
        setOpen(true);
    }

    function handleClose() {
        setOpen(false);
    }

    async function handleSubmit(event: React.SubmitEvent<HTMLFormElement>) {
        event.preventDefault();
        const data = new FormData(event.currentTarget);
        const email = data.get('email') as string;
        const password = data.get('password') as string;

        let hasError = false;

        if (!email) {
            setEmailError(true);
            setEmailErrorMessage('Email requis');
            hasError = true;
        } else {
            setEmailError(false);
            setEmailErrorMessage('');
        }

        if (!password) {
            setPasswordError(true);
            setPasswordErrorMessage('Mot de passe requis');
            hasError = true;
        } else {
            setPasswordError(false);
            setPasswordErrorMessage('');
        }

        if (hasError) {
            return;
        }

        try {
            await authContext?.connexion(email, password);
            snackbarContext?.trigger({
                message: 'Connexion réussie',
                couleur: 'success',
            });
            sessionStorage.setItem("fedeConnexion", "false");
            naviagate('/');
        } catch (e) {
            const error = e as AxiosError<ApiError>;
            if (error.response?.status === 401) {
                snackbarContext?.trigger({
                    message: error.response.data?.message || 'Email ou mot de passe incorrect',
                    couleur: 'error',
                });
            }

            if (error.response?.status === 403) {
                snackbarContext?.trigger({
                    message: error.response.data?.message || 'Votre compte est en attente de validation par un administrateur',
                    couleur: 'warning',
                });
            }

            if (error.response?.status === 429) {
                snackbarContext?.trigger({
                    message: error.response.data?.message || 'Trop de tentatives de connexion. Veuillez réessayer plus tard.',
                    couleur: 'warning',
                });
            }
        }
    }
    return (
        <Box
            sx={{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                height: '100%',
                minHeight: (theme) => `calc(100vh - ${theme.mixins.toolbar.minHeight}px)`,
                px: 2,
                py: 4,
            }}
        >
            <Card sx={{
                p: { xs: 3, sm: 4 },
                width: '100%',
                maxWidth: 480,
                display: 'flex',
                flexDirection: 'column',
                gap: 2.5,
                borderRadius: 3,
                border: '1px solid',
                borderColor: 'divider',
                boxShadow: '0 10px 30px rgba(15,23,42,0.08)',
            }}>
                <Box sx={{ textAlign: 'center' }}>
                    <Typography variant="h4" component="h1">Connexion</Typography>
                    <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
                        Accédez à votre espace avec votre établissement ou votre compte.
                    </Typography>
                </Box>
                <Button
                    fullWidth
                    size="large"
                    variant="contained"
                    onClick={() => window.location.href = "/loginCAS"}
                    startIcon={<AccountBalance />}
                    sx={{
                        minHeight: 68,
                        px: 2.5,
                        fontSize: '1.1rem',
                        fontWeight: 600,
                        lineHeight: 1.4,
                        textTransform: 'none',
                        '& .MuiButton-startIcon svg': { fontSize: '1.5rem' },
                    }}
                >
                    Se connecter avec votre établissement
                </Button>
                <Divider><Typography variant="body2" color="text.secondary">ou avec un compte</Typography></Divider>
                <Box component="form" onSubmit={handleSubmit} noValidate sx={{ display: 'flex', flexDirection: 'column', width: '100%', gap: 2.5 }}>
                    <FormControl>
                        <FormLabel htmlFor="email">Email</FormLabel>
                        <TextField
                            error={emailError}
                            helperText={emailErrorMessage}
                            id="email"
                            type="email"
                            name="email"
                            placeholder="alice.dubois@gmail.com"
                            autoComplete="email"
                            required
                            fullWidth
                            variant="outlined"
                            color={emailError ? 'error' : 'primary'}
                        />
                    </FormControl>
                    <FormControl>
                        <FormLabel htmlFor="password">Mot de passe</FormLabel>
                        <TextField
                            error={passwordError}
                            helperText={passwordErrorMessage}
                            name="password"
                            placeholder="••••••"
                            type="password"
                            id="password"
                            autoComplete="current-password"
                            required
                            fullWidth
                            variant="outlined"
                            color={passwordError ? 'error' : 'primary'}
                        />
                    </FormControl>
                    <MdpOublie open={open} handleClose={handleClose} />
                    <Button
                        type="submit"
                        fullWidth
                        variant="outlined"
                    >
                        Se connecter
                    </Button>
                    <Link
                        component="button"
                        type="button"
                        onClick={handleClickOpen}
                        variant="body2"
                        sx={{ alignSelf: 'center' }}
                    >
                        Mot de passe oublié ?
                    </Link>
                </Box>
            </Card>
        </Box>
    )
}