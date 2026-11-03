import { symfony } from "../utils/axios";

export type Grade = {
    id: number;
    nom: string;
}

export type typeContrat = {
    id: number;
    nom: string;
}

export type Agent = {
    id?: number | null;
    nom: string;
    prenom: string;
    email: string;
    employeur_id?: number | null;
    corps_id?: number | null;
    grade_id?: number | null;
    type_contrat_id?: number | null;
    unite_id?: number | null;
    doctorant?: boolean | null;
    universite_rattachement_id?: number | null;
    baps_ids?: number[];
    emploi_types_ids?: number[];
    section_cnrs_id?: number | null;
    cnu_id?: number | null;
    is_admin?: boolean;
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
    employeur_id: number | null;
    corps_id: number | null;
    grade_id: number | null;
    type_contrat_id: number | null;
    unite_id: number | null;
    doctorant: boolean | null;
    universite_rattachement_id: number | null;
    baps_ids: number[];
    emploi_types_ids: number[];
    section_cnrs_id: number | null;
    cnu_id: number | null;
    is_admin?: boolean;
};

export type RetourSoumettreAgentAuDu = {
    message: string;
    utilisateur: Agent;
};

export async function fetchAgentListeParPage(page: number, search: string) {
    const { data } = await symfony.get<RetourFetchAgentParPage>("/agent", {
        params: {
            page,
            search,
        },
    });

    return data;
}

export async function getAgentByPage(search: string) {
    return fetchAgentListeParPage(1, search);
}

export async function soumettreAgentAuDu(payload: PayloadSoumettreAgentAuDu) {
    const { data } = await symfony.post<RetourSoumettreAgentAuDu>("/agent", {
        ...payload
    });

    return data;
}

export async function modifierAgent(id: number, payload: PayloadSoumettreAgentAuDu) {
    const { data } = await symfony.put<RetourSoumettreAgentAuDu>(`/agent`, {
        utilisateur_id: id,
        ...payload
    });

    return data;
}