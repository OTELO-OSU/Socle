import { Avatar, Box, Button, Card, CardContent, Chip, Dialog, DialogActions, DialogContent, DialogTitle, Divider, IconButton, List, ListItem, ListItemText, MenuItem, Paper, Select, Stack, Typography } from "@mui/material";
import { useContext, useState } from "react";
import { ajouterTagAuUser, fetchTagsAdmin, removeTagFromUser, type TagAdmin } from "../services/tagsService.ts";
import { Add, Delete } from "@mui/icons-material";
import { AgentAutocompleteField } from "../components/AgentAutocompleteField.tsx";
import type { Agent } from "../services/agentService.ts";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { AxiosError } from "axios";
import { DictionnaireContext } from "../context/dictionnaireContext.ts";
import { SnackbarContext } from "../context/snackbarContext.ts";
import { AdminPageShell } from "../components/admin/AdminPageShell";

function DialogAddUserToTag({ tagAdmin, open, onClose }: { tagAdmin: TagAdmin, open: boolean, onClose: () => void }) {
    const [selectedAgent, setSelectedAgent] = useState<Agent | null>(null);
    const queryClient = useQueryClient();
    const snackbarContext = useContext(SnackbarContext);

    const addUserToTagMutation = useMutation({
        mutationFn: ({ tagId, agentId }: { tagId: number; agentId: number; agent: Agent }) => ajouterTagAuUser(tagId, agentId),
        onSuccess: (_data, variables) => {
            queryClient.setQueryData<TagAdmin[]>(['tagsAdmin'], (previousTags) => {
                if (!previousTags) return previousTags;

                return previousTags.map((tag) => {
                    if (tag.id !== variables.tagId) return tag;

                    const alreadyInTag = tag.utilisateurs.some((user) => user.id === variables.agent.id);
                    if (alreadyInTag) return tag;

                    return {
                        ...tag,
                        utilisateurs: [...tag.utilisateurs, variables.agent],
                    };
                });
            });
            snackbarContext!.trigger({
                message: _data.message || "Le tag a été ajouté à l'utilisateur.",
                couleur: "success",
            });
            setSelectedAgent(null);
            onClose();
        },
        onError: (error) => {
            console.error("Erreur lors de l'ajout du tag à l'utilisateur :", error);
            snackbarContext!.trigger({
                message: error instanceof AxiosError ? error.response?.data?.message || "Une erreur est survenue." : "Une erreur est survenue.",
                couleur: "error",
            });
        }
    });

    function ajouterUtilisateur() {
        if (!selectedAgent?.id) return;
        addUserToTagMutation.mutate({ tagId: tagAdmin.id, agentId: selectedAgent.id, agent: selectedAgent });
    }

    return (
        <Dialog
            open={open}
            onClose={onClose}
            fullWidth
            maxWidth="sm"
            slotProps={{ paper: { sx: { borderRadius: 3, p: 0.5 } } }}
        >
            <DialogTitle sx={{ fontWeight: 700 }}>Ajouter un utilisateur au tag "{tagAdmin.nom}"</DialogTitle>
            <DialogContent sx={{ pt: "8px !important", display: "flex", flexDirection: "column", gap: 1.5 }}>
                <AgentAutocompleteField label="Agent" value={selectedAgent} onChange={setSelectedAgent} />
                {addUserToTagMutation.isError && (
                    <Typography color="error" variant="body2" sx={{ mt: 1 }}>
                        {addUserToTagMutation.error instanceof AxiosError ? addUserToTagMutation.error.response?.data?.message : "Une erreur est survenue."}
                    </Typography>
                )}
            </DialogContent>
            <DialogActions sx={{ px: 3, pb: 2 }}>
                <Button onClick={onClose} color="inherit" disabled={addUserToTagMutation.isPending}>Annuler</Button>
                <Button
                    onClick={ajouterUtilisateur}
                    color="primary"
                    variant="contained"
                    disabled={!selectedAgent?.id || addUserToTagMutation.isPending}
                >
                    {addUserToTagMutation.isPending ? "Ajout..." : "Ajouter"}
                </Button>
            </DialogActions>
        </Dialog>
    )
}

export function TagAdminCard({ tagAdmin }: { tagAdmin: TagAdmin }) {
    const [dialogOpen, setDialogOpen] = useState(false);
    const snackbarContext = useContext(SnackbarContext);

    const queryClient = useQueryClient();

    const removeTagFromUserMutation = useMutation({
        mutationFn: ({ agentId }: { agentId: number }) => removeTagFromUser(tagAdmin.id, agentId),
        onSuccess: (_data, variables) => {
            queryClient.setQueryData<TagAdmin[]>(['tagsAdmin'], (previousTags) => {
                if (!previousTags) return previousTags;
                return previousTags.map((tag) => {
                    if (tag.id !== tagAdmin.id) return tag;

                    return {
                        ...tag,
                        utilisateurs: tag.utilisateurs.filter((user) => user.id !== variables.agentId),
                    };
                });
            });
            snackbarContext!.trigger({
                message: _data.message || "Le tag a été retiré de l'utilisateur.",
                couleur: "success",
            });
        },
        onError: (error) => {
            console.error("Erreur lors de la suppression du tag de l'utilisateur :", error);
            snackbarContext!.trigger({
                message: error instanceof AxiosError ? error.response?.data?.message || "Une erreur est survenue." : "Une erreur est survenue.",
                couleur: "error",
            });
        }
    });

    return (
        <Card sx={{ borderRadius: 4, border: "1px solid", borderColor: "divider", boxShadow: "0 10px 30px rgba(15,23,42,0.08)" }}>
            <DialogAddUserToTag tagAdmin={tagAdmin} open={dialogOpen} onClose={() => setDialogOpen(false)} />
            <Box
                sx={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    gap: 2,
                    p: 2.5,
                    background: "linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%)",
                }}
            >
                <Stack spacing={1}>
                    <Typography variant="h5" sx={{ fontWeight: 700 }}>{tagAdmin.nom}</Typography>
                    <Chip
                        size="small"
                        color="primary"
                        variant="outlined"
                        label={`${tagAdmin.utilisateurs.length} utilisateur${tagAdmin.utilisateurs.length > 1 ? "s" : ""}`}
                        sx={{ width: "fit-content" }}
                    />
                </Stack>
                <Button startIcon={<Add />} variant="contained" color="primary" onClick={() => setDialogOpen(true)}>
                    Ajouter
                </Button>
            </Box>
            <Divider />
            <CardContent>
                <Typography variant="subtitle1" sx={{ fontWeight: 600, mb: 1.5 }}>Utilisateurs affiliés à ce tag</Typography>
                {tagAdmin.utilisateurs.length > 0 ? (
                    <List sx={{ py: 0 }}>
                        {tagAdmin.utilisateurs.map((agent) => (
                            <ListItem
                                key={agent.id}
                                sx={{
                                    px: 1,
                                    py: 1,
                                    borderRadius: 2,
                                    "&:not(:last-of-type)": { mb: 1 },
                                    backgroundColor: "rgba(148, 163, 184, 0.08)",
                                }}
                                secondaryAction={
                                    <IconButton color="error" onClick={() => removeTagFromUserMutation.mutate({ agentId: agent.id! })}>
                                        <Delete />
                                    </IconButton>
                                }
                            >
                                <Avatar sx={{ width: 32, height: 32, mr: 1.5, bgcolor: "primary.main", fontSize: 14 }}>
                                    {(agent.prenom?.[0] || "?").toUpperCase()}
                                </Avatar>
                                <ListItemText
                                    primary={<Typography sx={{ fontWeight: 600 }}>{agent.prenom} {agent.nom}</Typography>}
                                    secondary="Utilisateur rattaché à ce tag"
                                />
                            </ListItem>
                        ))}
                    </List>
                ) : (
                    <Box sx={{ p: 2, borderRadius: 2, border: "1px dashed", borderColor: "divider", backgroundColor: "rgba(148,163,184,0.08)" }}>
                        <Typography color="text.secondary">Aucun utilisateur n'est affilié à ce tag.</Typography>
                    </Box>
                )}
            </CardContent>
        </Card>
    );
}

function AjouterTagDialog({ open, onClose }: { open: boolean, onClose: () => void }) {
    const dictionnaireContext = useContext(DictionnaireContext);
    const [selectedTagId, setSelectedTagId] = useState<number | null>(null);

    const queryClient = useQueryClient();

    function ajouterDansLaQuery() {
        if (!selectedTagId) return;

        const tagToAdd = dictionnaireContext?.tags.find((tag) => tag.id === selectedTagId);
        if (!tagToAdd) return;

        queryClient.setQueryData<TagAdmin[]>(['tagsAdmin'], (previousTags) => {
            if (!previousTags) return previousTags;

            const alreadyExists = previousTags.some((tag) => tag.id === selectedTagId);
            if (alreadyExists) return previousTags;

            return [{ ...tagToAdd, utilisateurs: [] }, ...previousTags];
        });
        onClose();
    }

    return (
        <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm" slotProps={{ paper: { sx: { borderRadius: 3, p: 0.5 } } }}>
            <DialogTitle sx={{ fontWeight: 700 }}>Ajouter un tag</DialogTitle>
            <DialogContent sx={{ pt: "8px !important" }}>
                <Typography color="text.secondary">Sur quel tag souhaitez-vous ajouter un utilisateur ?</Typography>
                <Select fullWidth sx={{ mt: 2 }} value={selectedTagId || ''} onChange={(event) => {
                    const tagId = Number(event.target.value);
                    setSelectedTagId(Number.isNaN(tagId) ? null : tagId);
                }}>

                    <MenuItem value="" disabled>Choisissez un tag</MenuItem>
                    {dictionnaireContext?.tags.map((tag) => (
                        <MenuItem key={tag.id} value={tag.id}>
                            {tag.nom}
                        </MenuItem>
                    ))}
                </Select>
            </DialogContent>
            <DialogActions sx={{ px: 3, pb: 2 }}>
                <Button onClick={onClose} color="inherit">Fermer</Button>
                <Button onClick={ajouterDansLaQuery} color="primary" variant="contained">Ajouter</Button>
            </DialogActions>
        </Dialog >
    )
}

export default function TagsAdmin() {
    const [isAjouterTagDialogOpen, setIsAjouterTagDialogOpen] = useState(false);
    const tagsAdmin = useQuery({
        queryKey: ['tagsAdmin'],
        queryFn: fetchTagsAdmin,
    });

    return (
        <AdminPageShell
            title="Gestion des tags"
            subtitle="Octroie aux utilisateurs la permission d'accéder à certaines plateformes en fonction des tags qui leur sont associés."
            action={
                <Button startIcon={<Add />} variant="contained" color="primary" onClick={() => setIsAjouterTagDialogOpen(true)}>
                    Ajouter un tag
                </Button>
            }
        >
            <AjouterTagDialog open={isAjouterTagDialogOpen} onClose={() => setIsAjouterTagDialogOpen(false)} />
            {tagsAdmin.isPending ? (
                <Typography sx={{ px: 1 }}>Chargement des tags...</Typography>
            ) : tagsAdmin.isError ? (
                <Typography color="error" sx={{ px: 1 }}>Erreur lors du chargement des tags.</Typography>
            ) : null}
            {tagsAdmin.isSuccess && (
                <>
                    {tagsAdmin.data.length === 0 && (
                        <Paper sx={{ p: 2, borderRadius: 3, border: "1px dashed", borderColor: "divider", mb: 2 }}>
                            <Typography color="text.secondary">Encore aucune plateforme ne possède de tag. Vous pouvez quand même assigner des tags aux utilisateurs en prévision.</Typography>
                        </Paper>
                    )}
                    <Stack spacing={2.5}>
                        {tagsAdmin.data?.map((tag) => (
                            <TagAdminCard key={tag.id} tagAdmin={tag} />
                        ))}
                    </Stack>
                </>
            )}
        </AdminPageShell>
    );
}