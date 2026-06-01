import { useQuery } from "@tanstack/react-query";
import { Dialog, DialogContent, DialogTitle, Link, Stack, Typography } from "@mui/material";
import L from "leaflet";
import { useContext, useEffect, useMemo, useState } from "react";
import { MapContainer, Marker, TileLayer } from "react-leaflet";
import MarkerClusterGroup from "react-leaflet-cluster";
import { useMap } from "react-leaflet/hooks";
import { DictionnaireContext } from "../context/dictionnaireContext";
import { fetchPlateformeListeParPage, type PlateformeLight } from "../services/plateformeService";
import 'leaflet/dist/leaflet.css';
import "leaflet.markercluster/dist/MarkerCluster.css";
import "leaflet.markercluster/dist/MarkerCluster.Default.css";
import markerIcon2xUrl from "leaflet/dist/images/marker-icon-2x.png";
import markerIconUrl from "leaflet/dist/images/marker-icon.png";
import markerShadowUrl from "leaflet/dist/images/marker-shadow.png";

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2xUrl,
    iconUrl: markerIconUrl,
    shadowUrl: markerShadowUrl,
});

const plateformeMarkerIcon = L.icon({
    iconRetinaUrl: markerIcon2xUrl,
    iconUrl: markerIconUrl,
    shadowUrl: markerShadowUrl,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41],
});

type PlateformeMapPoint = {
    id: number;
    nom: string;
    siteWeb: string | null;
    villeId: number | null;
    latitude: number;
    longitude: number;
};

function FitMapToPoints({ points }: { points: PlateformeMapPoint[] }) {
    const map = useMap();

    useEffect(() => {
        if (points.length === 0) {
            return;
        }

        const bounds = L.latLngBounds(points.map((point) => [point.latitude, point.longitude] as [number, number]));
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 12 });
    }, [map, points]);

    return null;
}

function normalizeWebsiteUrl(siteWeb: string): string {
    if (/^https?:\/\//i.test(siteWeb)) {
        return siteWeb;
    }

    return `https://${siteWeb}`;
}

async function getPlateformeMapPoints(): Promise<PlateformeMapPoint[]> {
    const firstPage = await fetchPlateformeListeParPage(1, "");
    const allPlateformes: PlateformeLight[] = [...firstPage.plateformes];

    if (firstPage.pages_total > 1) {
        const otherPageResponses = await Promise.all(
            Array.from({ length: firstPage.pages_total - 1 }, (_, index) =>
                fetchPlateformeListeParPage(index + 2, "")
            )
        );

        otherPageResponses.forEach(({ plateformes }) => {
            allPlateformes.push(...plateformes);
        });
    }

    return allPlateformes
        .filter((plateforme) => {
            const latitude = plateforme.lastHistorique.latitude;
            const longitude = plateforme.lastHistorique.longitude;
            return typeof latitude === "number" && typeof longitude === "number";
        })
        .map((plateforme) => ({
            id: plateforme.id,
            nom: plateforme.lastHistorique.nom_officiel,
            siteWeb: plateforme.lastHistorique.site_web,
            villeId: plateforme.lastHistorique.ville_id,
            latitude: plateforme.lastHistorique.latitude as number,
            longitude: plateforme.lastHistorique.longitude as number,
        }));
}

export default function Home() {
    const dictionnaire = useContext(DictionnaireContext);
    const { data: points = [] } = useQuery({
        queryKey: ["plateformes", "map-points"],
        queryFn: getPlateformeMapPoints,
    });
    const [selectedPoint, setSelectedPoint] = useState<PlateformeMapPoint | null>(null);

    const villesById = useMemo(() => {
        return new Map(dictionnaire.ville.map((ville) => [ville.id, ville.nom]));
    }, [dictionnaire.ville]);

    const selectedVille =
        selectedPoint?.villeId != null ? (villesById.get(selectedPoint.villeId) ?? "Ville inconnue") : "Ville non renseignée";

    return (
        <>
            <MapContainer className="h-full w-screen" center={[46.5, 2.5]} zoom={6} minZoom={3} maxZoom={18} maxBounds={[[-85, -180],
            [85, 180],
            ]} scrollWheelZoom={true} maxBoundsViscosity={1.0}
                worldCopyJump={true}
            >
                <TileLayer
                    attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                />
                <FitMapToPoints points={points} />
                <MarkerClusterGroup chunkedLoading>
                    {points.map((point) => (
                        <Marker
                            key={point.id}
                            position={[point.latitude, point.longitude]}
                            icon={plateformeMarkerIcon}
                            eventHandlers={{
                                click: () => {
                                    setSelectedPoint(point);
                                },
                            }}
                        />
                    ))}
                </MarkerClusterGroup>
            </MapContainer>

            <Dialog open={selectedPoint !== null} onClose={() => setSelectedPoint(null)} maxWidth="sm" fullWidth>
                <DialogTitle>Informations plateforme</DialogTitle>
                <DialogContent>
                    {selectedPoint && (
                        <Stack spacing={1.5}>
                            <Typography><strong>Nom :</strong> {selectedPoint.nom}</Typography>
                            <Typography>
                                <strong>Site web :</strong>{" "}
                                {selectedPoint.siteWeb ? (
                                    <Link href={normalizeWebsiteUrl(selectedPoint.siteWeb)} target="_blank" rel="noopener noreferrer">
                                        {selectedPoint.siteWeb}
                                    </Link>
                                ) : (
                                    "Non renseigné"
                                )}
                            </Typography>
                            <Typography><strong>Ville :</strong> {selectedVille}</Typography>
                        </Stack>
                    )}
                </DialogContent>
            </Dialog>
        </>
    )
}