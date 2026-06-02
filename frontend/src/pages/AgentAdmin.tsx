import { MoreVert } from "@mui/icons-material";
import {
    Box,
    Button,
    Container,
    Dialog,
    DialogActions,
    DialogContent,
    DialogTitle,
    Divider,
    IconButton,
    Menu,
    MenuItem,
    Pagination,
    Stack,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableRow,
    TextField,
    Typography,
} from "@mui/material";
import type { AxiosError } from "axios";
import { useCallback, useContext, useEffect, useState } from "react";
import { AgentAdvancedFields } from "../components/AgentAdvancedFields";
import { SnackbarContext } from "../context/snackbarContext";
import { fetchAgentListeParPage, modifierAgent, type Agent } from "../services/agentService.ts";
import type { ApiError } from "../utils/axios.ts";

export default function AgentAdmin() {
    const snackbar = useContext(SnackbarContext);

    const [agents, setAgents] = useState<Agent[]>([]);
    const [recherche, setRecherche] = useState("");
    const [page, setPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [isLoading, setIsLoading] = useState(false);
    const [isSaving, setIsSaving] = useState(false);

    const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
    const [selectedAgent, setSelectedAgent] = useState<Agent | null>(null);
    const [editDialogOpen, setEditDialogOpen] = useState(false);
    const [editableAgent, setEditableAgent] = useState<Agent | null>(null);

    const menuOpen = Boolean(anchorEl);

    const fetchAgents = useCallback(async () => {
        setIsLoading(true);
        try {
            const { agents, pages_total } = await fetchAgentListeParPage(page, recherche);
            setAgents(agents);
            setTotalPages(pages_total);
        } catch (error) {
            console.error("Erreur lors du chargement des agents :", error);
        } finally {
            setIsLoading(false);
        }
    }, [page, recherche]);

    useEffect(() => {
        void fetchAgents();
    }, [fetchAgents]);

    function handleOpenMenu(event: React.MouseEvent<HTMLButtonElement>, agent: Agent) {
        setAnchorEl(event.currentTarget);
        setSelectedAgent(agent);
    }

    function handleCloseMenu() {
        setAnchorEl(null);
    }

    function handleOpenEditDialog() {
        if (!selectedAgent) {
            return;
        }

        setEditableAgent(selectedAgent);

        setEditDialogOpen(true);
        handleCloseMenu();
    }

    function handleCloseEditDialog() {
        setEditDialogOpen(false);
        setEditableAgent(null);
        setSelectedAgent(null);
    }

    async function handleSaveEditAgent() {
        if (!editableAgent) {
            return;
        }

        setIsSaving(true);
        try {
            await modifierAgent(editableAgent.id!, {
                nom: editableAgent.nom,
                prenom: editableAgent.prenom,
                email: editableAgent.email,
                employeur_id: editableAgent.employeur_id ?? null,
                corps_id: editableAgent.corps_id ?? null,
                grade_id: editableAgent.grade_id ?? null,
                type_contrat_id: editableAgent.type_contrat_id ?? null,
                unite_id: editableAgent.unite_id ?? null,
                doctorant: editableAgent.doctorant ?? false,
                universite_rattachement_id: editableAgent.universite_rattachement_id ?? null,
                baps_ids: editableAgent.baps_ids ?? [],
                emploi_types_ids: editableAgent.emploi_types_ids ?? [],
                section_cnrs_id: editableAgent.section_cnrs_id ?? null,
                cnu_id: editableAgent.cnu_id ?? null,
            });

            snackbar?.trigger({
                message: "Agent mis à jour avec succès",
                couleur: "success",
            });

            await fetchAgents();
            handleCloseEditDialog();
        } catch (error) {
            const errorAxios = error as AxiosError<ApiError>;
            snackbar?.trigger({
                message: errorAxios.response?.data.message ?? "Erreur lors de la mise à jour de l'agent",
                couleur: "error",
            });
        } finally {
            setIsSaving(false);
        }
    }

    return (
        <Container sx={{ py: 4 }}>
            <Typography variant="h5" component="h1">
                Liste des agents
            </Typography>

            <Stack direction="row" divider={<Divider orientation="vertical" flexItem />} spacing={2} sx={{ my: 2, alignItems: "center" }}>
                <TextField
                    type="text"
                    placeholder="Rechercher un agent..."
                    value={recherche}
                    onChange={(e) => {
                        setRecherche(e.target.value);
                        setPage(1);
                    }}
                    sx={{ width: "100%" }}
                />
                <Pagination count={totalPages} page={page} onChange={(_, newPage) => setPage(newPage)} sx={{ flexShrink: 0 }} />
            </Stack>

            {isLoading ? (
                <Typography component="p" variant="h6">
                    Chargement...
                </Typography>
            ) : agents.length === 0 ? (
                <Typography variant="body1">Aucun agent trouvé.</Typography>
            ) : (
                <Box sx={{ overflowX: "auto" }}>
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableCell>Prénom</TableCell>
                                <TableCell>Nom</TableCell>
                                <TableCell>Email</TableCell>
                                <TableCell align="right">Actions</TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {agents.map((agent) => (
                                <TableRow key={agent.id} hover>
                                    <TableCell>{agent.prenom}</TableCell>
                                    <TableCell>{agent.nom}</TableCell>
                                    <TableCell>{agent.email}</TableCell>
                                    <TableCell align="right">
                                        <IconButton
                                            aria-label="actions"
                                            aria-controls={menuOpen ? "agent-actions-menu" : undefined}
                                            aria-haspopup="true"
                                            onClick={(event) => handleOpenMenu(event, agent)}
                                        >
                                            <MoreVert />
                                        </IconButton>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Box>
            )}

            <Stack direction="row" spacing={2} sx={{ mt: 2, alignItems: "center", justifyContent: "center" }}>
                <Typography variant="body2" component="span">
                    Page {page} sur {totalPages}
                </Typography>
            </Stack>

            <Menu id="agent-actions-menu" anchorEl={anchorEl} open={menuOpen} onClose={handleCloseMenu}>
                <MenuItem onClick={handleOpenEditDialog}>Éditer</MenuItem>
            </Menu>

            <Dialog open={editDialogOpen} onClose={handleCloseEditDialog} fullWidth maxWidth="sm">
                <DialogTitle>Éditer un agent</DialogTitle>
                <DialogContent sx={{ display: "flex", flexDirection: "column", gap: 2, pt: 1 }}>
                    {editableAgent && (
                        <AgentAdvancedFields
                            value={editableAgent}
                            onChange={(next) => {
                                setEditableAgent((current) =>
                                    current
                                        ? {
                                            ...current,
                                            ...next,
                                        }
                                        : current
                                );
                            }}
                            disabled={isSaving}
                        />
                    )}
                </DialogContent>
                <DialogActions>
                    <Button color="inherit" onClick={handleCloseEditDialog} disabled={isSaving}>
                        Annuler
                    </Button>
                    <Button variant="contained" onClick={handleSaveEditAgent} disabled={isSaving || !editableAgent}>
                        Enregistrer
                    </Button>
                </DialogActions>
            </Dialog>
        </Container>
    );
}
