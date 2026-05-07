import { Box, Button, Card, CardActions, CardContent, Container, Dialog, Divider, FormControl, FormLabel, Link, Menu, MenuItem, Pagination, Stack, TextField, Typography } from "@mui/material";
import { useCallback, useContext, useEffect, useState } from "react";
import { creerPlateforme, fetchPlateformeListeParPage, patchResponsablePlateforme, type PlateformeLight } from "../services/plateformeService";
import { SnackbarContext } from "../context/snackbarContext";
import { Link as LinkRouter, useNavigate } from "react-router-dom";
import { MoreVert } from "@mui/icons-material";
import type { Agent } from "../services/agentService";
import type { AxiosError } from "axios";
import type { ApiError } from "../utils/axios";
import { AgentAutocompleteField } from "../components/AgentAutocompleteField.tsx";

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

    const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
    const [selectedPlateforme, setSelectedPlateforme] = useState<PlateformeLight | null>(null);
    const [responsablesScientifiques, setResponsablesScientifiques] = useState<Agent[]>([]);
    const [responsablesTechniques, setResponsablesTechniques] = useState<Agent[]>([]);
    const [responsablesPlateforme, setResponsablesPlateforme] = useState<Agent[]>([]);
    const menu = Boolean(anchorEl);
    const handleClick = (event: React.MouseEvent<HTMLButtonElement>, plateforme: PlateformeLight) => {
        setAnchorEl(event.currentTarget);
        setSelectedPlateforme(plateforme);
    };

    function handleCloseMenu() {
        setAnchorEl(null);
    }

    function handleModifyPlateforme() {
        if (!selectedPlateforme) {
            return;
        }

        setResponsablesScientifiques(
            selectedPlateforme.lastHistorique.plateformeHistoriquePersonnels
                .filter((personnel) => personnel.responsableScientifique)
                .map((personnel) => personnel.utilisateur)
        );
        setResponsablesTechniques(
            selectedPlateforme.lastHistorique.plateformeHistoriquePersonnels
                .filter((personnel) => personnel.responsableTechnique)
                .map((personnel) => personnel.utilisateur)
        );
        setResponsablesPlateforme(
            selectedPlateforme.lastHistorique.plateformeHistoriquePersonnels
                .filter((personnel) => personnel.responsablePlateforme)
                .map((personnel) => personnel.utilisateur)
        );
        setEditDialogOpen(true);
        handleCloseMenu();
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
                responsablesScientifiques.map((a) => a.id),
                responsablesTechniques.map((a) => a.id),
                responsablesPlateforme.map((a) => a.id)
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
        <Container sx={{ py: 4 }}>
            <Typography variant="h5" component={"h1"}>Liste des plateformes</Typography>
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
            <Dialog open={editDialogOpen} onClose={handleCloseEditDialog} fullWidth maxWidth="sm">
                <Box sx={{ p: 3, display: 'flex', flexDirection: 'column', gap: 2 }}>
                    <Typography variant="h4" component="h2">Modifier les responsables</Typography>
                    <Typography variant="body1">
                        Responsables de la plateforme : {selectedPlateforme?.lastHistorique.nomOfficiel ?? "N/A"}
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
            <Stack direction="row" divider={<Divider orientation="vertical" flexItem />} spacing={2} sx={{ my: 2, alignItems: "center" }}>
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
                    sx={{ width: '35%' }}
                />
                <Button variant="contained" color="primary" onClick={() => setDialogOpen(true)}>
                    Créer une nouvelle plateforme
                </Button>
            </Stack>

            {isLoading ? (
                <Typography component={'p'} variant="h6">Chargement...</Typography>
            ) : (
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                    {plateformes.map((plateforme) => (
                        <Card key={plateforme.id}>
                            <CardContent>
                                <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 2 }}>
                                    <Box>
                                        <Typography variant="h6">{plateforme.lastHistorique.nomOfficiel}</Typography>
                                        <Typography variant="body2" color="textSecondary">
                                            Année : {plateforme.lastHistorique.annee}
                                        </Typography>
                                    </Box>
                                    <Button
                                        color="inherit"
                                        aria-label="plus"
                                        aria-controls={menu ? 'menu' : undefined}
                                        aria-haspopup="true"
                                        onClick={(event) => handleClick(event, plateforme)}
                                    >
                                        <MoreVert />
                                    </Button>
                                    <Menu
                                        anchorEl={anchorEl}
                                        open={menu}
                                        onClose={handleCloseMenu}
                                    >
                                        <MenuItem onClick={handleModifyPlateforme}>Modifier</MenuItem>
                                        <MenuItem onClick={handleCloseMenu}>Supprimer</MenuItem>
                                    </Menu>
                                </Box>
                            </CardContent>
                            <CardActions>
                                <Link component={LinkRouter} to={`/plateforme/edition?id=${plateforme.lastHistorique.id}`} color="primary" sx={{ cursor: 'pointer' }}>
                                    Voir les détails
                                </Link>


                            </CardActions>
                        </Card>
                    ))}
                </Box>
            )}
            <Button onClick={() => setPage((prev) => Math.max(prev - 1, 1))} disabled={page === 1}>
                Précédent
            </Button>
            <Typography variant="body2" component={'span'}> Page {page} sur {totalPages} </Typography>
            <Button onClick={() => setPage((prev) => Math.min(prev + 1, totalPages))} disabled={page === totalPages}>
                Suivant
            </Button>
        </Container>
    )
}