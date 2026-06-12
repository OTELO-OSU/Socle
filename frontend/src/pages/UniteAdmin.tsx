import { Alert, Box, Button, Card, CardContent, Dialog, DialogActions, DialogContent, DialogTitle, IconButton, Typography } from "@mui/material";
import { useCallback, useContext, useEffect, useState } from "react";
import { fetchUnite, patchDirecteurUnite, switchDelegueDirOsu, type UniteDetail } from "../services/uniteService.ts";
import { formatUniteLabel } from "../services/formatLabelService.ts";
import { Add, Delete, Edit, SupervisorAccount } from "@mui/icons-material";
import { AgentAutocompleteField } from "../components/AgentAutocompleteField.tsx";
import type { Agent } from "../services/agentService.ts";
import { SnackbarContext } from "../context/snackbarContext.ts";
import type { AxiosError } from "axios";
import { AdminPageShell } from "../components/admin/AdminPageShell";

type UniteCardProps = {
    unite: UniteDetail;
    onUpdated: () => Promise<void>;
};

function UniteCard({ unite, onUpdated }: UniteCardProps) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [newDirecteur, setNewDirecteur] = useState<Agent | null>(unite.directeur ?? null);
    const [isSaving, setIsSaving] = useState(false);
    const [isDelegueDialogOpen, setIsDelegueDialogOpen] = useState(false);
    const [newDelegue, setNewDelegue] = useState<Agent | null>(null);
    const [isSavingDelegue, setIsSavingDelegue] = useState(false);
    const [removingDelegueId, setRemovingDelegueId] = useState<number | null>(null);

    const snackbarContext = useContext(SnackbarContext);

    useEffect(() => {
        setNewDirecteur(unite.directeur ?? null);
    }, [unite.directeur]);

    const patchDirecteur = async (directeur: Agent | null) => {
        setIsSaving(true);
        try {
            await patchDirecteurUnite(unite.id, directeur?.id ?? null);
            await onUpdated();
            snackbarContext?.trigger({ message: "Directeur mis à jour avec succès", couleur: "success" });
            setIsDialogOpen(false);
        } catch (error) {
            console.error("Erreur lors de la mise à jour du directeur :", error);
            snackbarContext?.trigger({ message: "Erreur lors de la mise à jour du directeur", couleur: "error" });
        } finally {
            setIsSaving(false);
        }
    };

    const handleAddDelegue = useCallback(async () => {
        if (!newDelegue?.id) {
            snackbarContext?.trigger({ message: "Sélectionnez un délégué avant de valider", couleur: "warning" });
            return;
        }

        setIsSavingDelegue(true);
        try {
            const { message } = await switchDelegueDirOsu(newDelegue.id, unite.id, "add");
            snackbarContext?.trigger({ message, couleur: "success" });
            setIsDelegueDialogOpen(false);
            setNewDelegue(null);
            await onUpdated();
        } catch (error) {
            const axiosError = error as AxiosError<{ message: string }>;
            console.error("Erreur lors de l'ajout du délégué du DU :", error);
            snackbarContext?.trigger({ message: axiosError.response?.data.message || "Erreur lors de l'ajout du délégué", couleur: "error" });
        } finally {
            setIsSavingDelegue(false);
        }
    }, [newDelegue, onUpdated, snackbarContext, unite.id]);

    const handleRemoveDelegue = useCallback(async (agentId: number) => {
        setRemovingDelegueId(agentId);
        try {
            const { message } = await switchDelegueDirOsu(agentId, unite.id, "remove");
            snackbarContext?.trigger({ message, couleur: "success" });
            await onUpdated();
        } catch (error) {
            const axiosError = error as AxiosError<{ message: string }>;
            console.error("Erreur lors de la suppression du délégué du DU :", error);
            snackbarContext?.trigger({ message: axiosError.response?.data.message || "Erreur lors de la suppression du délégué", couleur: "error" });
        } finally {
            setRemovingDelegueId(null);
        }
    }, [onUpdated, snackbarContext, unite.id]);

    return (
        <>
            <Dialog open={isDialogOpen} onClose={() => setIsDialogOpen(false)}>
                <DialogTitle>Modifier l'unité</DialogTitle>
                <DialogContent>
                    <Typography sx={{ mb: 2 }}>Changer le directeur de l'unité {unite.nom}</Typography>
                    <AgentAutocompleteField multiple={false} label="Directeur" value={newDirecteur} onChange={(value: Agent | null) =>
                        setNewDirecteur(value)
                    } />
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setIsDialogOpen(false)} disabled={isSaving}>Annuler</Button>
                    <Button variant="contained" disabled={isSaving} onClick={() => {
                        void patchDirecteur(newDirecteur);
                    }}>Enregistrer</Button>
                </DialogActions>
            </Dialog>
            <Dialog open={isDelegueDialogOpen} onClose={() => setIsDelegueDialogOpen(false)} fullWidth maxWidth="sm">
                <DialogTitle>Ajouter un délégué du DU</DialogTitle>
                <DialogContent>
                    <Typography sx={{ mb: 2 }}>
                        Sélectionnez un agent à ajouter comme délégué du directeur de l'unité.
                    </Typography>
                    <AgentAutocompleteField
                        multiple={false}
                        label="Délégué du directeur"
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
            <Card>
                <CardContent>
                    <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 2, mb: 1 }}>
                        <Typography variant="h6">{formatUniteLabel(unite)}</Typography>
                        <IconButton onClick={() => setIsDialogOpen(true)}><Edit /></IconButton>
                    </Box>
                    <Typography variant="body2" color="textSecondary" sx={{ mb: 2 }}>
                        {unite.codeUnite}
                    </Typography>
                    {unite.directeur_id ? (
                        <Typography variant="body1" color="textPrimary" sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                            <SupervisorAccount />
                            Directeur d'unité : {unite.directeur?.prenom} {unite.directeur?.nom} | {unite.directeur?.email}
                        </Typography>
                    ) : (<Alert severity="warning">Aucun directeur assigné</Alert>)}
                    <Box sx={{ mt: 2 }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 2, mb: 1 }}>
                            <Typography variant="subtitle1">Délégués du DU</Typography>
                            <Button size="small" variant="contained" startIcon={<Add />} onClick={() => setIsDelegueDialogOpen(true)}>
                                Ajouter un délégué
                            </Button>
                        </Box>
                        {(unite.delegues ?? []).length > 0 ? (
                            <Box sx={{ display: 'flex', flexDirection: 'column', gap: 1 }}>
                                {(unite.delegues ?? []).map((delegue) => (
                                    <Box
                                        key={delegue.id}
                                        sx={{
                                            border: '1px solid',
                                            borderColor: 'divider',
                                            borderRadius: 1,
                                            p: 1,
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'space-between',
                                            gap: 2,
                                        }}
                                    >
                                        <Typography variant="body2">
                                            {delegue.prenom} {delegue.nom} | {delegue.email}
                                        </Typography>
                                        <IconButton
                                            onClick={() => {
                                                if (delegue.id) {
                                                    void handleRemoveDelegue(delegue.id);
                                                }
                                            }}
                                            disabled={removingDelegueId === delegue.id}
                                            aria-label="Retirer le délégué"
                                        >
                                            <Delete fontSize="small" />
                                        </IconButton>
                                    </Box>
                                ))}
                            </Box>
                        ) : (
                            <Typography variant="body2" color="textSecondary">Aucun délégué défini</Typography>
                        )}
                    </Box>
                </CardContent>
            </Card>
        </>
    );
}

export default function UniteAdmin() {
    const [unites, setUnites] = useState<UniteDetail[]>([]);
    const [isLoading, setIsLoading] = useState(false);

    const fetchUnites = useCallback(async () => {
        setIsLoading(true);
        try {
            const unites = await fetchUnite();
            setUnites(unites);
        } catch (error) {
            console.error("Erreur lors du chargement des unités :", error);
        } finally {
            setIsLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchUnites();
    }, [fetchUnites]);

    return (
        <AdminPageShell
            title="Gestion des unités"
            subtitle="Configure les directeurs et délégués pour chaque unité."
        >

            {isLoading ? (
                <Typography component={'p'} variant="h6">Chargement...</Typography>
            ) : (
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                    {unites.map((unite) => (
                        <UniteCard key={unite.id} unite={unite} onUpdated={fetchUnites} />
                    ))}
                </Box>
            )}
        </AdminPageShell>
    )
}