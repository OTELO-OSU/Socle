import { symfony } from "../utils/axios";
import type { Agent } from "./agentService";

export type UniteDetail = {
    id: number;
    nom: string;
    acronyme?: string | null;
    codeUnite?: string;
    directeur_id?: number | null;
    directeur?: Agent | null;
    delegues?: Agent[] | null;
}

type DelegueOperation = "add" | "remove";

export async function fetchUnite() {
    const { data } = await symfony.get<UniteDetail[]>('/unite');

    return data;
}

export async function patchDirecteurUnite(uniteId: number, directeurId: number | null) {
    const { data } = await symfony.patch<UniteDetail[]>(`/unite/directeur`, { utilisateur_id: directeurId, unite_id: uniteId });

    return data;
}

export async function switchDelegueDirOsu(delegue_directeur_unite_id: number, unite_id: number, operation: DelegueOperation) {
    const { data } = await symfony.patch<{ message: string }>(`/unite/directeur/delegue`, { delegue_directeur_unite_id, unite_id, operation });
    return data;
}