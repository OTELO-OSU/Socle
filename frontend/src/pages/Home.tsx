import { useQuery } from "@tanstack/react-query";
import { Container, Link, Table, TableBody, TableCell, TableHead, TableRow, TableSortLabel } from "@mui/material";
import { useContext, useMemo, useState } from "react";
import { DictionnaireContext } from "../context/dictionnaireContext";
import { fetchPlateformePublique } from "../services/plateformeService";

type PlateformeMapPoint = {
    id: number;
    nom: string;
    siteWeb: string | null;
    villeId: number | null;
    villeNom: string | null;
    latitude: number;
    longitude: number;
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

async function getPlateformeMapPoints(): Promise<PlateformeMapPoint[]> {
    const allPlateformes = await fetchPlateformePublique();

    return allPlateformes
        .filter((plateforme) => {
            const latitude = plateforme.dernier_historique.latitude;
            const longitude = plateforme.dernier_historique.longitude;
            return typeof latitude === "number" && typeof longitude === "number";
        })
        .map((plateforme) => ({
            id: plateforme.id,
            nom: plateforme.dernier_historique.nom_officiel,
            siteWeb: plateforme.dernier_historique.site_web,
            villeId: plateforme.dernier_historique.ville_id,
            villeNom: plateforme.dernier_historique.ville_nom,
            latitude: plateforme.dernier_historique.latitude as number,
            longitude: plateforme.dernier_historique.longitude as number,
            laboratoire: plateforme.dernier_historique.laboratoire_operateur_id ? plateforme.dernier_historique.laboratoire_operateur_id : null,
            laboratoireNom: plateforme.dernier_historique.laboratoire_operateur_nom,
        }));
}

export default function Home() {
    const dictionnaire = useContext(DictionnaireContext);
    const { data: points = [] } = useQuery({
        queryKey: ["plateformes", "map-points"],
        queryFn: getPlateformeMapPoints,
    });
    const [sortField, setSortField] = useState<SortField>("nom");
    const [sortDirection, setSortDirection] = useState<SortDirection>("asc");

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
    }, [points, sortDirection, sortField, villesById]);

    const handleSort = (field: SortField) => {
        setSortDirection((currentDirection) => {
            if (sortField === field) {
                return currentDirection === "asc" ? "desc" : "asc";
            }

            return "asc";
        });
        setSortField(field);
    };

    return (
        <Container>
            <Table sx={{ mt: 5, mb: 5 }} aria-label="Liste des plateformes publiques">
                <TableHead>
                    <TableRow>
                        <TableCell sortDirection={sortField === "laboratoire" ? sortDirection : false}>
                            <TableSortLabel
                                active={sortField === "laboratoire"}
                                direction={sortField === "laboratoire" ? sortDirection : "asc"}
                                onClick={() => handleSort("laboratoire")}
                            >
                                Laboratoire
                            </TableSortLabel>
                        </TableCell>
                        <TableCell sortDirection={sortField === "siteWeb" ? sortDirection : false}>
                            <TableSortLabel
                                active={sortField === "siteWeb"}
                                direction={sortField === "siteWeb" ? sortDirection : "asc"}
                                onClick={() => handleSort("siteWeb")}
                            >
                                Site Web
                            </TableSortLabel>
                        </TableCell>
                        <TableCell sortDirection={sortField === "ville" ? sortDirection : false}>
                            <TableSortLabel
                                active={sortField === "ville"}
                                direction={sortField === "ville" ? sortDirection : "asc"}
                                onClick={() => handleSort("ville")}
                            >
                                Ville
                            </TableSortLabel>
                        </TableCell>
                        <TableCell sortDirection={sortField === "nom" ? sortDirection : false}>
                            <TableSortLabel
                                active={sortField === "nom"}
                                direction={sortField === "nom" ? sortDirection : "asc"}
                                onClick={() => handleSort("nom")}
                            >
                                Nom
                            </TableSortLabel>
                        </TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {sortedPoints.map((point) => (
                        <TableRow key={point.id}>
                            <TableCell>{point.laboratoireNom ?? (point.laboratoire ? laboById.get(point.laboratoire) : null) ?? "Laboratoire inconnu"}</TableCell>
                            <TableCell>
                                {point.siteWeb ? (
                                    <Link href={normalizeWebsiteUrl(point.siteWeb)} target="_blank" rel="noopener noreferrer">
                                        {point.siteWeb}
                                    </Link>
                                ) : (
                                    "Pas de site web"
                                )}
                            </TableCell>
                            <TableCell>{point.villeNom ?? (point.villeId ? villesById.get(point.villeId) : null) ?? "Ville inconnue"}</TableCell>
                            <TableCell>{point.nom}</TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </Container>
    )
}