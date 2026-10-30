import { useCallback, useContext, useEffect, useState } from "react";
import { fetchPlateformeListeParPage, type PlateformeLight } from "../services/plateformeService";
import { Box, Button, Card, CardActions, CardContent, Container, Divider, LinearProgress, Link, List, Pagination, Stack, Tab, Tabs, TextField, Typography } from "@mui/material";
import { Link as LinkRouter } from "react-router-dom";
import { BarChart, Description, Done, Download, Group, Launch, Save, Verified } from "@mui/icons-material";
import { downloadAllPlateformes, downloadPlateformesByTag, downloadPlateformesByUnite, downloadSinglePlateforme } from "../services/exportService";
import { allyProps } from "../components/formulairePlateforme/allyProps";
import { CustomTabPanel } from "../components/CustomTabPanel";
import { fetchPlateformeHarveste } from "../services/harvesterService";
import { AuthContext } from "../context/authContext";

function IconeTitre({ brouillon }: { brouillon: boolean }) {
    if (brouillon) {
        return <Save />
    } else {
        return <Done />
    }
}

function PlateformeCard({ plateforme, harvester, host }: { plateforme: PlateformeLight; harvester: boolean; host?: string }) {
    const personnel = plateforme.dernier_historique.personnels

    const responsableScientifique = personnel.filter(p => p.responsable_scientifique);
    const responsableTechnique = personnel.filter(p => p.responsable_technique);
    const responsablePlateforme = personnel.filter(p => p.responsable_plateforme);



    return (
        <>
            <Card key={plateforme.id}>
                <CardContent>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                        <Box>
                            <Typography variant="h6" sx={{ display: 'flex', alignItems: 'center', gap: 0.5, fontWeight: 'bold' }}><IconeTitre brouillon={plateforme.dernier_historique.brouillon} />{plateforme.dernier_historique.nom_officiel}</Typography>
                            {plateforme.instrument_national && (
                                <Typography variant="subtitle1" color="textPrimary" sx={{ display: "flex", alignItems: "center", gap: 0.5, mt: 1, fontWeight: "bold" }}>
                                    <Verified fontSize="small" />
                                    Instrument national
                                </Typography>
                            )}
                            {plateforme.dernier_historique.description && (
                                <Box sx={{ mt: 2 }}>
                                    <Typography variant="body1" color="textPrimary" sx={{ display: 'flex', alignItems: 'center', gap: 0.5, fontWeight: "medium" }}>
                                        <Description />
                                        Description :
                                    </Typography>
                                    <Typography
                                        variant="body2" color="textPrimary" sx={{ px: 4 }}
                                    >
                                        {plateforme.dernier_historique.description.length > 200 ? plateforme.dernier_historique.description.substring(0, 500) + "..." : plateforme.dernier_historique.description}
                                    </Typography>
                                </Box>
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
                            <Box sx={{ mt: 2 }}>
                                <Typography variant="body1" color="textPrimary" sx={{ display: 'flex', alignItems: 'center', gap: 0.5, fontWeight: "medium" }}>
                                    <BarChart />
                                    Taux de remplissage : {plateforme.pourcentage_remplissage ?? 0}%
                                </Typography>
                                <LinearProgress variant="determinate" value={plateforme.pourcentage_remplissage ?? 0} sx={{ height: 10, width: "500px", mt: 2, borderRadius: 5 }} />
                            </Box>
                        </Box>
                        {plateforme.dernier_historique.logo_url &&
                            <Box>
                                <img src={plateforme.dernier_historique.logo_url} alt={`${plateforme.dernier_historique.nom_officiel} logo`} style={{ maxHeight: 126 }} />
                            </Box>
                        }
                    </Box>
                </CardContent>
                <CardActions>
                    <Button component={LinkRouter} to={`/plateforme/edition?id=${plateforme.id}&harvester=${harvester}&host=${host}`} color="primary" sx={{ cursor: 'pointer' }}>
                        <Launch fontSize="small" sx={{ mr: 0.5 }} />
                        Voir les détails
                    </Button>
                    <Button component={Link} onClick={() => downloadSinglePlateforme({ plateformeId: plateforme.id, harvester: harvester, host: host! })} color="primary" sx={{ cursor: 'pointer' }}>
                        <Download fontSize="small" sx={{ mr: 0.5 }} />
                        Exporter
                    </Button>
                </CardActions>
            </Card >
        </>
    )
}

export function ListePlateforme() {
    const [plateformes, setPlateformes] = useState<PlateformeLight[]>([]);
    const [plateformesHarvestees, setPlateformesHarvestees] = useState<Record<string, PlateformeLight[]>>({});
    const [recherche, setRecherche] = useState('');
    const [page, setPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [isLoading, setIsLoading] = useState(false);
    const [isLoadingHarvestees, setIsLoadingHarvestees] = useState(false);
    const [tabs, setTabs] = useState(0);
    const [exportTagId, setExportTagId] = useState('');
    const [exportRorId, setExportRorId] = useState('');
    const [exportEnCours, setExportEnCours] = useState(false);
    const [exportErreur, setExportErreur] = useState<string | null>(null);

    const lancerExport = async (action: () => Promise<void>) => {
        setExportEnCours(true);
        setExportErreur(null);
        try {
            await action();
        } catch (error) {
            console.error("Erreur lors de l'export :", error);
            setExportErreur("L'export a échoué (droits insuffisants ou instance indisponible).");
        } finally {
            setExportEnCours(false);
        }
    };

    const tagIdValide = /^[1-9]\d*$/.test(exportTagId.trim());
    const rorIdValide = exportRorId.trim() !== '';

    const authContext = useContext(AuthContext);

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
            {(authContext?.estAdmin || authContext?.estDu) && (
                <Stack direction="row" spacing={2} sx={{ my: 2, alignItems: "center", flexWrap: "wrap", rowGap: 2 }}>
                    {authContext?.estAdmin && (
                        <Button variant="contained" startIcon={<Download />} disabled={exportEnCours} onClick={() => lancerExport(downloadAllPlateformes)}>
                            Tout exporter
                        </Button>
                    )}
                    <TextField
                        size="small"
                        type="number"
                        label="ID du tag"
                        value={exportTagId}
                        onChange={(e) => setExportTagId(e.target.value)}
                        sx={{ width: 140 }}
                    />
                    <Button variant="outlined" startIcon={<Download />} disabled={exportEnCours || !tagIdValide} onClick={() => lancerExport(() => downloadPlateformesByTag(Number(exportTagId)))}>
                        Exporter par tag
                    </Button>
                    {(authContext?.estAdmin || authContext?.estDu) && (
                        <>
                            <TextField
                                size="small"
                                label="ROR de l'unité"
                                value={exportRorId}
                                onChange={(e) => setExportRorId(e.target.value)}
                                sx={{ width: 180 }}
                            />
                            <Button variant="outlined" startIcon={<Download />} disabled={exportEnCours || !rorIdValide} onClick={() => lancerExport(() => downloadPlateformesByUnite(exportRorId.trim()))}>
                                Exporter par unité
                            </Button>
                        </>
                    )}
                </Stack>
            )}
            {exportEnCours && <LinearProgress sx={{ mb: 2 }} />}
            {exportErreur && <Typography color="error" sx={{ mb: 2 }}>{exportErreur}</Typography>}
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
                        {plateformes.length === 0 && (
                            <Typography variant="body1" sx={{ mb: 2 }}>
                                Aucune plateforme locale trouvée.
                            </Typography>
                        )}
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
                    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
                        {Object.entries(plateformesHarvestees).map(([host, plateformes]) => (
                            <Box key={host}>
                                <Typography variant="h6" sx={{ mb: 2 }}>
                                    {host}
                                </Typography>

                                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                                    {plateformes.map((plateforme) => (
                                        <PlateformeCard
                                            key={`${host}/${plateforme.id}`}
                                            harvester={true}
                                            host={host}
                                            plateforme={plateforme}
                                        />
                                    ))}
                                </Box>
                            </Box>
                        ))}
                        {Object.entries(plateformesHarvestees).length === 0 && (
                            <Typography variant="body1">
                                Aucune plateforme moissonnée trouvée.
                            </Typography>
                        )}
                    </Box>
                )}
            </CustomTabPanel>
        </Container>
    );
}