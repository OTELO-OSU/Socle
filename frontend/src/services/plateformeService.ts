import { symfony } from "../utils/axios";

export type PlateformeLight = {
    id: number;
    pseudo_id: string;
    lastHistorique: {
        id: number;
        annee: number;
        nom_officiel: string;
        forme_developpee_nom_officiel: string | null;
        plateformeHistoriquePersonnels: {
            id: number;
            utilisateur_id: {
                id: number;
                nom: string;
                prenom: string;
                email: string;
            },
            responsable_scientifique: boolean,
            responsable_technique: boolean,
            quotite: number;
        }[];
        brouillon: boolean;
    }
    prenom: string;
    plateformeDemande: string | null;
    uniteDemande: string | null;
    osuDemande: boolean;
}

export type PlateformeCree = {
    message: string;
    plateforme_id: number;
}

export async function fetchPlateformeListeParPage(page: number, recherche: string) {
    const {data} = await symfony.get<PlateformeLight[]>('/plateforme', {
        params: {
            page: page,
            search: recherche,
        }
    });

    return data;
}

export async function creerPlateforme(nom: string) {
    const {data} = await symfony.post<PlateformeCree>('/plateforme', {
        nom: nom
    });

    return { message: data.message, plateforme_id: data.plateforme_id };
}