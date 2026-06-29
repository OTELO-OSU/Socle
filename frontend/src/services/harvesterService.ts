import { symfony } from "../utils/axios";
import type { PlateformeLight } from "./plateformeService";

export type HarvesterAdmin = {
    harvesters: Harvester[];
    hmt: HMT[];
}
export type Harvester = {
    id: number;
    client_id: string;
    host: string;
}

export type HMT = {
    id: number;
    client_id: string;
    tags_ids: number[];
    utilisateur_id: number;
}

export async function fetchHarvesterAdmin() {
    const { data } = await symfony.get<HarvesterAdmin>('/admin/harvesters');

    return data;
}

export async function creerHMT(clientId: string) {
    const { data } = await symfony.get<{ hmt: string }>('/harvester/hmt', { params: { clientId } });

    return data;
}

export async function entrerHMT(hmt: string) {
    const { data } = await symfony.post<{ hmt: string }>('/harvester/hmt', { hmt });

    return data;
}

export async function supprHMT(clientId: number) {
    const { data } = await symfony.delete<{ hmt: string }>('/harvester/hmt', { data: { clientId } });

    return data;
}

export async function supprHarvester(harvesterId: number) {
    const { data } = await symfony.delete<{ harvester: string }>('/harvester', { data: { harvesterId } });

    return data;
}

export async function fetchPlateformeHarveste() {
    const { data } = await symfony.get<PlateformeLight[]>('/harvester/plateformes');

    return data;
}

export async function fetchDetailPlateformeHarvestee({ id, host }: { id: number, host: string }) {
    const { data } = await symfony.get<PlateformeLight[]>('/harvester/plateforme', {
        params: {
            id,
            host
        }
    });
    console.log("data", data);
    return data;
}