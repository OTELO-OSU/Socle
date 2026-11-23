import { symfony } from "../utils/axios";

export type PseudoIdMapping = {
    id: number;
    plateformeId: number;
    instanceHost: string | null;
    pseudoIdMapped: string;
    updatedAt: string;
};

export type PlateformeSelectOption = {
    id: number;
    nomOfficiel: string;
    pseudoId: string;
    instance_host?: string;
};

export async function fetchAllPseudoIdMappings(): Promise<PseudoIdMapping[]> {
    const { data } = await symfony.get<PseudoIdMapping[]>('/admin/plateforme/pseudo-id');
    return data;
}

export async function fetchPseudoIdMapping(plateformeId: number, instanceHost?: string): Promise<PseudoIdMapping | null> {
    try {
        const params = new URLSearchParams();
        if (instanceHost) {
            params.append('instanceHost', instanceHost);
        }
        const { data } = await symfony.get<PseudoIdMapping>(`/admin/plateforme/pseudo-id/${plateformeId}${params.toString() ? '?' + params.toString() : ''}`);
        return data;
    } catch (error: any) {
        if (error.response?.status === 404) {
            return null;
        }
        throw error;
    }
}

export async function fetchPlateformesForSelect(): Promise<PlateformeSelectOption[]> {
    const { data } = await symfony.get<PlateformeSelectOption[]>('/admin/plateformes/select');
    return data;
}

export async function createOrUpdatePseudoIdMapping(
    plateformeId: number,
    pseudoId: string,
    instanceHost?: string
): Promise<PseudoIdMapping> {
    const { data } = await symfony.post<PseudoIdMapping>('/admin/plateforme/pseudo-id', {
        plateformeId,
        pseudoId,
        instanceHost,
    });
    return data;
}

export async function deletePseudoIdMapping(plateformeId: number, instanceHost?: string): Promise<{ message: string }> {
    const params = new URLSearchParams();
    if (instanceHost) {
        params.append('instanceHost', instanceHost);
    }
    const { data } = await symfony.delete<{ message: string }>(`/admin/plateforme/pseudo-id/${plateformeId}${params.toString() ? '?' + params.toString() : ''}`);
    return data;
}

