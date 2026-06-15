import { Box, Button, Card, CardContent, Dialog, DialogActions, DialogContent, DialogTitle, Divider, IconButton, InputAdornment, List, ListItem, ListItemText, MenuItem, Paper, Select, Stack, TextField, Typography } from "@mui/material";
import { useContext, useState } from "react";
import { ajouterTagAuUser, removeTagFromUser } from "../services/tagsService.ts";
import { Add, CopyAll, Delete } from "@mui/icons-material";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { AxiosError } from "axios";
import { DictionnaireContext } from "../context/dictionnaireContext.ts";
import { AdminPageShell } from "../components/admin/AdminPageShell.tsx";
import { creerHMT, entrerHMT, fetchHarvesterAdmin, supprHarvester, supprHMT, type Harvester, type HarvesterAdmin, type HMT } from "../services/harvesterService.ts";

export function HMTCard({ hmt }: { hmt: HMT }) {
    const [dialogOpen, setDialogOpen] = useState(false);
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const dictionnaireContext = useContext(DictionnaireContext);

    const queryClient = useQueryClient();

    const removeTagFromHMTMutation = useMutation({
        mutationFn: ({ utilisateurId, tagId }: { utilisateurId: number; tagId: number }) => removeTagFromUser(tagId, utilisateurId),
        onSuccess: (_data, variables) => {
            // Mettre à jour la query pour refléter la suppression du tag
            queryClient.setQueryData<HarvesterAdmin>(['harvesterAdmin'], (previousData) => {
                if (!previousData) return previousData;

                return {
                    ...previousData,
                    hmt: previousData.hmt.map((hmt) => {
                        if (hmt.utilisateur_id !== variables.utilisateurId) return hmt;

                        return {
                            ...hmt,
                            tags_ids: hmt.tags_ids.filter((tagId) => tagId !== variables.tagId),
                        };
                    }),
                };
            });
        },
        onError: (error) => {
            console.error("Erreur lors de la suppression du tag de l'HMT :", error);
            // Vous pouvez également afficher un message d'erreur à l'utilisateur ici
        }
    });

    return (
        <Card sx={{ borderRadius: 4, border: "1px solid", borderColor: "divider", boxShadow: "0 10px 30px rgba(15,23,42,0.08)" }}>
            {dialogOpen && (
                <AjouterTagDialog
                    hmt={hmt}
                    open={dialogOpen}
                    onClose={() => setDialogOpen(false)}
                />
            )}
            {deleteDialogOpen && (
                <SupprHMTDialog
                    hmt={hmt}
                    open={deleteDialogOpen}
                    onClose={() => setDeleteDialogOpen(false)}
                />
            )}
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
                    <Typography variant="h5" sx={{ fontWeight: 700 }}>{hmt.client_id}</Typography>
                    {/* <Chip
                        size="small"
                        color="primary"
                        variant="outlined"
                        label={`${hmt.utilisateurs.length} utilisateur${hmt.utilisateurs.length > 1 ? "s" : ""}`}
                        sx={{ width: "fit-content" }}
                    /> */}
                </Stack>
                <Box sx={{ display: 'flex', gap: 2 }}>
                    <Button startIcon={<Add />} variant="contained" color="primary" onClick={() => setDialogOpen(true)}>
                        Ajouter un tag
                    </Button>
                    <IconButton color="error" onClick={() => setDeleteDialogOpen(true)}>
                        <Delete />
                    </IconButton>
                </Box>

            </Box>
            <Divider />
            <CardContent>
                <Typography variant="subtitle1" sx={{ fontWeight: 600, mb: 1.5 }}>Tags autorisés à la lecture par le HMT</Typography>
                {hmt.tags_ids.length > 0 ? (
                    <List sx={{ py: 0 }}>
                        {hmt.tags_ids.map((tagId) => (
                            <ListItem
                                key={tagId}
                                sx={{
                                    px: 1,
                                    py: 1,
                                    borderRadius: 2,
                                    "&:not(:last-of-type)": { mb: 1 },
                                    backgroundColor: "rgba(148, 163, 184, 0.08)",
                                }}
                                secondaryAction={
                                    <IconButton color="error" onClick={() => removeTagFromHMTMutation.mutate({ utilisateurId: hmt.utilisateur_id, tagId })}>
                                        <Delete />
                                    </IconButton>
                                }
                            >
                                <ListItemText
                                    primary={<Typography sx={{ fontWeight: 600 }}>{dictionnaireContext?.tags.find((tag) => tag.id === tagId)?.nom}</Typography>}
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

export function HarvesterCard({ harvester }: { harvester: Harvester }) {
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);

    return (
        <Card sx={{ borderRadius: 4, border: "1px solid", borderColor: "divider", boxShadow: "0 10px 30px rgba(15,23,42,0.08)" }}>
            {deleteDialogOpen && (
                <SupprHarvesterDialog
                    harvester={harvester}
                    open={deleteDialogOpen}
                    onClose={() => setDeleteDialogOpen(false)}
                />
            )}
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
                    <Typography variant="h5" sx={{ fontWeight: 700 }}>{harvester.client_id}</Typography>
                    <Typography color="text.secondary">{harvester.host}</Typography>
                </Stack>
                <Box sx={{ display: 'flex', gap: 2 }}>
                    <IconButton color="error" onClick={() => setDeleteDialogOpen(true)}>
                        <Delete />
                    </IconButton>
                </Box>
            </Box>
        </Card >
    );
}

function SupprHMTDialog({ hmt, open, onClose }: { hmt: HMT, open: boolean, onClose: () => void }) {
    const [error, setError] = useState<string | null>(null);

    const queryClient = useQueryClient();

    const supprHMTMutation = useMutation({
        mutationFn: ({ clientId }: { clientId: number }) => supprHMT(clientId),
        onSuccess: (_data, variables) => {
            queryClient.setQueryData<HarvesterAdmin>(['harvesterAdmin'], (previousData) => {
                if (!previousData) return previousData;

                return {
                    ...previousData,
                    hmt: previousData.hmt.filter((hmt) => hmt.id !== variables.clientId),
                };
            });
            onClose();
        },
        onError: (error) => {
            setError(error instanceof AxiosError ? error.response?.data?.message || "Une erreur est survenue." : "Une erreur est survenue.");
        }
    });

    return (
        <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm" slotProps={{ paper: { sx: { borderRadius: 3, p: 0.5 } } }}>
            <DialogTitle sx={{ fontWeight: 700 }}>Retirer un HMT.</DialogTitle>
            <DialogContent>
                <Typography color="text.secondary" sx={{ mb: 1 }}>Êtes-vous sûr de vouloir retirer le HMT "{hmt.client_id}" ?</Typography>
                <Typography color="error" sx={{ fontWeight: '700' }}>En retirant ce HMT, l'instance ayant déclaré le harvester "{hmt.client_id}" ne pourra plus récuperer les données qui lui étaient partagées.</Typography>
                {error && (
                    <Typography color="error" variant="body2" sx={{ mt: 1 }}>
                        {error}
                    </Typography>
                )}
            </DialogContent>
            <DialogActions sx={{ px: 3, pb: 2 }}>
                <Button onClick={onClose} color="inherit">Fermer</Button>
                <Button onClick={() => supprHMTMutation.mutate({ clientId: hmt.id })} color="error" variant="contained">
                    Retirer
                </Button>
            </DialogActions>
        </Dialog >
    )
}

function SupprHarvesterDialog({ harvester, open, onClose }: { harvester: Harvester, open: boolean, onClose: () => void }) {
    const [error, setError] = useState<string | null>(null);

    const queryClient = useQueryClient();

    const supprHarvesterMutation = useMutation({
        mutationFn: ({ harvesterId }: { harvesterId: number }) => supprHarvester(harvesterId),
        onSuccess: (_data, variables) => {
            queryClient.setQueryData<HarvesterAdmin>(['harvesterAdmin'], (previousData) => {
                if (!previousData) return previousData;

                return {
                    ...previousData,
                    harvesters: previousData.harvesters.filter((h) => h.id !== variables.harvesterId),
                };
            });
            onClose();
        },
        onError: (error) => {
            setError(error instanceof AxiosError ? error.response?.data?.message || "Une erreur est survenue." : "Une erreur est survenue.");
        }
    });

    return (
        <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm" slotProps={{ paper: { sx: { borderRadius: 3, p: 0.5 } } }}>
            <DialogTitle sx={{ fontWeight: 700 }}>Retirer un harvester.</DialogTitle>
            <DialogContent>
                <Typography color="text.secondary" sx={{ mb: 1 }}>Êtes-vous sûr de vouloir retirer le harvester "{harvester.client_id}" ?</Typography>
                <Typography color="error" sx={{ fontWeight: '700' }}>En retirant ce harvester, les différentes plateformes provenant de "{harvester.client_id}" ne seront plus récupérées.</Typography>
                {error && (
                    <Typography color="error" variant="body2" sx={{ mt: 1 }}>
                        {error}
                    </Typography>
                )}
            </DialogContent>
            <DialogActions sx={{ px: 3, pb: 2 }}>
                <Button onClick={onClose} color="inherit">Fermer</Button>
                <Button onClick={() => supprHarvesterMutation.mutate({ harvesterId: harvester.id })} color="error" variant="contained">
                    Retirer
                </Button>
            </DialogActions>
        </Dialog >
    )
}

function CreerHMTDialog({ open, onClose }: { open: boolean, onClose: () => void }) {
    const [clientId, setClientId] = useState<string | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [hmt, setHmt] = useState<string | null>(null);

    const queryClient = useQueryClient();

    const creerHMTMutation = useMutation({
        mutationFn: ({ clientId }: { clientId: string | null }) => {
            if (!clientId) {
                throw new Error("Le client_id ne peut pas être vide.");
            }
            return creerHMT(clientId);
        },
        onSuccess: (data) => {
            setHmt(data.hmt);
            queryClient.invalidateQueries({
                queryKey: ['harvesterAdmin'],
            });

        },
        onError: (error) => {
            setError(error instanceof AxiosError ? error.response?.data?.message || "Une erreur est survenue." : "Une erreur est survenue.");
        }
    });

    function closeAll() {
        setHmt(null);
        setClientId(null);
        setError(null);
        onClose();
    }

    const handleCopy = async () => {
        await navigator.clipboard.writeText(hmt || '');
    };

    return (
        <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm" slotProps={{ paper: { sx: { borderRadius: 3, p: 0.5 } } }}>
            <Dialog open={!!hmt} onClose={() => closeAll()} fullWidth maxWidth="sm" slotProps={{ paper: { sx: { borderRadius: 3, p: 0.5 } } }}>
                <DialogTitle sx={{ fontWeight: 700 }}>HMT créé avec succès</DialogTitle>
                <DialogContent>
                    <Typography color="text.secondary">Veuillez copier le HMT ci-dessous pour l'envoyer au client :</Typography>
                    <TextField
                        sx={{ mt: 2 }}
                        fullWidth
                        label="HMT"
                        value={hmt || ''}
                        slotProps={{
                            input: {
                                readOnly: true,
                                endAdornment: (
                                    <InputAdornment position="end">
                                        <IconButton onClick={handleCopy}>
                                            <CopyAll />
                                        </IconButton>
                                    </InputAdornment>
                                )
                            }
                        }}
                    />
                </DialogContent>
                <DialogActions sx={{ px: 3, pb: 2 }}>
                    <Button onClick={closeAll} color="primary">Fermer</Button>
                </DialogActions>
            </Dialog>
            <DialogTitle sx={{ fontWeight: 700 }}>Créer un HMT.</DialogTitle>
            <DialogContent sx={{ pt: "8px !important" }}>
                <Typography color="text.secondary">Quel sera le client_id du HMT ?</Typography>
                <TextField
                    sx={{ mt: 1 }}
                    fullWidth
                    label="Client ID"
                    value={clientId || ''}
                    onChange={(e) => {
                        setClientId(e.target.value);
                    }}
                />
                {error && (
                    <Typography color="error" variant="body2" sx={{ mt: 1 }}>
                        {error}
                    </Typography>
                )}
            </DialogContent>
            <DialogActions sx={{ px: 3, pb: 2 }}>
                <Button onClick={closeAll} color="inherit">Fermer</Button>
                <Button onClick={() => creerHMTMutation.mutate({ clientId: clientId })} color="primary" variant="contained">
                    Créer
                </Button>
            </DialogActions>
        </Dialog >
    )
}

function EntrerHMTDialog({ open, onClose }: { open: boolean, onClose: () => void }) {
    const [error, setError] = useState<string | null>(null);
    const [hmt, setHmt] = useState<string | null>(null);

    const queryClient = useQueryClient();

    const entrerHMTMutation = useMutation({
        mutationFn: ({ hmt }: { hmt: string | null }) => {
            if (!hmt) {
                throw new Error("Le hmt ne peut pas être vide.");
            }
            return entrerHMT(hmt);
        },
        onSuccess: (data) => {
            setHmt(data.hmt);
            queryClient.invalidateQueries({
                queryKey: ['harvesterAdmin'],
            });

            closeAll();
        },
        onError: (error) => {
            setError(error instanceof AxiosError ? error.response?.data?.message || "Une erreur est survenue." : "Une erreur est survenue.");
        }
    });

    function closeAll() {
        setHmt(null);
        setError(null);
        onClose();
    }

    return (
        <Dialog open={open} onClose={closeAll} fullWidth maxWidth="sm" slotProps={{ paper: { sx: { borderRadius: 3, p: 0.5 } } }}>
            <DialogTitle sx={{ fontWeight: 700 }}>Entrer un HMT</DialogTitle>
            <DialogContent sx={{ pt: "8px !important" }}>
                <Typography color="text.secondary">Veuillez entrer le HMT que l'administrateur d'une autre instance vous a transmis pour déclarer un nouveau harvester.</Typography>
                <TextField
                    sx={{ mt: 1 }}
                    fullWidth
                    label="HMT"
                    value={hmt || ''}
                    onChange={(e) => {
                        setHmt(e.target.value);
                    }}
                />
                {error && (
                    <Typography color="error" variant="body2" sx={{ mt: 1 }}>
                        {error}
                    </Typography>
                )}
            </DialogContent>
            <DialogActions sx={{ px: 3, pb: 2 }}>
                <Button onClick={closeAll} color="inherit">Fermer</Button>
                <Button onClick={() => entrerHMTMutation.mutate({ hmt: hmt })} color="primary" variant="contained">
                    Déclarer
                </Button>
            </DialogActions>
        </Dialog >
    )
}

function AjouterTagDialog({ hmt, open, onClose }: { hmt: HMT, open: boolean, onClose: () => void }) {
    const dictionnaireContext = useContext(DictionnaireContext);
    const [selectedTagId, setSelectedTagId] = useState<number | null>(null);
    const [error, setError] = useState<string | null>(null);

    const queryClient = useQueryClient();

    const ajouterTagsMutation = useMutation({
        mutationFn: ({ tagId, utilisateurId }: { tagId: number; utilisateurId: number }) => ajouterTagAuUser(tagId, utilisateurId),
        onSuccess: (_data, variables) => {
            queryClient.setQueryData<HarvesterAdmin>(['harvesterAdmin'], (previousData) => {
                if (!previousData) return previousData;

                const alreadyInHMT = previousData.hmt.find((hmt) => hmt.utilisateur_id === variables.utilisateurId)?.tags_ids.includes(variables.tagId);
                if (alreadyInHMT) return previousData;

                return {
                    ...previousData,
                    hmt: previousData.hmt.map((hmt) => {
                        if (hmt.utilisateur_id !== variables.utilisateurId) return hmt;

                        return {
                            ...hmt,
                            tags_ids: [...hmt.tags_ids, variables.tagId],
                        };
                    }),
                };
            });
            onClose();
        },
        onError: (error) => {
            setError(error instanceof AxiosError ? error.response?.data?.message || "Une erreur est survenue." : "Une erreur est survenue.");
        }
    });

    return (
        <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm" slotProps={{ paper: { sx: { borderRadius: 3, p: 0.5 } } }}>
            <DialogTitle sx={{ fontWeight: 700 }}>Ajouter un tag</DialogTitle>
            <DialogContent sx={{ pt: "8px !important" }}>
                <Typography color="text.secondary">Quel tag souhaitez-vous ajouter au HMT "{hmt.client_id}" ?</Typography>
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
                {error && (
                    <Typography color="error" variant="body2" sx={{ mt: 1 }}>
                        {error}
                    </Typography>
                )}
            </DialogContent>
            <DialogActions sx={{ px: 3, pb: 2 }}>
                <Button onClick={onClose} color="inherit">Fermer</Button>
                <Button onClick={() => ajouterTagsMutation.mutate({ tagId: selectedTagId!, utilisateurId: hmt.utilisateur_id })} color="primary" variant="contained">Ajouter</Button>
            </DialogActions>
        </Dialog >
    )
}

export default function HarvesterAdmin() {
    const harvesterAdmin = useQuery<HarvesterAdmin>({
        queryKey: ['harvesterAdmin'],
        queryFn: fetchHarvesterAdmin,
    });

    const [creerHMTDialogOpen, setCreerHMTDialogOpen] = useState(false);
    const [entrerHMTDialogOpen, setEntrerHMTDialogOpen] = useState(false);

    return (
        <AdminPageShell
            title="Gestion des Harvesters et HMT"
            subtitle="Octroie aux utilisateurs la permission d'accéder à certaines plateformes en fonction des tags qui leur sont associés."
        >
            {harvesterAdmin.isPending ? (
                <Typography sx={{ px: 1 }}>Chargement des harvesters...</Typography>
            ) : harvesterAdmin.isError ? (
                <Typography color="error" sx={{ px: 1 }}>Erreur lors du chargement des harvesters.</Typography>
            ) : null}
            {harvesterAdmin.isSuccess && (
                <>
                    <Box sx={{ borderRadius: 3, border: "1px solid", borderColor: "divider", p: 2.5, backgroundColor: "rgba(148,163,184,0.08)" }}>
                        <EntrerHMTDialog
                            open={entrerHMTDialogOpen}
                            onClose={() => {
                                setEntrerHMTDialogOpen(false);
                            }}
                        />
                        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
                            <Typography variant="h6" sx={{ fontWeight: 700 }}>Liste des Harvesters</Typography>
                            <Button variant="contained" color="primary" onClick={() => {
                                setEntrerHMTDialogOpen(true);
                            }}>
                                <Add sx={{ mr: 1 }} />
                                Entrer un HMT
                            </Button>
                        </Box>
                        {harvesterAdmin.data.harvesters.length === 0 && (
                            <Paper sx={{ p: 2, borderRadius: 3, border: "1px dashed", borderColor: "divider", mb: 2 }}>
                                <Typography color="text.secondary">Aucun harvester n'est enregistré sur cette instance.</Typography>
                            </Paper>
                        )}
                        <Stack spacing={2.5}>
                            {harvesterAdmin.data?.harvesters.map((harvester) => (
                                <HarvesterCard key={harvester.id} harvester={harvester} />
                            ))}
                        </Stack>
                    </Box>
                    <Box sx={{ borderRadius: 3, border: "1px solid", borderColor: "divider", p: 2.5, backgroundColor: "rgba(148,163,184,0.08)" }}>
                        <CreerHMTDialog
                            open={creerHMTDialogOpen}
                            onClose={() => {
                                setCreerHMTDialogOpen(false);
                            }}
                        />
                        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
                            <Typography variant="h6" sx={{ fontWeight: 700 }}>Liste des HMT</Typography>
                            <Button variant="contained" color="primary" onClick={() => {
                                setCreerHMTDialogOpen(true);
                            }}>
                                <Add sx={{ mr: 1 }} />
                                Créer un HMT
                            </Button>
                        </Box>
                        {harvesterAdmin.data.hmt.length === 0 && (
                            <Paper sx={{ p: 2, borderRadius: 3, border: "1px dashed", borderColor: "divider", mb: 2 }}>
                                <Typography color="text.secondary">Personne n'harvest sur cette instance.</Typography>
                            </Paper>
                        )}
                        <Stack spacing={2.5}>
                            {harvesterAdmin.data?.hmt.map((tag) => (
                                <HMTCard key={tag.id} hmt={tag} />
                            ))}
                        </Stack>
                    </Box>
                </>
            )
            }
        </AdminPageShell >
    );
}