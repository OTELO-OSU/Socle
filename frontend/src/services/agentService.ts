import { symfony } from "../utils/axios";

export type Agent = {
    id: number;
    nom: string;
    prenom: string;
    email: string;
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