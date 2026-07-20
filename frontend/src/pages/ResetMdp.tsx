import { Box, Button, Card, FormControl, FormLabel, TextField, Typography } from "@mui/material";
import { useContext, useEffect, useState } from "react";
import { motDePasseOubliePhase2 } from "../services/authService";
import { SnackbarContext } from "../context/snackbarContext";
import { useNavigate } from "react-router-dom";

export default function ResetMdp() {

    const snackbarContext = useContext(SnackbarContext);
    const router = useNavigate();

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');

        if (!token) {
            router('/', { replace: true });
        }
    }, []);

    const [passwordError, setPasswordError] = useState(false);
    const [passwordErrorMessage, setPasswordErrorMessage] = useState('');
    const [passwordConfirmError, setPasswordConfirmError] = useState(false);
    const [passwordConfirmErrorMessage, setPasswordConfirmErrorMessage] = useState('');
    async function handleSubmit(event: React.SubmitEvent<HTMLFormElement>) {
        event.preventDefault();

        const data = new FormData(event.currentTarget);
        const mdp = data.get('password') as string;
        const mdpConfirm = data.get('passwordConfirm') as string;

        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');

        if (!token) {
            console.error('Token manquant dans l\'URL');
            router('/', { replace: true });
            return;
        }

        setPasswordError(false);
        setPasswordErrorMessage('');
        setPasswordConfirmError(false);
        setPasswordConfirmErrorMessage('');

        if (!mdp) {
            setPasswordError(true);
            setPasswordErrorMessage('Le mot de passe est requis');
            return;
        }

        if (!mdpConfirm) {
            setPasswordConfirmError(true);
            setPasswordConfirmErrorMessage('La confirmation du mot de passe est requise');
            return;
        }

        if (mdp.length < 8) {
            setPasswordError(true);
            setPasswordErrorMessage('Le mot de passe doit contenir au moins 8 caractères');
            return;
        }

        if (mdp !== mdpConfirm) {
            setPasswordConfirmError(true);
            setPasswordConfirmErrorMessage('Les mots de passe ne correspondent pas');
            return;
        }
        try {
            const res = await motDePasseOubliePhase2(token, mdp)
            snackbarContext?.trigger({
                message: res.message,
                couleur: 'success',
            });

        } catch (e) {
            const error = e as any;
            snackbarContext?.trigger({
                message: error.response?.data?.message || 'Une erreur est survenue',
                couleur: 'error',
            });
        }


        router('/connexion', { replace: true });
    }
    return (
        <Box sx={{
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            height: '100%',
            minHeight: (theme) => `calc(100vh - ${theme.mixins.toolbar.minHeight}px)`
        }}>
            <Card sx={{ p: 4, width: '100%', maxWidth: 500, display: 'flex', flexDirection: 'column', gap: 2 }}>
                <Typography variant="h4" component="h1" align="center">Réinitialiser le mot de passe</Typography>
                <Box component="form" onSubmit={handleSubmit} noValidate sx={{ display: 'flex', flexDirection: 'column', width: '100%', gap: 2 }}>
                    <FormControl>
                        <FormLabel htmlFor="password">Mot de passe</FormLabel>
                        <TextField
                            error={passwordError}
                            helperText={passwordErrorMessage}
                            id="password"
                            type="password"
                            name="password"
                            placeholder="••••••"
                            autoComplete="current-password"
                            required
                            fullWidth
                            variant="outlined"
                            color={passwordError ? 'error' : 'primary'}
                        />
                    </FormControl>
                    <FormControl>
                        <FormLabel htmlFor="passwordConfirm">Confirmer le mot de passe</FormLabel>
                        <TextField
                            error={passwordConfirmError}
                            helperText={passwordConfirmErrorMessage}
                            name="passwordConfirm"
                            placeholder="••••••"
                            type="password"
                            id="passwordConfirm"
                            autoComplete="current-password"
                            required
                            fullWidth
                            variant="outlined"
                            color={passwordConfirmError ? 'error' : 'primary'}
                        />
                    </FormControl>
                    <Button
                        type="submit"
                        fullWidth
                        variant="contained"
                    >
                        Réinitialiser le mot de passe
                    </Button>
                </Box>
            </Card>
        </Box>
    )
}