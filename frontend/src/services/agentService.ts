import { symfony } from "../utils/axios";
import type { Employeur } from "./dictionnaireService";

export type Corps = {
    id: number;
    nom: string;
    acronyme?: string | null;
}

export type Grade = {
    id: number;
    nom: string;
}

export type typeContrat = {
    id: number;
    nom: string;
}

export type Agent = {
    id: number;
    nom: string;
    prenom: string;
    email: string;
    employeur?: Employeur | null;
    corps?: Corps | null;
    grade?: Grade | null;
    typeContrat?: typeContrat | null;
    unite?: number | null;
};

export type RetourFetchAgentParPage = {
    agents: Agent[];
    page: number;
    pages_total: number;
    total: number;
};

export type PayloadSoumettreAgentAuDu = {
    nom: string;
    prenom: string;
    email: string;
    employeur: number | null;
    corps: number | null;
    grade: number | null;
    typeContrat: number | null;
    unite: number | null;
};

export type RetourSoumettreAgentAuDu = {
    message: string;
    utilisateur: Agent;
};

export async function getAgentByPage(search: string) {
    const { data } = await symfony.get<RetourFetchAgentParPage>("/agent", {
        params: {
            page: 1,
            search,
        },
    });

    return data;
}

export async function soumettreAgentAuDu(payload: PayloadSoumettreAgentAuDu) {
    const { data } = await symfony.post<RetourSoumettreAgentAuDu>("/agent", {
        ...payload
    });

    return data;
}