import { Box, Button, Card, FormControl, FormHelperText, FormLabel, Link, MenuItem, Select, Stack, TextField, Typography } from "@mui/material";
import { useContext, useState } from "react";
import type { AxiosError } from "axios";
import { SnackbarContext } from "../context/snackbarContext";
import type { ApiError } from "../utils/axios";
import { useNavigate, Link as RouterLink } from "react-router-dom";
import { inscription } from "../services/authService";

export default function Inscription() {
    const [prenomError, setPrenomError] = useState(false);
    const [prenomErrorMessage, setPrenomErrorMessage] = useState('');
    const [nomError, setNomError] = useState(false);
    const [nomErrorMessage, setNomErrorMessage] = useState('');
    const [emailError, setEmailError] = useState(false);
    const [emailErrorMessage, setEmailErrorMessage] = useState('');
    const [role, setRole] = useState('');
    const [roleError, setRoleError] = useState(false);
    const [roleErrorMessage, setRoleErrorMessage] = useState('');
    const [mdpError, setMdpError] = useState(false);
    const [mdpErrorMessage, setMdpErrorMessage] = useState('');
    const [confirmMdpError, setConfirmMdpError] = useState(false);
    const [confirmMdpErrorMessage, setConfirmMdpErrorMessage] = useState('');
    const snackbarContext = useContext(SnackbarContext);
    const naviagate = useNavigate();

    async function handleSubmit(event: React.SubmitEvent<HTMLFormElement>) {
        event.preventDefault();
        const data = new FormData(event.currentTarget);
        const email = data.get('email') as string;
        const mdp = data.get('mdp') as string;
        const confirmMdp = data.get('confirmMdp') as string;
        const prenom = data.get('prenom') as string;
        const nom = data.get('nom') as string;
        const selectedRole = data.get('role') as string;

        let hasError = false;

        if (!prenom) {
            setPrenomError(true);
            setPrenomErrorMessage('Prénom requis');
            hasError = true;
        } else {
            setPrenomError(false);
            setPrenomErrorMessage('');
        }

        if (!nom) {
            setNomError(true);
            setNomErrorMessage('Nom requis');
            hasError = true;
        } else {
            setNomError(false);
            setNomErrorMessage('');
        }

        if (!email) {
            setEmailError(true);
            setEmailErrorMessage('Email requis');
            hasError = true;
        } else {
            setEmailError(false);
            setEmailErrorMessage('');
        }

        if (!selectedRole) {
            setRoleError(true);
            setRoleErrorMessage('Rôle requis');
            hasError = true;
        } else {
            setRoleError(false);
            setRoleErrorMessage('');
        }

        if (!mdp) {
            setMdpError(true);
            setMdpErrorMessage('Mot de passe requis');
            hasError = true;
        } else {
            setMdpError(false);
            setMdpErrorMessage('');
        }

        if (!confirmMdp) {
            setConfirmMdpError(true);
            setConfirmMdpErrorMessage('Confirmation du mot de passe requise');
            hasError = true;
        } else {
            setConfirmMdpError(false);
            setConfirmMdpErrorMessage('');
        }


        if (mdp && confirmMdp && mdp !== confirmMdp) {
            setConfirmMdpError(true);
            setConfirmMdpErrorMessage('Les mots de passe ne correspondent pas');
            hasError = true;
        } else if (mdp && confirmMdp) {
            setConfirmMdpError(false);
            setConfirmMdpErrorMessage('');
        }

        if (hasError) {
            return;
        }

        try {
            const { data } = await inscription(prenom, nom, email, mdp, selectedRole);
            snackbarContext?.trigger({
                message: data.message,
                couleur: 'success',
            });
            naviagate('/');
        } catch (e) {
            const error = e as AxiosError<ApiError>;
            if (error.response?.status === 400) {
                snackbarContext?.trigger({
                    message: error.response.data?.messageArray.map((msg) => `- ${msg}`).join('\n') || 'Une erreur est survenue lors de l\'inscription',
                    couleur: 'error',
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
                <Typography variant="h4" component="h1" align="center">Inscription</Typography>
                <Box component="form" onSubmit={handleSubmit} noValidate sx={{ display: 'flex', flexDirection: 'column', width: '100%', gap: 2 }}>
                    <Stack direction="row" spacing={2}>
                        <FormControl>
                            <FormLabel htmlFor="prenom">Prénom</FormLabel>
                            <TextField
                                error={prenomError}
                                helperText={prenomErrorMessage}
                                id="prenom"
                                type="text"
                                name="prenom"
                                placeholder="Alice"
                                autoComplete="given-name"
                                required
                                fullWidth
                                variant="outlined"
                                color={prenomError ? 'error' : 'primary'}
                            />
                        </FormControl>
                        <FormControl>
                            <FormLabel htmlFor="nom">Nom</FormLabel>
                            <TextField
                                error={nomError}
                                helperText={nomErrorMessage}
                                id="nom"
                                type="text"
                                name="nom"
                                placeholder="Dubois"
                                autoComplete="family-name"
                                required
                                fullWidth
                                variant="outlined"
                                color={nomError ? 'error' : 'primary'}
                            />
                        </FormControl>
                    </Stack>
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
                    <FormControl fullWidth error={roleError}>
                        <Select id="role" value={role} onChange={(e) => setRole(e.target.value)} name="role" displayEmpty>
                            <MenuItem value="">Sélectionner un rôle</MenuItem>
                            <MenuItem value="3">Directeur OSU</MenuItem>
                        </Select>
                        <FormHelperText>{roleErrorMessage}</FormHelperText>
                    </FormControl>
                    <FormControl>
                        <FormLabel htmlFor="mdp">Mot de passe</FormLabel>
                        <TextField
                            error={mdpError}
                            helperText={mdpErrorMessage}
                            name="mdp"
                            placeholder="••••••"
                            type="password"
                            id="mdp"
                            autoComplete="new-password"
                            required
                            fullWidth
                            variant="outlined"
                            color={mdpError ? 'error' : 'primary'}
                        />
                    </FormControl>
                    <FormControl>
                        <FormLabel htmlFor="confirmMdp">Confirmer le mot de passe</FormLabel>
                        <TextField
                            error={confirmMdpError}
                            helperText={confirmMdpErrorMessage}
                            name="confirmMdp"
                            placeholder="••••••"
                            type="password"
                            id="confirmMdp"
                            autoComplete="new-password"
                            required
                            fullWidth
                            variant="outlined"
                            color={confirmMdpError ? 'error' : 'primary'}
                        />
                    </FormControl>
                    <Button
                        type="submit"
                        fullWidth
                        variant="contained"
                    >
                        S'inscrire
                    </Button>
                    <Link
                        component={RouterLink}
                        to="/connexion"
                        variant="body2"
                        sx={{ alignSelf: 'center' }}
                    >
                        Déjà un compte ? Se connecter
                    </Link>
                </Box>
            </Card>
        </Box>
    )
}