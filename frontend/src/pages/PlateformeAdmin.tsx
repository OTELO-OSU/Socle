import { Box, Button, Card, CardActions, CardContent, Dialog, FormControl, FormLabel, List, Pagination, Stack, TextField, Typography } from "@mui/material";
import { useCallback, useContext, useEffect, useState } from "react";
import { creerPlateforme, deletePlateforme, fetchPlateformeListeParPage, patchResponsablePlateforme, type PlateformeLight } from "../services/plateformeService";
import { SnackbarContext } from "../context/snackbarContext";
import { Link as LinkRouter, useNavigate } from "react-router-dom";
import { Delete, Description, Download, Group, Launch, ManageAccounts, Verified } from "@mui/icons-material";
import type { Agent } from "../services/agentService";
import type { AxiosError } from "axios";
import type { ApiError } from "../utils/axios";
import { AgentAutocompleteField } from "../components/AgentAutocompleteField.tsx";
import { AdminPageShell, AdminSurface } from "../components/admin/AdminPageShell";
import { downloadSinglePlateforme } from "../services/exportService.ts";

type PlateformeAdminCardProps = {
    plateforme: PlateformeLight;
    onEditPlateforme: () => void;
    onDeletePlateforme: () => void;
}

function PlateformeAdminCard({ plateforme, onEditPlateforme, onDeletePlateforme }: PlateformeAdminCardProps) {
    const personnel = plateforme.dernier_historique.personnels

    const responsableScientifique = personnel.filter(p => p.responsable_scientifique);
    const responsableTechnique = personnel.filter(p => p.responsable_technique);
    const responsablePlateforme = personnel.filter(p => p.responsable_plateforme);



    return (
        <>
            <Card key={plateforme.id}>
                <CardContent>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                        <Box>
                            <Typography variant="h6" sx={{ display: 'flex', alignItems: 'center', gap: 0.5, fontWeight: 'bold' }}>{plateforme.dernier_historique.nom_officiel}</Typography>
                            {responsableScientifique.length > 0 && (
                                <>
                                    <Box sx={{ mt: 2 }}>
                                        <Typography variant="body1" color="textPrimary" sx={{ display: 'flex', alignItems: 'center', gap: 0.5, fontWeight: "medium" }}>
                                            <Group />
                                            Responsable Scientifique :
                                        </Typography>
                                        <List sx={{ px: 4 }}>
                                            {responsableScientifique.map((personnel, index) => (
                                                <Box component="li" key={`${personnel.utilisateur_id}-${index}`}>
                                                    <Typography variant="body2" color="textPrimary">
                                                        {personnel.utilisateur.prenom} {personnel.utilisateur.nom} | {personnel.utilisateur.email}
                                                    </Typography>
                                                </Box>
                                            ))}
                                        </List>
                                    </Box>
                                </>)}
                            {responsableTechnique.length > 0 && (
                                <>
                                    <Box sx={{ mt: 2 }}>
                                        <Typography variant="body1" color="textPrimary" sx={{ display: 'flex', alignItems: 'center', gap: 0.5, fontWeight: "medium" }}>
                                            <Group />
                                            Responsable Technique :
                                        </Typography>
                                        <List sx={{ px: 4 }}>
                                            {responsableTechnique.map((personnel, index) => (
                                                <Box component="li" key={`${personnel.utilisateur_id}-${index}`}>
                                                    <Typography variant="body2" color="textPrimary">
                                                        {personnel.utilisateur.prenom} {personnel.utilisateur.nom} | {personnel.utilisateur.email}
                                                    </Typography>
                                                </Box>
                                            ))}
                                        </List>
                                    </Box>
                                </>)}
                            {responsablePlateforme.length > 0 && (
                                <>
                                    <Box sx={{ mt: 2 }}>
                                        <Typography variant="body1" color="textPrimary" sx={{ display: 'flex', alignItems: 'center', gap: 0.5, fontWeight: "medium" }}>
                                            <Group />
                                            Responsable Plateforme :
                                        </Typography>
                                        <List sx={{ px: 4 }}>
                                            {responsablePlateforme.map((personnel, index) => (
                                                <Box component="li" key={`${personnel.utilisateur_id}-${index}`}>
                                                    <Typography variant="body2" color="textPrimary">
                                                        {personnel.utilisateur.prenom} {personnel.utilisateur.nom} | {personnel.utilisateur.email}
                                                    </Typography>
                                                </Box>
                                            ))}
                                        </List>
                                    </Box>
                                </>)}
                        </Box>
                        {plateforme.dernier_historique.logo_url &&
                            <Box>
                                <img src={plateforme.dernier_historique.logo_url} alt={`${plateforme.dernier_historique.nom_officiel} logo`} style={{ maxHeight: 126 }} />
                            </Box>
                        }
                    </Box>
                </CardContent>
                <CardActions>
                    <Button component={LinkRouter} to={`/plateforme/edition?id=${plateforme.id}`} color="primary" sx={{ cursor: 'pointer' }}>
                        <Launch fontSize="small" sx={{ mr: 0.5 }} />
                        Voir les détails
                    </Button>
                    <Button onClick={() => downloadSinglePlateforme({ plateformeId: plateforme.id, harvester: false, host: "" })} color="primary" sx={{ cursor: 'pointer' }}>
                        <Download fontSize="small" sx={{ mr: 0.5 }} />
                        Exporter
                    </Button>
                    <Button onClick={() => onEditPlateforme()} color="primary" sx={{ cursor: 'pointer' }}>
                        <ManageAccounts fontSize="small" sx={{ mr: 0.5 }} />
                        Modifier les responsables
                    </Button>
                    <Button onClick={() => onDeletePlateforme()} color="primary" sx={{ cursor: 'pointer' }}>
                        <Delete fontSize="small" sx={{ mr: 0.5 }} />
                        Supprimer la plateforme
                    </Button>
                </CardActions>
            </Card >
        </>
    )
}

export default function PlateformeAdmin() {
    const [plateformes, setPlateformes] = useState<PlateformeLight[]>([]);
    const [recherche, setRecherche] = useState('');
    const [page, setPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [isLoading, setIsLoading] = useState(false);

    const fetchPlateformes = useCallback(async () => {
        setIsLoading(true);
        try {
            const { plateformes, pages_total } = await fetchPlateformeListeParPage(page, recherche);
            setPlateformes(plateformes);
            setTotalPages(pages_total);
        } catch (error) {
            console.error("Erreur lors du chargement des plateformes :", error);
        } finally {
            setIsLoading(false);
        }
    }, [page, recherche]);

    useEffect(() => {
        fetchPlateformes();
    }, [fetchPlateformes]);

    const [dialogOpen, setDialogOpen] = useState(false);
    const [editDialogOpen, setEditDialogOpen] = useState(false);
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const snackbar = useContext(SnackbarContext);
    const navigation = useNavigate();

    async function handleSubmitCreatePlateforme(event: React.SubmitEvent<HTMLFormElement>) {
        event.preventDefault();
        const data = new FormData(event.currentTarget);
        const nom = data.get('nom') as string;

        try {
            const { message, plateforme_id } = await creerPlateforme(nom);
            setDialogOpen(false);
            snackbar?.trigger({
                message: message,
                couleur: "success",
            })
            navigation(`/plateforme/edition?id=${plateforme_id}`);
        } catch (error) {
            const errorAxios = error as AxiosError<ApiError>;
            snackbar?.trigger({
                message: errorAxios.response!.data.message,
                couleur: "error",
            })

        }

    }

    async function handleSubmitDeletePlateforme(event: React.SubmitEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!selectedPlateforme) {
            return;
        }

        try {
            const { message } = await deletePlateforme(selectedPlateforme.id);
            snackbar?.trigger({
                message,
                couleur: "success",
            });
            await fetchPlateformes();
            handleCloseDeleteDialog();
        } catch (error) {
            const errorAxios = error as AxiosError<ApiError>;
            snackbar?.trigger({
                message: errorAxios.response!.data.message,
                couleur: "error",
            });
        }
    }

    const [selectedPlateforme, setSelectedPlateforme] = useState<PlateformeLight | null>(null);
    const [responsablesScientifiques, setResponsablesScientifiques] = useState<Agent[]>([]);
    const [responsablesTechniques, setResponsablesTechniques] = useState<Agent[]>([]);
    const [responsablesPlateforme, setResponsablesPlateforme] = useState<Agent[]>([]);

    function handleModifyPlateforme(plateforme: PlateformeLight) {

        setSelectedPlateforme(plateforme);

        setResponsablesScientifiques(
            plateforme.dernier_historique.personnels
                .filter((personnel) => personnel.responsable_scientifique)
                .map((personnel) => personnel.utilisateur)
        );
        setResponsablesTechniques(
            plateforme.dernier_historique.personnels
                .filter((personnel) => personnel.responsable_technique)
                .map((personnel) => personnel.utilisateur)
        );
        setResponsablesPlateforme(
            plateforme.dernier_historique.personnels
                .filter((personnel) => personnel.responsable_plateforme)
                .map((personnel) => personnel.utilisateur)
        );
        setEditDialogOpen(true);
    }

    function handleDeletePlateforme(plateforme: PlateformeLight) {
        setSelectedPlateforme(plateforme);
        setDeleteDialogOpen(true);
    }

    function handleCloseDeleteDialog() {
        setSelectedPlateforme(null);
        setDeleteDialogOpen(false);
    }

    function handleCloseEditDialog() {
        setEditDialogOpen(false);
        setSelectedPlateforme(null);
        setResponsablesScientifiques([]);
        setResponsablesTechniques([]);
        setResponsablesPlateforme([]);
    }

    async function handleSaveEditDialog() {
        try {
            await patchResponsablePlateforme(
                selectedPlateforme!.id,
                responsablesScientifiques.map((a) => a.id!),
                responsablesTechniques.map((a) => a.id!),
                responsablesPlateforme.map((a) => a.id!)
            );

            snackbar?.trigger({
                message: "Responsables de la plateforme mis à jour avec succès",
                couleur: "success",
            });
        } catch (error) {
            const errorAxios = error as AxiosError<ApiError>;
            snackbar?.trigger({
                message: errorAxios.response!.data.message,
                couleur: "error",
            });
        }

        await fetchPlateformes();
        handleCloseEditDialog();
    }

    return (
        <AdminPageShell
            title="Gestion des plateformes"
            subtitle="Administre les responsables et l'édition des plateformes."
            action={
                <Button variant="contained" color="primary" onClick={() => setDialogOpen(true)}>
                    Créer une nouvelle plateforme
                </Button>
            }
        >
            <Dialog open={dialogOpen} onClose={() => setDialogOpen(false)}>
                <Box component={"form"} sx={{ p: 3, display: 'flex', flexDirection: 'column', gap: 2 }} onSubmit={handleSubmitCreatePlateforme}>
                    <Typography variant="h4" component="h2">Créer une Plateforme</Typography>
                    <FormControl fullWidth>
                        <FormLabel htmlFor="nom">Nom de la plateforme</FormLabel>
                        <TextField autoFocus type="text" id="nom" name="nom" required />
                    </FormControl>
                    <Button type="submit" variant="contained" color="primary" sx={{ mt: 2 }}>
                        Créer
                    </Button>
                </Box>
            </Dialog>
            <Dialog open={deleteDialogOpen} onClose={handleCloseDeleteDialog}>
                <Box component={"form"} sx={{ p: 3, display: 'flex', flexDirection: 'column', gap: 2 }} onSubmit={handleSubmitDeletePlateforme}>
                    <Typography variant="h4" component="h2">Supprimer une Plateforme</Typography>
                    <Typography variant="body1">
                        Êtes-vous sûr de vouloir supprimer {selectedPlateforme?.dernier_historique.nom_officiel ?? "N/A"} ?
                    </Typography>
                    <Button type="submit" variant="contained" color="primary" sx={{ mt: 2 }}>
                        Supprimer
                    </Button>
                </Box>
            </Dialog>
            <Dialog open={editDialogOpen} onClose={handleCloseEditDialog} fullWidth maxWidth="sm">
                <Box sx={{ p: 3, display: 'flex', flexDirection: 'column', gap: 2 }}>
                    <Typography variant="h4" component="h2">Modifier les responsables</Typography>
                    <Typography variant="body1">
                        Responsables de la plateforme : {selectedPlateforme?.dernier_historique.nom_officiel ?? "N/A"}
                    </Typography>
                    <AgentAutocompleteField
                        multiple={true}
                        label="Responsables scientifiques"
                        value={responsablesScientifiques}
                        onChange={setResponsablesScientifiques}
                        enabled={editDialogOpen}
                    />
                    <AgentAutocompleteField
                        multiple={true}
                        label="Responsables techniques"
                        value={responsablesTechniques}
                        onChange={setResponsablesTechniques}
                        enabled={editDialogOpen}
                    />
                    <AgentAutocompleteField
                        multiple={true}
                        label="Responsables de la plateforme"
                        value={responsablesPlateforme}
                        onChange={setResponsablesPlateforme}
                        enabled={editDialogOpen}
                    />
                    <Stack direction="row" spacing={2} sx={{ justifyContent: 'flex-end' }}>
                        <Button color="inherit" onClick={handleCloseEditDialog}>Annuler</Button>
                        <Button onClick={handleSaveEditDialog}>Sauvegarder</Button>
                    </Stack>
                </Box>
            </Dialog>
            <AdminSurface>
                <Stack direction="row" spacing={2} sx={{ my: 0, alignItems: "center", justifyContent: "space-between" }}>
                    <TextField
                        type="text"
                        placeholder="Rechercher une plateforme..."
                        value={recherche}
                        onChange={(e) => setRecherche(e.target.value)}
                        sx={{ width: '35%' }}
                    />
                    <Pagination
                        count={totalPages}
                        page={page}
                        onChange={(_, newPage) => setPage(newPage)}
                    />
                </Stack>

                {isLoading ? (
                    <Typography component={'p'} variant="h6" sx={{ mt: 2 }}>Chargement...</Typography>
                ) : (
                    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2, mt: 2 }}>
                        {plateformes.map((plateforme) => (
                            <PlateformeAdminCard
                                key={plateforme.id}
                                plateforme={plateforme}
                                onEditPlateforme={() => {
                                    handleModifyPlateforme(plateforme);
                                }}
                                onDeletePlateforme={() => {
                                    handleDeletePlateforme(plateforme);
                                }}
                            />
                        ))}
                    </Box>
                )}
                <Stack direction="row" spacing={1} sx={{ mt: 2, alignItems: "center" }}>
                    <Button onClick={() => setPage((prev) => Math.max(prev - 1, 1))} disabled={page === 1}>
                        Précédent
                    </Button>
                    <Typography variant="body2" component={'span'}> Page {page} sur {totalPages} </Typography>
                    <Button onClick={() => setPage((prev) => Math.min(prev + 1, totalPages))} disabled={page === totalPages}>
                        Suivant
                    </Button>
                </Stack>
            </AdminSurface>
        </AdminPageShell>
    )
}