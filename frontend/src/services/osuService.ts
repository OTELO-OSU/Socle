import { symfony } from "../utils/axios";
import type { Agent } from "./agentService";

type RetourOsuAdmin = {
    directeur_osu: Agent | null;
    delegue_directeur_osu: Agent[] | [];
}

export async function fetchOsuAdmin() {
    const { data } = await symfony.get<RetourOsuAdmin>("/osu");
    return data;
}

export async function patchDirecteurOsu(directeurId: number | null) {
    const { data } = await symfony.patch<{ message: string }>("/osu/directeur", { directeur_osu_id: directeurId });
    return data;
}

export async function patchDelegueDirecteurOsu(agentId: number, operation: "add" | "remove") {
    const { data } = await symfony.patch<{ message: string }>("/osu/directeur/delegue", { delegue_directeur_osu_id: agentId, operation });
    return data;
}