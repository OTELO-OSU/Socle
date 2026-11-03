import { useQuery } from "@tanstack/react-query";
import { Box, Button, Card, CardContent, FormControl, IconButton, InputLabel, Link, MenuItem, Select, Stack, ToggleButton, ToggleButtonGroup, Typography } from "@mui/material";
import { useContext, useEffect, useMemo, useState } from "react";
import MapIcon from "@mui/icons-material/Map";
import ViewListIcon from "@mui/icons-material/ViewList";
import LanguageIcon from "@mui/icons-material/Language";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import ScienceIcon from "@mui/icons-material/Science";
import ArrowDownwardIcon from "@mui/icons-material/ArrowDownward";
import ArrowUpwardIcon from "@mui/icons-material/ArrowUpward";
import { MapContainer, Marker, Popup, TileLayer, useMap } from "react-leaflet";
import MarkerClusterGroup from "react-leaflet-cluster";
import { DictionnaireContext } from "../context/dictionnaireContext";
import { ApplicationSettingsContext } from "../context/applicationSettingsContext";
import { fetchPlateformePublique } from "../services/plateformeService";

type PlateformeMapPoint = {
    id: number;
    instanceHost: string;
    nom: string;
    siteWeb: string | null;
    villeId: number | null;
    villeNom: string | null;
    latitude: number | null;
    longitude: number | null;
    laboratoire: number | null;
    laboratoireNom: string | null;
};

type SortField = "laboratoire" | "siteWeb" | "ville" | "nom";
type SortDirection = "asc" | "desc";

function normalizeWebsiteUrl(siteWeb: string): string {
    if (/^https?:\/\//i.test(siteWeb)) {
        return siteWeb;
    }

    return `https://${siteWeb}`;
}

async function getPlateformes(): Promise<PlateformeMapPoint[]> {
    const allPlateformes = await fetchPlateformePublique();

    return allPlateformes
        .map((plateforme) => ({
            id: plateforme.id,
            instanceHost: plateforme.instance_host ?? "local",
            nom: plateforme.dernier_historique.nom_officiel,
            siteWeb: plateforme.dernier_historique.site_web,
            villeId: plateforme.dernier_historique.ville_id,
            villeNom: plateforme.dernier_historique.ville_nom,
            latitude: plateforme.dernier_historique.latitude,
            longitude: plateforme.dernier_historique.longitude,
            laboratoire: plateforme.dernier_historique.laboratoire_operateur_id ? plateforme.dernier_historique.laboratoire_operateur_id : null,
            laboratoireNom: plateforme.dernier_historique.laboratoire_operateur_nom,
        }));
}

function hasCoordinates(point: PlateformeMapPoint): point is PlateformeMapPoint & { latitude: number; longitude: number } {
    return typeof point.latitude === "number" && typeof point.longitude === "number";
}

function FitMapToPoints({ points }: { points: Array<{ latitude: number; longitude: number }> }) {
    const map = useMap();

    useEffect(() => {
        if (points.length === 0) {
            return;
        }

        map.fitBounds(points.map((point) => [point.latitude, point.longitude] as [number, number]), {
            padding: [30, 30],
            maxZoom: 8,
        });
    }, [map, points]);

    return null;
}

export default function Home() {
    const dictionnaire = useContext(DictionnaireContext);
    const applicationSettings = useContext(ApplicationSettingsContext);
    const { data: points = [] } = useQuery({
        queryKey: ["plateformes", "map-points"],
        queryFn: getPlateformes,
    });
    const [viewModeOverride, setViewModeOverride] = useState<"list" | "map" | null>(null);
    const [sortField, setSortField] = useState<SortField>("nom");
    const [sortDirection, setSortDirection] = useState<SortDirection>("asc");
    const viewMode = viewModeOverride ?? (applicationSettings?.settings.vue_accueil === "carte" ? "map" : "list");

    const villesById = useMemo(() => {
        return new Map(dictionnaire.ville.map((ville) => [ville.id, ville.nom]));
    }, [dictionnaire.ville]);

    const laboById = useMemo(() => {
        return new Map(dictionnaire.unite.map((unite) => [unite.id, unite.nom]));
    }, [dictionnaire.unite]);

    const sortedPoints = useMemo(() => {
        const getSortableValue = (point: PlateformeMapPoint, field: SortField) => {
            switch (field) {
                case "siteWeb":
                    return point.siteWeb ?? "";
                case "ville":
                    return point.villeNom ?? (point.villeId ? (villesById.get(point.villeId) ?? "") : "");
                case "laboratoire":
                    return point.laboratoireNom ?? (point.laboratoire ? (laboById.get(point.laboratoire) ?? "") : "");
                case "nom":
                    return point.nom;
            }
        };

        return [...points].sort((left, right) => {
            const leftValue = getSortableValue(left, sortField);
            const rightValue = getSortableValue(right, sortField);
            const comparison = leftValue.localeCompare(rightValue, "fr", { sensitivity: "base" });

            if (comparison !== 0) {
                return sortDirection === "asc" ? comparison : -comparison;
            }

            return left.nom.localeCompare(right.nom, "fr", { sensitivity: "base" });
        });
    }, [laboById, points, sortDirection, sortField, villesById]);

    const mapPoints = useMemo(() => points.filter(hasCoordinates), [points]);
    const viewToggle = (
        <ToggleButtonGroup
            value={viewMode}
            exclusive
            onChange={(_, value: "list" | "map" | null) => value && setViewModeOverride(value)}
            size="small"
            aria-label="Mode d'affichage"
        >
            <ToggleButton value="list" aria-label="Afficher la liste">
                <ViewListIcon />
            </ToggleButton>
            <ToggleButton value="map" aria-label="Afficher la carte">
                <MapIcon />
            </ToggleButton>
        </ToggleButtonGroup>
    );

    return (
        <Box sx={{ position: "relative", width: "100%", height: "100%" }}>
            {viewMode === "map" && (
                <Box sx={{ position: "absolute", top: 16, right: 16, zIndex: 1000, backgroundColor: "background.paper", borderRadius: 2, boxShadow: 3 }}>
                    {viewToggle}
                </Box>
            )}

            {viewMode === "list" ? (
                <Box sx={{ height: "100%", overflow: "auto", px: { xs: 2, sm: 4, md: 6 }, pt: 3, pb: 5 }}>
                    <Stack direction={{ xs: "column", sm: "row" }} spacing={2} sx={{ maxWidth: 1000, mx: "auto", mb: 3, p: 1.5, border: "1px solid", borderColor: "divider", borderRadius: 2, backgroundColor: "background.paper", alignItems: { xs: "stretch", sm: "center" } }}>
                        <Typography variant="h5" sx={{ fontWeight: 700, flexGrow: 1, color: "text.primary" }}>
                            Les plateformes
                        </Typography>
                        <FormControl size="small" sx={{ minWidth: 180 }}>
                            <InputLabel id="sort-platforms-label">Trier par</InputLabel>
                            <Select
                                labelId="sort-platforms-label"
                                value={sortField}
                                label="Trier par"
                                onChange={(event) => setSortField(event.target.value as SortField)}
                            >
                                <MenuItem value="nom">Nom</MenuItem>
                                <MenuItem value="laboratoire">Laboratoire</MenuItem>
                                <MenuItem value="ville">Ville</MenuItem>
                                <MenuItem value="siteWeb">Site web</MenuItem>
                            </Select>
                        </FormControl>
                        <IconButton
                            color="primary"
                            aria-label={sortDirection === "asc" ? "Ordre croissant" : "Ordre décroissant"}
                            onClick={() => setSortDirection((direction) => direction === "asc" ? "desc" : "asc")}
                        >
                            {sortDirection === "asc" ? <ArrowUpwardIcon /> : <ArrowDownwardIcon />}
                        </IconButton>
                        {viewToggle}
                    </Stack>

                    <Stack spacing={2} sx={{ maxWidth: 1000, mx: "auto" }}>
                        {sortedPoints.map((point) => {
                            const laboratoire = point.laboratoireNom ?? (point.laboratoire ? laboById.get(point.laboratoire) : null);
                            const ville = point.villeNom ?? (point.villeId ? villesById.get(point.villeId) : null);
                            const initials = point.nom.trim().slice(0, 2).toUpperCase();

                            return (
                                <Card
                                    key={`${point.instanceHost}-${point.id}`}
                                    component="article"
                                    sx={{
                                        border: "1px solid",
                                        borderColor: "divider",
                                        borderRadius: 2,
                                        transition: "border-color 160ms ease, box-shadow 160ms ease",
                                        "&:hover": { borderColor: "primary.main", boxShadow: 3 },
                                    }}
                                >
                                    <CardContent sx={{ display: "flex", flexDirection: { xs: "column", sm: "row" }, gap: 2.5, alignItems: { xs: "stretch", sm: "center" }, p: { xs: 2.5, sm: 3 } }}>
                                        <Box sx={{ width: 52, height: 52, flexShrink: 0, display: "grid", placeItems: "center", borderRadius: 2, backgroundColor: "primary.main", color: "primary.contrastText", fontWeight: 700, fontSize: "1.1rem" }}>
                                            {initials}
                                        </Box>
                                        <Box sx={{ minWidth: 0, flexGrow: 1 }}>
                                            <Typography variant="h6" component="h2" sx={{ fontWeight: 700, lineHeight: 1.3, color: "text.primary", overflowWrap: "anywhere", mb: 1.25 }}>
                                                {point.nom}
                                            </Typography>
                                            <Stack direction={{ xs: "column", md: "row" }} spacing={{ xs: 0.75, md: 3 }} sx={{ color: "text.secondary" }}>
                                                <Stack direction="row" spacing={1} sx={{ alignItems: "flex-start", minWidth: 0 }}>
                                                <ScienceIcon fontSize="small" color="primary" />
                                                    <Typography variant="body2" sx={{ overflowWrap: "anywhere" }}>{laboratoire ?? "Laboratoire inconnu"}</Typography>
                                                </Stack>
                                                <Stack direction="row" spacing={1} sx={{ alignItems: "flex-start", minWidth: 0 }}>
                                                    <LocationOnIcon fontSize="small" color="primary" />
                                                    <Typography variant="body2" sx={{ overflowWrap: "anywhere" }}>{ville ?? "Ville inconnue"}</Typography>
                                                </Stack>
                                            </Stack>
                                        </Box>
                                        {point.siteWeb ? (
                                            <Button
                                                href={normalizeWebsiteUrl(point.siteWeb)}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                size="small"
                                                variant="outlined"
                                                startIcon={<LanguageIcon />}
                                                sx={{ flexShrink: 0, alignSelf: { xs: "flex-start", sm: "center" }, whiteSpace: "nowrap" }}
                                            >
                                                Site web
                                            </Button>
                                        ) : null}
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </Stack>
                </Box>
            ) : (
                <Box sx={{ height: "100%", width: "100%" }}>
                    {mapPoints.length === 0 ? (
                        <Typography sx={{ p: 3 }}>Aucune plateforme ne possède de coordonnées géographiques.</Typography>
                    ) : (
                        <MapContainer center={[46.5, 2.5]} zoom={5} minZoom={3} maxZoom={18} style={{ height: "100%", width: "100%" }}>
                            <TileLayer
                                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                            />
                            <FitMapToPoints points={mapPoints} />
                            <MarkerClusterGroup chunkedLoading>
                                {mapPoints.map((point) => (
                                    <Marker key={`${point.instanceHost}-${point.id}`} position={[point.latitude, point.longitude]}>
                                        <Popup>
                                            <strong>{point.nom}</strong>
                                            <br />
                                            {point.villeNom ?? (point.villeId ? villesById.get(point.villeId) : null) ?? "Ville inconnue"}
                                            {point.siteWeb && (
                                                <>
                                                    <br />
                                                    <Link href={normalizeWebsiteUrl(point.siteWeb)} target="_blank" rel="noopener noreferrer">{point.siteWeb}</Link>
                                                </>
                                            )}
                                        </Popup>
                                    </Marker>
                                ))}
                            </MarkerClusterGroup>
                        </MapContainer>
                    )}
                </Box>
            )}
        </Box>
    )
}