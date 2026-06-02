import { Alert, Box, Button, Card, CardContent, Container, Dialog, DialogActions, DialogContent, DialogTitle, IconButton, Typography } from "@mui/material";
import { useCallback, useContext, useEffect, useState } from "react";
import { fetchUnite, patchDirecteurUnite, type UniteDetail } from "../services/uniteService.ts";
import { formatUniteLabel } from "../services/formatLabelService.ts";
import { Edit, SupervisorAccount } from "@mui/icons-material";
import { AgentAutocompleteField } from "../components/AgentAutocompleteField.tsx";
import type { Agent } from "../services/agentService.ts";
import { SnackbarContext } from "../context/snackbarContext.ts";

type UniteCardProps = {
    unite: UniteDetail;
    onUpdated: () => Promise<void>;
};

function UniteCard({ unite, onUpdated }: UniteCardProps) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [newDirecteur, setNewDirecteur] = useState<Agent | null>(unite.directeur ?? null);
    const [isSaving, setIsSaving] = useState(false);

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
        <Container sx={{ py: 4 }}>
            <Typography variant="h5" component={"h1"} sx={{ mb: 2 }}>Liste des unités</Typography>

            {isLoading ? (
                <Typography component={'p'} variant="h6">Chargement...</Typography>
            ) : (
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                    {unites.map((unite) => (
                        <UniteCard key={unite.id} unite={unite} onUpdated={fetchUnites} />
                    ))}
                </Box>
            )}
        </Container>
    )
}