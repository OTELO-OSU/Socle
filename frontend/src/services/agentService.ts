import { symfony } from "../utils/axios";
import type { Cnu, Corps, Employeur, SectionCnrs } from "./dictionnaireService";

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
    type_contrat?: typeContrat | null;
    unite?: number | null;
    doctorant?: boolean | null;
    universite_rattachement?: number | null;
    baps_ids?: number[];
    emploi_types_ids?: number[];
    section_cnrs?: SectionCnrs | null;
    cnu?: Cnu | null;
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
    type_contrat: number | null;
    unite: number | null;
    doctorant: boolean | null;
    universite_rattachement: number | null;
    baps_ids: number[];
    emploi_types_ids: number[];
    section_cnrs: number | null;
    cnu: number | null;
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