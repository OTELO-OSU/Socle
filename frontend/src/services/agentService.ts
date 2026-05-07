import { symfony } from "../utils/axios";

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
    corps?: Corps | null;
    grade?: Grade | null;
    typeContrat?: typeContrat | null;
};

export type RetourFetchAgentParPage = {
    agents: Agent[];
    page: number;
    pages_total: number;
    total: number;
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