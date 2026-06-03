import { Alert, Box, Button, Card, CardContent, Container, Dialog, DialogActions, DialogContent, DialogTitle, FormControl, IconButton, TextField, Typography } from "@mui/material";
import { useCallback, useEffect, useState } from "react";
import { fetchAnneeDeSaisie, setAnneeDeSaisie } from "../services/saisieService.ts";
import { Add, Send } from "@mui/icons-material";

export default function SaisieAdmin() {
    const [annee, setAnnee] = useState<number | null>(null);
    const [isLoading, setIsLoading] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isConfirmDialogOpen, setIsConfirmDialogOpen] = useState(false);

    const fetchAnnee = useCallback(async () => {
        setIsLoading(true);
        try {
            const { annee } = await fetchAnneeDeSaisie();
            setAnnee(annee);
        } catch (error) {
            console.error("Erreur lors du chargement de l'année de saisie :", error);
        } finally {
            setIsLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchAnnee();
    }, [fetchAnnee]);

    const handleIncrementAnnee = useCallback(async () => {
        if (annee === null) {
            return;
        }

        setIsSubmitting(true);
        try {
            await setAnneeDeSaisie(Number(annee) + 1);
            await fetchAnnee();
        } catch (error) {
            console.error("Erreur lors de l'incrémentation de l'année de saisie :", error);
        } finally {
            setIsSubmitting(false);
        }
    }, [annee, fetchAnnee]);

    return (
        <Container sx={{ py: 4 }}>
            <Dialog open={isConfirmDialogOpen} onClose={() => setIsConfirmDialogOpen(false)} fullWidth maxWidth="sm">
                <DialogTitle sx={{ color: 'error.main' }}>Confirmer l'incrémentation</DialogTitle>
                <DialogContent>
                    <Alert severity="error" sx={{ mb: 2 }}>
                        Cette action incrémente l'année de saisie et il sera difficile de revenir en arrière.
                    </Alert>
                    <Typography>
                        Confirmez-vous le passage de {annee} à {annee !== null ? Number(annee) + 1 : "l'année suivante"} ?
                    </Typography>
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setIsConfirmDialogOpen(false)} disabled={isSubmitting}>Annuler</Button>
                    <Button
                        color="error"
                        variant="contained"
                        onClick={() => {
                            setIsConfirmDialogOpen(false);
                            void handleIncrementAnnee();
                        }}
                        disabled={isSubmitting}
                    >
                        Confirmer
                    </Button>
                </DialogActions>
            </Dialog>
            <Typography variant="h5" component={"h1"} sx={{ mb: 2 }}>Année de Saisie</Typography>
            <Typography component={'p'} variant="body1">La définition d'une année de saisie permet aux plateformes d'être associées à une année spécifique afin de pouvoir en historiser les données. Sans date de saisie définie, il n'est pas possible de procéder à la saisie de données dans les plateformes.</Typography>
            <Typography component={'p'} color="error" variant="body1" sx={{ fontWeight: 'bold', mt: 1 }}>Il est difficilement possible de revenir en arrière une fois l'année de saisie incrémentée.</Typography>
            <Typography component={'p'} color="error" variant="body1" sx={{ fontWeight: 'bold' }}>
                Soyez donc prudent dans votre choix.
            </Typography>

            {isLoading ? (
                <Typography component={'p'} variant="h6">Chargement...</Typography>
            ) : annee !== null ? (
                <Card sx={{ mt: 3, maxWidth: 560 }}>
                    <CardContent>
                        <Typography component={'p'} variant="overline" color="text.secondary">
                            Année de saisie actuelle
                        </Typography>
                        <Typography component={'p'} variant="h4" sx={{ mb: 2 }}>
                            {annee}
                        </Typography>
                        <Button
                            variant="contained"
                            startIcon={<Add />}
                            color="error"
                            onClick={() => setIsConfirmDialogOpen(true)}
                            disabled={isSubmitting}
                        >
                            Incrémenter l'année
                        </Button>
                    </CardContent>
                </Card>
            ) : (
                <Box sx={{ mt: 2 }}>
                    <Alert severity="error">Aucune date de saisie n'est déclarée, choissisez une année de saisie.
                        <Box sx={{ display: 'flex', mt: 2 }} component={"form"} onSubmit={async (e) => {
                            e.preventDefault()
                            e.stopPropagation()

                            // Récuperer annee
                            const annee = e.currentTarget.annee.value as number
                            setIsSubmitting(true)
                            await setAnneeDeSaisie(annee)
                            await fetchAnnee()
                            setIsSubmitting(false)
                        }}>
                            <FormControl >
                                <TextField
                                    name="annee"
                                    type="number"
                                    placeholder="YYYY"
                                    value={annee || undefined}
                                />
                            </FormControl>
                            <IconButton type="submit" disabled={isSubmitting}><Send /></IconButton>
                        </Box>
                    </Alert>
                </Box>


            )
            }
        </Container >
    )
}