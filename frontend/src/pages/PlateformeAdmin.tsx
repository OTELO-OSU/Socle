import { Autocomplete, Box, Button, Card, CardActions, CardContent, Container, Dialog, Divider, FormControl, FormLabel, Link, Menu, MenuItem, Pagination, Stack, TextField, Typography } from "@mui/material";
import { useCallback, useContext, useEffect, useState } from "react";
import { creerPlateforme, fetchPlateformeListeParPage, patchResponsablePlateforme, type PlateformeLight } from "../services/plateformeService";
import { SnackbarContext } from "../context/snackbarContext";
import { Link as LinkRouter, useNavigate } from "react-router-dom";
import { MoreVert } from "@mui/icons-material";
import { getAgentByPage, type Agent } from "../services/agentService";
import type { AxiosError } from "axios";
import type { ApiError } from "../utils/axios";

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

        const { message, plateforme_id } = await creerPlateforme(nom);
        setDialogOpen(false);
        snackbar?.trigger({
            message: message,
            couleur: "success",
        })
        navigation(`/plateforme/edition?id=${plateforme_id}`);
    }

    const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
    const [selectedPlateforme, setSelectedPlateforme] = useState<PlateformeLight | null>(null);
    const [responsablesScientifiques, setResponsablesScientifiques] = useState<Agent[]>([]);
    const [responsablesTechniques, setResponsablesTechniques] = useState<Agent[]>([]);
    const [responsablesPlateforme, setResponsablesPlateforme] = useState<Agent[]>([]);
    const [agentsOptionsScientifiques, setAgentsOptionsScientifiques] = useState<Agent[]>([]);
    const [agentsOptionsTechniques, setAgentsOptionsTechniques] = useState<Agent[]>([]);
    const [agentsOptionsPlateforme, setAgentsOptionsPlateforme] = useState<Agent[]>([]);
    const [scientifiquesRecherche, setScientifiquesRecherche] = useState('');
    const [techniquesRecherche, setTechniquesRecherche] = useState('');
    const [plateformeRecherche, setPlateformeRecherche] = useState('');
    const [debouncedScientifiquesRecherche, setDebouncedScientifiquesRecherche] = useState('');
    const [debouncedTechniquesRecherche, setDebouncedTechniquesRecherche] = useState('');
    const [debouncedPlateformeRecherche, setDebouncedPlateformeRecherche] = useState('');
    const [isLoadingAgentsScientifiques, setIsLoadingAgentsScientifiques] = useState(false);
    const [isLoadingAgentsTechniques, setIsLoadingAgentsTechniques] = useState(false);
    const [isLoadingAgentsPlateforme, setIsLoadingAgentsPlateforme] = useState(false);
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
                .filter((personnel) => personnel.responsable_scientifique)
                .map((personnel) => personnel.utilisateur_id)
        );
        setResponsablesTechniques(
            selectedPlateforme.lastHistorique.plateformeHistoriquePersonnels
                .filter((personnel) => personnel.responsable_technique)
                .map((personnel) => personnel.utilisateur_id)
        );
        setResponsablesPlateforme(
            selectedPlateforme.lastHistorique.plateformeHistoriquePersonnels
                .filter((personnel) => personnel.responsable_plateforme)
                .map((personnel) => personnel.utilisateur_id)
        );
        setScientifiquesRecherche('');
        setTechniquesRecherche('');
        setPlateformeRecherche('');
        setEditDialogOpen(true);
        handleCloseMenu();
    }

    function handleCloseEditDialog() {
        setEditDialogOpen(false);
        setSelectedPlateforme(null);
        setResponsablesScientifiques([]);
        setResponsablesTechniques([]);
        setResponsablesPlateforme([]);
        setAgentsOptionsScientifiques([]);
        setAgentsOptionsTechniques([]);
        setAgentsOptionsPlateforme([]);
        setScientifiquesRecherche('');
        setTechniquesRecherche('');
        setPlateformeRecherche('');
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

    useEffect(() => {
        const timer = setTimeout(() => setDebouncedScientifiquesRecherche(scientifiquesRecherche), 500);
        return () => clearTimeout(timer);
    }, [scientifiquesRecherche]);

    useEffect(() => {
        const timer = setTimeout(() => setDebouncedTechniquesRecherche(techniquesRecherche), 500);
        return () => clearTimeout(timer);
    }, [techniquesRecherche]);

    useEffect(() => {
        const timer = setTimeout(() => setDebouncedPlateformeRecherche(plateformeRecherche), 500);
        return () => clearTimeout(timer);
    }, [plateformeRecherche]);

    const fetchAgentsScientifiques = useCallback(async (search: string, selected: Agent[]) => {
        setIsLoadingAgentsScientifiques(true);
        try {
            const { agents } = await getAgentByPage(search);
            const map = new Map<number, Agent>();
            for (const a of selected) map.set(a.id, a);
            for (const a of agents) map.set(a.id, a);
            setAgentsOptionsScientifiques(Array.from(map.values()));
        } catch (error) {
            console.error("Erreur lors du chargement des agents scientifiques :", error);
        } finally {
            setIsLoadingAgentsScientifiques(false);
        }
    }, []);

    const fetchAgentsTechniques = useCallback(async (search: string, selected: Agent[]) => {
        setIsLoadingAgentsTechniques(true);
        try {
            const { agents } = await getAgentByPage(search);
            const map = new Map<number, Agent>();
            for (const a of selected) map.set(a.id, a);
            for (const a of agents) map.set(a.id, a);
            setAgentsOptionsTechniques(Array.from(map.values()));
        } catch (error) {
            console.error("Erreur lors du chargement des agents techniques :", error);
        } finally {
            setIsLoadingAgentsTechniques(false);
        }
    }, []);

    const fetchAgentsPlateforme = useCallback(async (search: string, selected: Agent[]) => {
        setIsLoadingAgentsPlateforme(true);
        try {
            const { agents } = await getAgentByPage(search);
            const map = new Map<number, Agent>();
            for (const a of selected) map.set(a.id, a);
            for (const a of agents) map.set(a.id, a);
            setAgentsOptionsPlateforme(Array.from(map.values()));
        } catch (error) {
            console.error("Erreur lors du chargement des agents plateforme :", error);
        } finally {
            setIsLoadingAgentsPlateforme(false);
        }
    }, []);

    useEffect(() => {
        if (!editDialogOpen) {
            return;
        }

        fetchAgentsScientifiques(debouncedScientifiquesRecherche, responsablesScientifiques);
    }, [editDialogOpen, fetchAgentsScientifiques, debouncedScientifiquesRecherche, responsablesScientifiques]);

    useEffect(() => {
        if (!editDialogOpen) {
            return;
        }

        fetchAgentsTechniques(debouncedTechniquesRecherche, responsablesTechniques);
    }, [editDialogOpen, fetchAgentsTechniques, debouncedTechniquesRecherche, responsablesTechniques]);

    useEffect(() => {
        if (!editDialogOpen) {
            return;
        }

        fetchAgentsPlateforme(debouncedPlateformeRecherche, responsablesPlateforme);
    }, [editDialogOpen, fetchAgentsPlateforme, debouncedPlateformeRecherche, responsablesPlateforme]);

    function getAgentLabel(agent: Agent) {
        return `${agent.nom} ${agent.prenom}`;
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
                        Responsables de la plateforme : {selectedPlateforme?.lastHistorique.nom_officiel ?? "N/A"}
                    </Typography>
                    <Autocomplete
                        multiple
                        noOptionsText="Aucun agent trouvé"
                        options={agentsOptionsScientifiques}
                        value={responsablesScientifiques}
                        loading={isLoadingAgentsScientifiques}
                        inputValue={scientifiquesRecherche}
                        onInputChange={(_, value) => setScientifiquesRecherche(value)}
                        onChange={(_, value) => setResponsablesScientifiques(value)}
                        getOptionLabel={getAgentLabel}
                        isOptionEqualToValue={(option, value) => option.id === value.id}
                        filterOptions={(options) => options}
                        renderInput={(params) => (
                            <TextField
                                {...params}
                                label="Responsables scientifiques"
                                placeholder="Rechercher un agent"
                            />
                        )}
                    />
                    <Autocomplete
                        multiple
                        noOptionsText="Aucun agent trouvé"
                        options={agentsOptionsTechniques}
                        value={responsablesTechniques}
                        loading={isLoadingAgentsTechniques}
                        inputValue={techniquesRecherche}
                        onInputChange={(_, value) => setTechniquesRecherche(value)}
                        onChange={(_, value) => setResponsablesTechniques(value)}
                        getOptionLabel={getAgentLabel}
                        isOptionEqualToValue={(option, value) => option.id === value.id}
                        filterOptions={(options) => options}
                        renderInput={(params) => (
                            <TextField
                                {...params}
                                label="Responsables techniques"
                                placeholder="Rechercher un agent"
                            />
                        )}
                    />
                    <Autocomplete
                        multiple
                        noOptionsText="Aucun agent trouvé"
                        options={agentsOptionsPlateforme}
                        value={responsablesPlateforme}
                        loading={isLoadingAgentsPlateforme}
                        inputValue={plateformeRecherche}
                        onInputChange={(_, value) => setPlateformeRecherche(value)}
                        onChange={(_, value) => setResponsablesPlateforme(value)}
                        getOptionLabel={getAgentLabel}
                        isOptionEqualToValue={(option, value) => option.id === value.id}
                        filterOptions={(options) => options}
                        renderInput={(params) => (
                            <TextField
                                {...params}
                                label="Responsables de la plateforme"
                                placeholder="Rechercher un agent"
                            />
                        )}
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
                                <Typography variant="h6">{plateforme.lastHistorique.nom_officiel}</Typography>
                                <Typography variant="body2" color="textSecondary">
                                    Année : {plateforme.lastHistorique.annee}
                                </Typography>
                            </CardContent>
                            <CardActions>
                                <Link component={LinkRouter} to={`/plateforme/edition?id=${plateforme.lastHistorique.id}`} color="primary" sx={{ cursor: 'pointer' }}>
                                    Voir les détails
                                </Link>
                                <Button
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