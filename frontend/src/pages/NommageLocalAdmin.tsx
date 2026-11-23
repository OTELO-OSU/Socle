import { Box, Button, Dialog, Pagination, Stack, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, TextField, Typography, Paper, IconButton, FormControl, Select, MenuItem, FormHelperText } from "@mui/material";
import { useCallback, useContext, useEffect, useState } from "react";
import { SnackbarContext } from "../context/snackbarContext";
import { Delete as DeleteIcon, Edit as EditIcon } from "@mui/icons-material";
import type { AxiosError } from "axios";
import type { ApiError } from "../utils/axios";
import {
    fetchAllPseudoIdMappings,
    createOrUpdatePseudoIdMapping,
    deletePseudoIdMapping,
    fetchPlateformesForSelect,
    type PseudoIdMapping,
    type PlateformeSelectOption,
} from "../services/adminPseudoIdService";
import { AdminPageShell, AdminSurface } from "../components/admin/AdminPageShell";

const ITEMS_PER_PAGE = 10;

export default function NommageLocalAdmin() {
    const [mappings, setMappings] = useState<PseudoIdMapping[]>([]);
    const [plateformes, setPlateformes] = useState<PlateformeSelectOption[]>([]);
    const [page, setPage] = useState(1);
    const [isLoading, setIsLoading] = useState(false);
    const [editDialogOpen, setEditDialogOpen] = useState(false);
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [selectedMapping, setSelectedMapping] = useState<PseudoIdMapping | null>(null);
    const [newPseudoId, setNewPseudoId] = useState("");
    const [newPlateformeId, setNewPlateformeId] = useState("");
    const snackbar = useContext(SnackbarContext);

    const fetchMappings = useCallback(async () => {
        setIsLoading(true);
        try {
            const data = await fetchAllPseudoIdMappings();
            setMappings(data);
        } catch (error) {
            console.error("Erreur lors du chargement des mappings :", error);
            snackbar?.trigger({
                message: "Erreur lors du chargement des mappings",
                couleur: "error",
            });
        } finally {
            setIsLoading(false);
        }
    }, [snackbar]);

    const fetchPlateformes = useCallback(async () => {
        try {
            const data = await fetchPlateformesForSelect();
            setPlateformes(data);
        } catch (error) {
            console.error("Erreur lors du chargement des plateformes :", error);
            snackbar?.trigger({
                message: "Erreur lors du chargement des plateformes",
                couleur: "error",
            });
        }
    }, [snackbar]);

    useEffect(() => {
        fetchMappings();
        fetchPlateformes();
    }, [fetchMappings, fetchPlateformes]);

    const handleEditClick = (mapping: PseudoIdMapping) => {
        setSelectedMapping(mapping);
        setNewPseudoId(mapping.pseudoIdMapped);
        setEditDialogOpen(true);
    };

    const handleDeleteClick = (mapping: PseudoIdMapping) => {
        setSelectedMapping(mapping);
        setDeleteDialogOpen(true);
    };

    async function handleSaveEdit() {
        if (!selectedMapping || !newPseudoId.trim()) {
            snackbar?.trigger({
                message: "Le pseudo_id ne peut pas être vide",
                couleur: "error",
            });
            return;
        }

        try {
            await createOrUpdatePseudoIdMapping(selectedMapping.plateformeId, newPseudoId, selectedMapping.instanceHost || undefined);
            snackbar?.trigger({
                message: "Pseudo ID mis à jour avec succès",
                couleur: "success",
            });
            await fetchMappings();
            handleCloseEditDialog();
        } catch (error) {
            const errorAxios = error as AxiosError<ApiError>;
            snackbar?.trigger({
                message: errorAxios.response?.data?.message || "Erreur lors de la mise à jour",
                couleur: "error",
            });
        }
    }

    async function handleDelete() {
        if (!selectedMapping) return;

        try {
            await deletePseudoIdMapping(selectedMapping.plateformeId, selectedMapping.instanceHost || undefined);
            snackbar?.trigger({
                message: "Mapping supprimé avec succès",
                couleur: "success",
            });
            await fetchMappings();
            handleCloseDeleteDialog();
        } catch (error) {
            const errorAxios = error as AxiosError<ApiError>;
            snackbar?.trigger({
                message: errorAxios.response?.data?.message || "Erreur lors de la suppression",
                couleur: "error",
            });
        }
    }

    async function handleCreateNew() {
        if (!newPlateformeId || !newPseudoId.trim()) {
            snackbar?.trigger({
                message: "La plateforme et le pseudo_id ne peuvent pas être vides",
                couleur: "error",
            });
            return;
        }

        try {
            const plateforme = plateformes.find(p => p.id === parseInt(newPlateformeId));
            const instanceHost = plateforme?.instance_host || undefined;
            await createOrUpdatePseudoIdMapping(parseInt(newPlateformeId), newPseudoId, instanceHost);
            snackbar?.trigger({
                message: "Nouveau mapping créé avec succès",
                couleur: "success",
            });
            await fetchMappings();
            setNewPlateformeId("");
            setNewPseudoId("");
        } catch (error) {
            const errorAxios = error as AxiosError<ApiError>;
            snackbar?.trigger({
                message: errorAxios.response?.data?.message || "Erreur lors de la création",
                couleur: "error",
            });
        }
    }

    function handleCloseEditDialog() {
        setEditDialogOpen(false);
        setSelectedMapping(null);
        setNewPseudoId("");
    }

    function handleCloseDeleteDialog() {
        setDeleteDialogOpen(false);
        setSelectedMapping(null);
    }

    const paginatedMappings = mappings.slice(
        (page - 1) * ITEMS_PER_PAGE,
        page * ITEMS_PER_PAGE
    );

    const totalPages = Math.ceil(mappings.length / ITEMS_PER_PAGE);

    const getPlateformeName = (plateformeId: number): string => {
        const plateforme = plateformes.find((p) => p.id === plateformeId);
        return plateforme ? `${plateforme.nomOfficiel} (ID: ${plateformeId})` : `ID: ${plateformeId}`;
    };

    const getInstanceHostDisplay = (mapping: PseudoIdMapping): string => {
        if (mapping.instanceHost) {
            return mapping.instanceHost;
        }
        return "Local";
    };

    return (
        <AdminPageShell
            title="Nommage Local des Plateformes"
            subtitle="Gérez les pseudo_ids personnalisés pour les plateformes locales et distantes"
        >
            <Dialog open={editDialogOpen} onClose={handleCloseEditDialog} fullWidth maxWidth="sm">
                <Box sx={{ p: 3, display: 'flex', flexDirection: 'column', gap: 2 }}>
                    <Typography variant="h4" component="h2">Modifier le pseudo_id</Typography>
                    <TextField
                        label="Pseudo ID"
                        fullWidth
                        value={newPseudoId}
                        onChange={(e) => setNewPseudoId(e.target.value)}
                    />
                    <Stack direction="row" spacing={2} sx={{ justifyContent: 'flex-end' }}>
                        <Button color="inherit" onClick={handleCloseEditDialog}>Annuler</Button>
                        <Button variant="contained" onClick={handleSaveEdit}>Sauvegarder</Button>
                    </Stack>
                </Box>
            </Dialog>

            <Dialog open={deleteDialogOpen} onClose={handleCloseDeleteDialog}>
                <Box sx={{ p: 3, display: 'flex', flexDirection: 'column', gap: 2 }}>
                    <Typography variant="h4" component="h2">Supprimer le mapping</Typography>
                    <Typography variant="body1">
                        Êtes-vous sûr de vouloir supprimer ce mapping pour la plateforme {getPlateformeName(selectedMapping?.plateformeId ?? 0)} ?
                    </Typography>
                    <Stack direction="row" spacing={2} sx={{ justifyContent: 'flex-end' }}>
                        <Button color="inherit" onClick={handleCloseDeleteDialog}>Annuler</Button>
                        <Button variant="contained" color="error" onClick={handleDelete}>Supprimer</Button>
                    </Stack>
                </Box>
            </Dialog>

            <AdminSurface>
                <Typography variant="h6" sx={{ mb: 2, fontWeight: "bold" }}>Créer un nouveau mapping</Typography>
                <Box sx={{ display: 'flex', gap: 2, mb: 3, alignItems: 'flex-start', flexWrap: 'wrap' }}>
                    <FormControl sx={{ flex: 1, minWidth: 250 }}>
                        <Select
                            value={newPlateformeId}
                            onChange={(e) => setNewPlateformeId(e.target.value)}
                            displayEmpty
                        >
                            <MenuItem value="">
                                <em>Sélectionner une plateforme</em>
                            </MenuItem>
                            {plateformes.map((plateforme) => (
                                <MenuItem key={plateforme.id} value={plateforme.id}>
                                    {plateforme.nomOfficiel} (ID: {plateforme.id}) {plateforme.instance_host ? ` - ${plateforme.instance_host}` : " - Local"}
                                </MenuItem>
                            ))}
                        </Select>
                        <FormHelperText>Choisir la plateforme</FormHelperText>
                    </FormControl>
                    <TextField
                        label="Pseudo ID"
                        value={newPseudoId}
                        onChange={(e) => setNewPseudoId(e.target.value)}
                        sx={{ flex: 2, minWidth: 200 }}
                    />
                    <Button variant="contained" color="primary" onClick={handleCreateNew} sx={{ mt: 1 }}>
                        Ajouter
                    </Button>
                </Box>
            </AdminSurface>

            <AdminSurface>
                {isLoading ? (
                    <Typography variant="h6" sx={{ mt: 2 }}>Chargement...</Typography>
                ) : mappings.length === 0 ? (
                    <Typography variant="body1" color="textSecondary" sx={{ mt: 2 }}>
                        Aucun mapping pseudo_id trouvé.
                    </Typography>
                ) : (
                    <>
                        <TableContainer component={Paper}>
                            <Table>
                                <TableHead>
                                    <TableRow sx={{ backgroundColor: "#f5f5f5" }}>
                                        <TableCell sx={{ fontWeight: "bold" }}>Plateforme</TableCell>
                                        <TableCell sx={{ fontWeight: "bold" }}>Instance/Host</TableCell>
                                        <TableCell sx={{ fontWeight: "bold" }}>Pseudo ID Mappé</TableCell>
                                        <TableCell sx={{ fontWeight: "bold" }}>Dernière Mise à Jour</TableCell>
                                        <TableCell sx={{ fontWeight: "bold" }} align="right">Actions</TableCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {paginatedMappings.map((mapping) => (
                                        <TableRow key={`${mapping.id}-${mapping.instanceHost}`}>
                                            <TableCell>{getPlateformeName(mapping.plateformeId)}</TableCell>
                                            <TableCell>{getInstanceHostDisplay(mapping)}</TableCell>
                                            <TableCell>{mapping.pseudoIdMapped}</TableCell>
                                            <TableCell>
                                                {new Date(mapping.updatedAt).toLocaleDateString()}
                                            </TableCell>
                                            <TableCell align="right">
                                                <IconButton
                                                    size="small"
                                                    onClick={() => handleEditClick(mapping)}
                                                    title="Modifier"
                                                >
                                                    <EditIcon fontSize="small" />
                                                </IconButton>
                                                <IconButton
                                                    size="small"
                                                    onClick={() => handleDeleteClick(mapping)}
                                                    title="Supprimer"
                                                    color="error"
                                                >
                                                    <DeleteIcon fontSize="small" />
                                                </IconButton>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </TableContainer>
                        <Stack direction="row" spacing={2} sx={{ mt: 2, justifyContent: 'space-between', alignItems: 'center' }}>
                            <Typography variant="body2">
                                Total: {mappings.length} mappings
                            </Typography>
                            {totalPages > 1 && (
                                <Pagination
                                    count={totalPages}
                                    page={page}
                                    onChange={(_, newPage) => setPage(newPage)}
                                />
                            )}
                        </Stack>
                    </>
                )}
            </AdminSurface>
        </AdminPageShell>
    );
}


