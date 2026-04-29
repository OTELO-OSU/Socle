import { Box, Button, Dialog, FormControl, FormLabel, TextField, Typography } from "@mui/material";
import { useContext, useState } from "react";
import { creerPlateforme } from "../services/plateformeService";
import { SnackbarContext } from "../context/snackbarContext";
import { useNavigate } from "react-router-dom";

export default function PlateformeAdmin() {
    const [dialogOpen, setDialogOpen] = useState(false);
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
    return (
        <>
            <Box sx={{ display: 'flex', justifyContent: 'end', alignItems: 'center' }}>
                <Button variant="contained" color="primary" onClick={() => setDialogOpen(true)}>
                    Créer une nouvelle plateforme
                </Button>

            </Box>
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
        </>
    )
}