import { Box, Button, Card, Divider, FormControl, FormLabel, Link, TextField, Typography } from "@mui/material";
import { useContext, useState } from "react";
import MdpOublie from "../components/MdpOublie";
import OrcidIcon from "../components/icons/OrcidIcon";
import { AccountBalance } from "@mui/icons-material";
import { AuthContext } from "../context/authContext";
import type { AxiosError } from "axios";
import { SnackbarContext } from "../context/snackbarContext";
import type { ApiError } from "../utils/axios";
import { useNavigate } from "react-router-dom";
import { ORCID_URL } from "../utils/constante";

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
                minHeight: (theme) => `calc(100vh - ${theme.mixins.toolbar.minHeight}px)`
            }}
        >
            <Card sx={{ p: 4, width: '100%', maxWidth: 500, display: 'flex', flexDirection: 'column', gap: 2 }}>
                <Typography variant="h4" component="h1" align="center">Connexion</Typography>
                <Box component="form" onSubmit={handleSubmit} noValidate sx={{ display: 'flex', flexDirection: 'column', width: '100%', gap: 2 }}>
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
                        variant="contained"
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
                <Divider>ou</Divider>
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                    <Button
                        fullWidth
                        variant="outlined"
                        onClick={() => window.location.href = `${ORCID_URL}`}
                        startIcon={<OrcidIcon />}
                    >
                        Se connecter avec ORCID
                    </Button>
                    <Button
                        fullWidth
                        variant="outlined"
                        onClick={() => window.location.href = "/loginCAS"}
                        startIcon={<AccountBalance />}
                    >
                        Se connecter avec votre établissement
                    </Button>
                </Box>
            </Card>
        </Box>
    )
}