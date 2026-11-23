import { symfony } from "../utils/axios";

export type PseudoIdMapping = {
    id: number;
    plateformeId: number;
    pseudoIdMapped: string;
    updatedAt: string;
};

export type PlateformeSelectOption = {
    id: number;
    nomOfficiel: string;
    pseudoId: string;
};

export async function fetchAllPseudoIdMappings(): Promise<PseudoIdMapping[]> {
    const { data } = await symfony.get<PseudoIdMapping[]>('/admin/plateforme/pseudo-id');
    return data;
}

export async function fetchPseudoIdMapping(plateformeId: number): Promise<PseudoIdMapping | null> {
    try {
        const { data } = await symfony.get<PseudoIdMapping>(`/admin/plateforme/pseudo-id/${plateformeId}`);
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
    pseudoId: string
): Promise<PseudoIdMapping> {
    const { data } = await symfony.post<PseudoIdMapping>('/admin/plateforme/pseudo-id', {
        plateformeId,
        pseudoId,
    });
    return data;
}

export async function deletePseudoIdMapping(plateformeId: number): Promise<{ message: string }> {
    const { data } = await symfony.delete<{ message: string }>(`/admin/plateforme/pseudo-id/${plateformeId}`);
    return data;
}

