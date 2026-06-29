import { useCallback, useEffect, useState } from "react";
import { fetchPlateformeListeParPage, type PlateformeLight } from "../services/plateformeService";
import { Box, Button, Card, CardActions, CardContent, Container, Divider, Link, List, Pagination, Stack, Tab, Tabs, TextField, Typography } from "@mui/material";
import { Link as LinkRouter } from "react-router-dom";
import { Done, Group, Save, Verified } from "@mui/icons-material";
import { downloadSinglePlateforme } from "../services/exportService";
import { allyProps } from "../components/formulairePlateforme/allyProps";
import { CustomTabPanel } from "../components/CustomTabPanel";
import { fetchPlateformeHarveste } from "../services/harvesterService";

function IconeTitre({ brouillon }: { brouillon: boolean }) {
    if (brouillon) {
        return <Save />
    } else {
        return <Done />
    }
}

function PlateformeCard({ plateforme, harvester, host }: { plateforme: PlateformeLight; harvester: boolean; host?: string }) {
    const personnel = plateforme.lastHistorique.personnels

    const responsableScientifique = personnel.filter(p => p.responsable_scientifique);
    const responsableTechnique = personnel.filter(p => p.responsable_technique);
    const responsablePlateforme = personnel.filter(p => p.responsable_plateforme);



    return (
        <>
            <Card key={plateforme.id}>
                <CardContent>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                        <Box>
                            <Typography variant="h6" sx={{ display: 'flex', alignItems: 'center', gap: 0.5, fontWeight: 'bold' }}><IconeTitre brouillon={plateforme.lastHistorique.brouillon} />{plateforme.lastHistorique.nom_officiel}</Typography>
                            {plateforme.instrument_national && (
                                <Typography variant="subtitle1" color="textPrimary" sx={{ display: "flex", alignItems: "center", gap: 0.5, mt: 1, fontWeight: "bold" }}>
                                    <Verified fontSize="small" />
                                    Instrument national
                                </Typography>
                            )}
                            {responsableScientifique.length > 0 && (
                                <>
                                    <Box sx={{ mt: 2 }}>
                                        <Typography variant="body1" color="textPrimary" sx={{ display: 'flex', alignItems: 'center', gap: 0.5, fontWeight: "medium" }}>
                                            <Group />
                                            Responsable Scientifique :
                                        </Typography>
                                        <List sx={{ px: 4 }}>
                                            {responsableScientifique.map((personnel) => (
                                                <Typography key={personnel.utilisateur_id} variant="body2" color="textPrimary">
                                                    {personnel.utilisateur.prenom} {personnel.utilisateur.nom} | {personnel.utilisateur.email}
                                                </Typography>
                                            ))}
                                        </List>
                                    </Box>
                                </>)}
                            {responsableTechnique.length > 0 && (
                                <>
                                    <Box sx={{ mt: 2 }}>
                                        <Typography variant="body1" color="textPrimary" sx={{ display: 'flex', alignItems: 'center', gap: 0.5, fontWeight: "medium" }}>
                                            <Group />
                                            Responsable Technique :
                                        </Typography>
                                        <List sx={{ px: 4 }}>
                                            {responsableTechnique.map((personnel) => (
                                                <Typography key={personnel.utilisateur_id} variant="body2" color="textPrimary">
                                                    {personnel.utilisateur.prenom} {personnel.utilisateur.nom} | {personnel.utilisateur.email}
                                                </Typography>
                                            ))}
                                        </List>
                                    </Box>
                                </>)}
                            {responsablePlateforme.length > 0 && (
                                <>
                                    <Box sx={{ mt: 2 }}>
                                        <Typography variant="body1" color="textPrimary" sx={{ display: 'flex', alignItems: 'center', gap: 0.5, fontWeight: "medium" }}>
                                            <Group />
                                            Responsable Plateforme :
                                        </Typography>
                                        <List sx={{ px: 4 }}>
                                            {responsablePlateforme.map((personnel) => (
                                                <Typography key={personnel.utilisateur_id} variant="body2" color="textPrimary">
                                                    {personnel.utilisateur.prenom} {personnel.utilisateur.nom} | {personnel.utilisateur.email}
                                                </Typography>
                                            ))}
                                        </List>
                                    </Box>
                                </>)}
                        </Box>
                        {plateforme.lastHistorique.logo_url &&
                            <Box>
                                <img src={plateforme.lastHistorique.logo_url} alt={`${plateforme.lastHistorique.nom_officiel} logo`} style={{ maxHeight: 126 }} />
                            </Box>
                        }
                    </Box>
                </CardContent>
                <CardActions>
                    <Link component={LinkRouter} to={`/plateforme/edition?id=${plateforme.id}&harvester=${harvester}&host=${host}`} color="primary" sx={{ cursor: 'pointer' }}>
                        Voir les détails
                    </Link>
                    <Link component={Button} onClick={() => downloadSinglePlateforme(plateforme.id)}>
                        Exporter
                    </Link>
                </CardActions>
            </Card >
        </>
    )
}

export function ListePlateforme() {
    const [plateformes, setPlateformes] = useState<PlateformeLight[]>([]);
    const [plateformesHarvestees, setPlateformesHarvestees] = useState<PlateformeLight[]>([]);
    const [recherche, setRecherche] = useState('');
    const [page, setPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [isLoading, setIsLoading] = useState(false);
    const [isLoadingHarvestees, setIsLoadingHarvestees] = useState(false);
    const [tabs, setTabs] = useState(0);

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

    const fetchPlateformesHarveste = useCallback(async () => {
        setIsLoadingHarvestees(true);
        try {
            const data = await fetchPlateformeHarveste();
            setPlateformesHarvestees(data);
        } catch (error) {
            console.error("Erreur lors du chargement des plateformes :", error);
        } finally {
            setIsLoadingHarvestees(false);
        }
    }, []);

    useEffect(() => {
        fetchPlateformes();
        fetchPlateformesHarveste();
    }, [fetchPlateformes, fetchPlateformesHarveste]);

    return (
        <Container sx={{ py: 4 }}>
            <Typography variant="h5" component={"h1"}>Liste des plateformes</Typography>
            <Box sx={{ borderBottom: 1, my: 2, borderColor: 'divider' }}>
                <Tabs value={tabs} onChange={(_, newValue) => setTabs(newValue)} aria-label="basic tabs example">
                    <Tab label="Locales" {...allyProps(0)} />
                    <Tab label="Moissonnées" {...allyProps(1)} />
                </Tabs>
            </Box>
            <CustomTabPanel value={tabs} index={0}>
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
                        onChange={(_, newPage) => setPage(newPage)}
                    />
                </Stack>

                {isLoading ? (
                    <Typography component={'p'} variant="h6">Chargement...</Typography>
                ) : (
                    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                        {plateformes.map((plateforme) => (
                            <PlateformeCard key={plateforme.id} harvester={false} plateforme={plateforme} />
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
            </CustomTabPanel>

            <CustomTabPanel value={tabs} index={1}>
                {isLoadingHarvestees ? (
                    <Typography component={'p'} variant="h6">Chargement...</Typography>
                ) : (
                    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                        {Object.entries(plateformesHarvestees).map(([host, plateforme]) => (
                            <PlateformeCard harvester={true} key={host + '/' + plateforme.id} host={host} plateforme={plateforme} />
                        ))}
                    </Box>
                )}
            </CustomTabPanel>
        </Container>
    );
}