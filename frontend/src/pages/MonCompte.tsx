import { Alert, Box, Button, Card, CardContent, CircularProgress, Container, Stack, Typography } from "@mui/material";
import type { AxiosError } from "axios";
import { useCallback, useContext, useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { AgentAdvancedFields } from "../components/AgentAdvancedFields";
import { AuthContext } from "../context/authContext";
import { SnackbarContext } from "../context/snackbarContext";
import { getAgentByPage, modifierAgent, type Agent, type PayloadSoumettreAgentAuDu } from "../services/agentService";
import { refreshAccessToken, type ApiError } from "../utils/axios";

function buildPayload(agent: Agent): PayloadSoumettreAgentAuDu {
    return {
        nom: agent.nom,
        prenom: agent.prenom,
        email: agent.email,
        employeur_id: agent.employeur_id ?? null,
        corps_id: agent.corps_id ?? null,
        grade_id: agent.grade_id ?? null,
        type_contrat_id: agent.type_contrat_id ?? null,
        unite_id: agent.unite_id ?? null,
        doctorant: agent.doctorant ?? false,
        universite_rattachement_id: agent.universite_rattachement_id ?? null,
        baps_ids: agent.baps_ids ?? [],
        emploi_types_ids: agent.emploi_types_ids ?? [],
        section_cnrs_id: agent.section_cnrs_id ?? null,
        cnu_id: agent.cnu_id ?? null,
    };
}

export default function MonCompte() {
    const authContext = useContext(AuthContext);
    const snackbar = useContext(SnackbarContext);
    const navigate = useNavigate();

    const [agent, setAgent] = useState<Agent | null>(null);
    const [editableAgent, setEditableAgent] = useState<Agent | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isSaving, setIsSaving] = useState(false);
    const [loadError, setLoadError] = useState<string | null>(null);

    const hasChanges = useMemo(() => {
        if (!agent || !editableAgent) {
            return false;
        }

        return JSON.stringify(buildPayload(agent)) !== JSON.stringify(buildPayload(editableAgent));
    }, [agent, editableAgent]);

    const fetchMonCompte = useCallback(async () => {
        if (!authContext?.estConnecte || !authContext.email) {
            setIsLoading(false);
            return;
        }

        setIsLoading(true);
        setLoadError(null);

        try {
            const { agents } = await getAgentByPage(authContext.email);
            const normalizedEmail = authContext.email.toLowerCase();
            const currentAgent = agents.find((item) => item.id === authContext.id)
                ?? agents.find((item) => item.email.toLowerCase() === normalizedEmail)
                ?? null;

            if (!currentAgent) {
                setLoadError("Impossible de retrouver votre profil utilisateur.");
                return;
            }

            setAgent(currentAgent);
            setEditableAgent(currentAgent);
            await refreshAccessToken();
        } catch {
            setLoadError("Le chargement de votre profil a échoué.");
        } finally {
            setIsLoading(false);
        }
    }, [authContext?.email, authContext?.estConnecte, authContext?.id]);

    useEffect(() => {
        void fetchMonCompte();
    }, [fetchMonCompte]);

    async function handleSave() {
        if (!editableAgent?.id) {
            return;
        }

        setIsSaving(true);
        try {
            const result = await modifierAgent(editableAgent.id, buildPayload(editableAgent));
            setAgent(result.utilisateur);
            setEditableAgent(result.utilisateur);
            await refreshAccessToken();
            snackbar?.trigger({
                message: "Vos informations ont été mises à jour.",
                couleur: "success",
            });
        } catch (error) {
            const axiosError = error as AxiosError<ApiError>;
            snackbar?.trigger({
                message: axiosError.response?.data.message ?? "La mise à jour du profil a échoué.",
                couleur: "error",
            });
        } finally {
            setIsSaving(false);
        }
    }

    function handleReset() {
        setEditableAgent(agent);
    }

    if (!authContext?.estConnecte) {
        return (
            <Container sx={{ py: 4 }}>
                <Alert
                    severity="info"
                    action={
                        <Button color="inherit" size="small" onClick={() => navigate("/connexion")}>
                            Connexion
                        </Button>
                    }
                >
                    Vous devez être connecté pour accéder à votre compte.
                </Alert>
            </Container>
        );
    }

    return (
        <Container maxWidth="md" sx={{ py: 4 }}>
            <Stack spacing={3}>
                <Box>
                    <Typography variant="h4" component="h1" gutterBottom>
                        Mon compte
                    </Typography>
                    <Typography variant="body1" color="text.secondary">
                        Modifiez vos informations personnelles et professionnelles.
                    </Typography>
                </Box>

                <Card elevation={2}>
                    <CardContent>
                        {isLoading ? (
                            <Box sx={{ display: "flex", justifyContent: "center", py: 6 }}>
                                <CircularProgress />
                            </Box>
                        ) : loadError ? (
                            <Stack spacing={2}>
                                <Alert severity="error">{loadError}</Alert>
                                <Box>
                                    <Button variant="outlined" onClick={() => void fetchMonCompte()}>
                                        Réessayer
                                    </Button>
                                </Box>
                            </Stack>
                        ) : editableAgent ? (
                            <Stack spacing={3}>
                                <AgentAdvancedFields
                                    value={editableAgent}
                                    onChange={setEditableAgent}
                                    disabled={isSaving}
                                    emailReadOnly
                                />

                                <Stack direction={{ xs: "column", sm: "row" }} spacing={2} sx={{ justifyContent: "flex-end" }}>
                                    <Button variant="text" color="inherit" onClick={handleReset} disabled={isSaving || !hasChanges}>
                                        Annuler les modifications
                                    </Button>
                                    <Button variant="contained" onClick={() => void handleSave()} disabled={isSaving || !hasChanges}>
                                        Enregistrer
                                    </Button>
                                </Stack>
                            </Stack>
                        ) : (
                            <Alert severity="warning">Aucune information de profil n&apos;a été trouvée.</Alert>
                        )}
                    </CardContent>
                </Card>
            </Stack>
        </Container>
    );
}
