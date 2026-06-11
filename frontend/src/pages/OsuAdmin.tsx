import { Box, Button, Card, CardContent, Dialog, DialogActions, DialogContent, DialogTitle, IconButton, Typography } from "@mui/material";
import { useCallback, useContext, useEffect, useState } from "react";
import { AccountCircle, Add, Delete, Edit } from "@mui/icons-material";
import type { Agent } from "../services/agentService.ts";
import { fetchOsuAdmin, patchDelegueDirecteurOsu, patchDirecteurOsu } from "../services/osuService.ts";
import { AgentAutocompleteField } from "../components/AgentAutocompleteField.tsx";
import { SnackbarContext } from "../context/snackbarContext.ts";
import type { AxiosError } from "axios";
import { AdminPageShell } from "../components/admin/AdminPageShell";

export default function OsuAdmin() {
    const [dirOsu, setDirOsu] = useState<Agent | null>(null);
    const [delegueDirecteurOsu, setDelegueDirecteurOsu] = useState<Agent[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [newDirecteur, setNewDirecteur] = useState<Agent | null>(null);
    const [isSaving, setIsSaving] = useState(false);
    const [isDelegueDialogOpen, setIsDelegueDialogOpen] = useState(false);
    const [newDelegue, setNewDelegue] = useState<Agent | null>(null);
    const [isSavingDelegue, setIsSavingDelegue] = useState(false);
    const [removingDelegueId, setRemovingDelegueId] = useState<number | null>(null);

    const snackbarContext = useContext(SnackbarContext);

    const fetchOsu = useCallback(async () => {
        setIsLoading(true);
        try {
            const { directeur_osu, delegue_directeur_osu } = await fetchOsuAdmin();
            setDirOsu(directeur_osu);
            setDelegueDirecteurOsu(delegue_directeur_osu ?? []);
        } catch (error) {
            console.error("Erreur lors du chargement des plateformes :", error);
        } finally {
            setIsLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchOsu();
    }, [fetchOsu]);

    useEffect(() => {
        setNewDirecteur(dirOsu);
    }, [dirOsu]);

    const handleSaveDirecteur = useCallback(async () => {
        setIsSaving(true);
        try {
            const { message } = await patchDirecteurOsu(newDirecteur?.id ?? null);
            snackbarContext?.trigger({ message: message, couleur: "success" });
            setIsDialogOpen(false);
            fetchOsu();
        } catch (error) {
            const axiosError = error as AxiosError<{ message: string }>;
            console.error("Erreur lors de la mise à jour du directeur de l'OSU :", error);
            snackbarContext?.trigger({ message: axiosError.response?.data.message || "Erreur lors de la mise à jour du directeur de l'OSU", couleur: "error" });
        } finally {
            setIsSaving(false);
        }
    }, [newDirecteur, snackbarContext, fetchOsu]);

    const handleAddDelegue = useCallback(async () => {
        if (!newDelegue?.id) {
            snackbarContext?.trigger({ message: "Sélectionnez un délégué avant de valider", couleur: "warning" });
            return;
        }

        setIsSavingDelegue(true);
        try {
            const { message } = await patchDelegueDirecteurOsu(newDelegue.id, "add");
            snackbarContext?.trigger({ message, couleur: "success" });
            setIsDelegueDialogOpen(false);
            setNewDelegue(null);
            await fetchOsu();
        } catch (error) {
            const axiosError = error as AxiosError<{ message: string }>;
            console.error("Erreur lors de l'ajout du délégué :", error);
            snackbarContext?.trigger({ message: axiosError.response?.data.message || "Erreur lors de l'ajout du délégué", couleur: "error" });
        } finally {
            setIsSavingDelegue(false);
        }
    }, [fetchOsu, newDelegue, snackbarContext]);

    const handleRemoveDelegue = useCallback(async (agentId: number) => {
        setRemovingDelegueId(agentId);
        try {
            const { message } = await patchDelegueDirecteurOsu(agentId, "remove");
            snackbarContext?.trigger({ message, couleur: "success" });
            await fetchOsu();
        } catch (error) {
            const axiosError = error as AxiosError<{ message: string }>;
            console.error("Erreur lors de la suppression du délégué :", error);
            snackbarContext?.trigger({ message: axiosError.response?.data.message || "Erreur lors de la suppression du délégué", couleur: "error" });
        } finally {
            setRemovingDelegueId(null);
        }
    }, [fetchOsu, snackbarContext]);

    return (
        <AdminPageShell
            title="Administration de l'OSU"
            subtitle="Gère le directeur et les délégués de direction OSU."
        >
            <Dialog open={isDialogOpen} onClose={() => setIsDialogOpen(false)} fullWidth maxWidth="sm">
                <DialogTitle>Modifier le directeur de l'OSU</DialogTitle>
                <DialogContent>
                    <Typography sx={{ mb: 2 }}>
                        Sélectionnez un agent ou videz le champ pour retirer le directeur actuel.
                    </Typography>
                    <AgentAutocompleteField
                        multiple={false}
                        label="Directeur de l'OSU"
                        value={newDirecteur}
                        onChange={(value: Agent | null) => setNewDirecteur(value)}
                        enabled={isDialogOpen}
                    />
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setIsDialogOpen(false)} disabled={isSaving}>Annuler</Button>
                    <Button variant="contained" onClick={() => void handleSaveDirecteur()} disabled={isSaving}>Enregistrer</Button>
                </DialogActions>
            </Dialog>
            <Dialog open={isDelegueDialogOpen} onClose={() => setIsDelegueDialogOpen(false)} fullWidth maxWidth="sm">
                <DialogTitle>Ajouter un délégué du directeur de l'OSU</DialogTitle>
                <DialogContent>
                    <Typography sx={{ mb: 2 }}>
                        Sélectionnez un agent à ajouter comme délégué du directeur.
                    </Typography>
                    <AgentAutocompleteField
                        multiple={false}
                        label="Délégué du directeur de l'OSU"
                        value={newDelegue}
                        onChange={(value: Agent | null) => setNewDelegue(value)}
                        enabled={isDelegueDialogOpen}
                    />
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setIsDelegueDialogOpen(false)} disabled={isSavingDelegue}>Annuler</Button>
                    <Button variant="contained" onClick={() => void handleAddDelegue()} disabled={isSavingDelegue}>Ajouter</Button>
                </DialogActions>
            </Dialog>
            {isLoading ? (
                <Typography>Chargement en cours...</Typography>
            ) : (
                <Box>
                    <Card
                        sx={{
                            mb: 4,
                            background: (theme) =>
                                `linear-gradient(90deg, ${theme.palette.primary.light} 0%, ${theme.palette.common.white} 100%)`
                        }}
                    >
                        <CardContent>
                            {dirOsu ? (
                                <>
                                    <Box sx={{ mb: 2 }}>
                                        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 2 }}>
                                            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                                                <AccountCircle />
                                                <Typography variant="h6">{dirOsu.prenom} {dirOsu.nom} | {dirOsu.email}</Typography>
                                            </Box>
                                            <IconButton onClick={() => setIsDialogOpen(true)} aria-label="Modifier le directeur de l'OSU">
                                                <Edit />
                                            </IconButton>
                                        </Box>
                                        <Typography sx={{ fontWeight: 'bold', ml: 4 }}>Directeur de l'OSU</Typography>
                                    </Box>
                                </>
                            ) : (
                                <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 2 }}>
                                    <Typography sx={{ mt: 1 }}>Aucun directeur défini</Typography>
                                    <IconButton onClick={() => setIsDialogOpen(true)} aria-label="Modifier le directeur de l'OSU">
                                        <Edit />
                                    </IconButton>
                                </Box>
                            )}
                        </CardContent>
                    </Card>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 2, mb: 2 }}>
                        <Typography variant="h6">Délégués du directeur de l'OSU</Typography>
                        <Button variant="contained" startIcon={<Add />} onClick={() => setIsDelegueDialogOpen(true)}>
                            Ajouter un délégué
                        </Button>
                    </Box>
                    {delegueDirecteurOsu.length > 0 ? (
                        delegueDirecteurOsu.map((delegue) => (
                            <Card
                                key={delegue.id}
                                sx={{
                                    mb: 2,
                                    background: (theme) =>
                                        `linear-gradient(90deg, ${theme.palette.secondary.light} 0%, ${theme.palette.common.white} 100%)`
                                }}
                            >
                                <CardContent>
                                    <Box sx={{ mb: 1 }}>
                                        <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 2 }}>
                                            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                                                <AccountCircle />
                                                <Typography variant="h6">{delegue.prenom} {delegue.nom} | {delegue.email}</Typography>
                                            </Box>
                                            <IconButton
                                                onClick={() => {
                                                    if (delegue.id) {
                                                        void handleRemoveDelegue(delegue.id);
                                                    }
                                                }}
                                                disabled={removingDelegueId === delegue.id}
                                                aria-label="Retirer le délégué"
                                            >
                                                <Delete />
                                            </IconButton>
                                        </Box>
                                        <Typography sx={{ fontWeight: 'bold', ml: 4 }}>Délégué du directeur de l'OSU</Typography>
                                    </Box>
                                </CardContent>
                            </Card>
                        ))
                    ) : (
                        <Typography>Aucun délégué défini</Typography>
                    )}
                </Box>
            )}
        </AdminPageShell>
    );
}