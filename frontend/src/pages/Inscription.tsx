import { Autocomplete, Box, Button, Card, FormControl, FormHelperText, FormLabel, InputLabel, Link, MenuItem, Select, Stack, TextField, Typography } from "@mui/material";
import { useContext, useEffect, useMemo, useState } from "react";
import type { AxiosError } from "axios";
import { SnackbarContext } from "../context/snackbarContext";
import type { ApiError } from "../utils/axios";
import { useNavigate, Link as RouterLink, useSearchParams } from "react-router-dom";
import { inscription, inscriptionSsMdp } from "../services/authService";
import { DictionnaireContext } from "../context/dictionnaireContext";
import { formatUniteLabel } from "../services/formatLabelService";
import { fetchPlateformePublique } from "../services/plateformeService";

export default function Inscription() {
    const [prenomError, setPrenomError] = useState(false);
    const [prenomErrorMessage, setPrenomErrorMessage] = useState('');
    const [nomError, setNomError] = useState(false);
    const [nomErrorMessage, setNomErrorMessage] = useState('');
    const [emailError, setEmailError] = useState(false);
    const [emailErrorMessage, setEmailErrorMessage] = useState('');
    const [unite, setUnite] = useState('');
    const [uniteError, setUniteError] = useState(false);
    const [uniteErrorMessage, setUniteErrorMessage] = useState('');
    const [plateforme, setPlateforme] = useState<number | null>(null);
    const [plateformeError, setPlateformeError] = useState(false);
    const [plateformeErrorMessage, setPlateformeErrorMessage] = useState('');
    const [plateformes, setPlateformes] = useState<{ id: number; nom: string }[]>([]);
    const [role, setRole] = useState('');
    const [roleError, setRoleError] = useState(false);
    const [roleErrorMessage, setRoleErrorMessage] = useState('');
    const [mdpError, setMdpError] = useState(false);
    const [mdpErrorMessage, setMdpErrorMessage] = useState('');
    const [confirmMdpError, setConfirmMdpError] = useState(false);
    const [confirmMdpErrorMessage, setConfirmMdpErrorMessage] = useState('');
    const snackbarContext = useContext(SnackbarContext);
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();

    const trigger = snackbarContext?.trigger;
    const token = searchParams.get("token");
    const ssMdp = searchParams.get("ssmdp");
    const dictionnaireContext = useContext(DictionnaireContext);

    const prefill = useMemo(() => {
        const payload = searchParams.get("payload");
        if (!payload) return null;

        try {
            return JSON.parse(atob(payload)) as { prenom?: string; nom?: string; email?: string };
        } catch {
            return null;
        }
    }, [searchParams]);

    useEffect(() => {
        // Fetch la liste de plateforme publique
        fetchPlateformePublique()
            .then((plateformes) => {
                if (plateformes.length > 0) {
                    const res = plateformes.map((plateforme) => ({
                        id: plateforme.id,
                        nom: plateforme.dernier_historique?.nom_officiel,
                    }));
                    setPlateformes(res);
                    console.log(res);

                }
            })
            .catch((error) => {
                console.error("Erreur lors de la récupération des plateformes publiques :", error);
            });

        const message = atob(searchParams.get("message") || "");
        if (message) {
            trigger?.({
                message: message,
                couleur: "success",
            });
        }

    }, [searchParams, trigger]);

    async function handleSubmit(event: React.SubmitEvent<HTMLFormElement>) {
        event.preventDefault();
        const data = new FormData(event.currentTarget);
        const email = data.get('email') as string;
        const mdp = data.get('mdp') as string;
        const confirmMdp = data.get('confirmMdp') as string;
        const prenom = data.get('prenom') as string;
        const nom = data.get('nom') as string;
        const selectedRole = parseInt(data.get('role') as string);
        const selectedUnite = parseInt(data.get('unite') as string);
        const selectedPlateforme = data.get('plateforme') as string;

        let hasError = false;

        const ssMdpBool = token && searchParams.get("ssmdp") === "1";

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

        if (!selectedPlateforme && selectedRole === 1) {
            setPlateformeError(true);
            setPlateformeErrorMessage('Plateforme requise');
            hasError = true;
        } else {
            setPlateformeError(false);
            setPlateformeErrorMessage('');
        }

        if (!selectedUnite) {
            setUniteError(true);
            setUniteErrorMessage('Unité requise');
            hasError = true;
        } else {
            setUniteError(false);
            setUniteErrorMessage('');
        }

        if (!selectedRole) {
            setRoleError(true);
            setRoleErrorMessage('Rôle requis');
            hasError = true;
        } else {
            setRoleError(false);
            setRoleErrorMessage('');
        }


        if (!ssMdpBool) {
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
        }

        if (hasError) {
            return;
        }

        try {
            if (!ssMdpBool) {
                const { data } = await inscription(prenom, nom, email, mdp, selectedRole, selectedUnite, plateforme);
                snackbarContext?.trigger({
                    message: data.message,
                    couleur: 'success',
                });
            } else {
                const { data } = await inscriptionSsMdp(prenom, nom, email, selectedRole, token, selectedUnite, plateforme);
                snackbarContext?.trigger({
                    message: data.message,
                    couleur: 'success',
                });
            }
            navigate('/');
        } catch (e) {
            const error = e as AxiosError<ApiError>;
            if (error.response?.status === 400) {
                snackbarContext?.trigger({
                    message: error.response.data?.messageArray.map((msg) => `- ${msg}`).join('\n') || 'Une erreur est survenue lors de l\'inscription',
                    couleur: 'error',
                });
            } else if (error.response?.status === 429) {
                snackbarContext?.trigger({
                    message: error.response.data?.message || 'Trop de tentatives d\'inscription. Veuillez réessayer plus tard.',
                    couleur: 'error',
                });
            }
            else if (error.response?.status === 409) {
                snackbarContext?.trigger({
                    message: error.response.data?.message || 'L\'email est déjà utilisé par un compte existant.',
                    couleur: 'error',
                });
            } else {
                snackbarContext?.trigger({
                    message: error.response?.data?.message || 'Une erreur est survenue lors de l\'inscription',
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
                                defaultValue={prefill?.prenom ?? ''}
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
                                defaultValue={prefill?.nom ?? ''}
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
                            defaultValue={prefill?.email ?? ''}
                            placeholder="alice.dubois@gmail.com"
                            autoComplete="email"
                            required
                            fullWidth
                            variant="outlined"
                            color={emailError ? 'error' : 'primary'}
                        />
                    </FormControl>
                    <FormControl fullWidth error={uniteError}>
                        <InputLabel id="unite-label">Unité</InputLabel>
                        <Select label="Unité" labelId="unite-label" id="unite" value={unite} onChange={(e) => setUnite(e.target.value)} name="unite">
                            {dictionnaireContext?.unite.map((unite) => (
                                <MenuItem key={unite.id} value={unite.id}>
                                    {formatUniteLabel(unite)}
                                </MenuItem>
                            ))}
                        </Select>
                        <FormHelperText>{uniteErrorMessage}</FormHelperText>
                    </FormControl>
                    <FormControl fullWidth error={roleError}>
                        <InputLabel id="role-label">Rôle</InputLabel>
                        <Select label="Rôle" labelId="role-label" id="role" value={role} onChange={(e) => setRole(e.target.value)} name="role">
                            <MenuItem value="1">Responsable de Plateforme</MenuItem>
                            <MenuItem value="2">Directeur d'Unité</MenuItem>
                        </Select>
                        <FormHelperText>{roleErrorMessage}</FormHelperText>
                    </FormControl>
                    {role === '1' && (
                        <FormControl fullWidth error={plateformeError}>
                            <Autocomplete options={plateformes} getOptionLabel={(option) => option.nom} onChange={(_, newValue) => setPlateforme(newValue!.id)} renderInput={(params) =>
                                <TextField {...params} name="plateforme" label="Plateforme" id="plateforme"></TextField>
                            } />
                            <FormHelperText>{plateformeErrorMessage}</FormHelperText>
                        </FormControl>
                    )}
                    {(ssMdp !== '1' || !token) &&
                        <>
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
                            </FormControl></>}
                    {
                        (ssMdp === '1' && token) ? <Button
                            type="submit"
                            fullWidth
                            variant="contained"
                        >
                            S'inscrire sans mot de passe
                        </Button> : <Button
                            type="submit"
                            fullWidth
                            variant="contained"
                        >
                            S'inscrire
                        </Button>
                    }
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