import { Card, Container, FormControl, FormLabel, TextField, Typography } from "@mui/material";

export default function ModificationPlateforme() {
    return (
        <Container sx={{ py: 4 }}>
            <Card sx={{ display: "flex", flexDirection: "column", p: 4 }}>
                <Typography variant="h4" component="h1">
                    Modifier une Plateforme
                </Typography>
                <FormControl fullWidth sx={{ mt: 2 }}>
                    <FormLabel htmlFor="nom">Nom de la plateforme</FormLabel>
                    <TextField type="text" id="nom" name="nom" required />
                </FormControl>
            </Card>
        </Container>
    )
}