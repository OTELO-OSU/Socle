import { useCallback, useEffect, useState } from "react";
import { fetchPlateformeListeParPage, type PlateformeLight } from "../services/plateformeService";
import { Box, Button, Card, CardActions, CardContent, Container, Divider, Link, Pagination, Stack, TextField, Typography } from "@mui/material";
import { Link as LinkRouter } from "react-router-dom";

export function ListePlateforme() {
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

    return (
        <Container sx={{ py: 4 }}>
            <Typography variant="h5" component={"h1"}>Liste des plateformes</Typography>
            <Stack direction="row" divider={<Divider orientation="vertical" flexItem />} spacing={2} sx={{ my: 2, alignItems: "center" }}>
                <TextField
                    type="text"
                    placeholder="Rechercher une plateforme..."
                    value={recherche}
                    onChange={(e) => setRecherche(e.target.value)}
                    sx={{ width: '55%' }}
                />
                <Pagination
                    count={totalPages}
                    page={page}
                    onChange={(e, newPage) => setPage(newPage)}
                />
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
    );
}