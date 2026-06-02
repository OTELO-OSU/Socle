import { symfony } from "../utils/axios";
import type { Agent } from "./agentService";

export type UniteDetail = {
    id: number;
    nom: string;
    acronyme?: string | null;
    codeUnite?: string;
    directeur_id?: number | null;
    directeur?: Agent | null;
}

export async function fetchUnite() {
    const { data } = await symfony.get<UniteDetail[]>('/unite');

    return data;
}

export async function patchDirecteurUnite(uniteId: number, directeurId: number | null) {
    const { data } = await symfony.patch<UniteDetail[]>(`/unite/directeur`, { utilisateur_id: directeurId, unite_id: uniteId });

    return data;
}