import { symfony } from "../utils/axios";
import type { Agent } from "./agentService";

export type Tag = {
    id: number;
    nom: string;
}

export type TagAdmin = Tag & {
    utilisateurs: Agent[];
}

export async function fetchTags() {
    const { data } = await symfony.get<Tag[]>('/tags');

    return data;
}

export async function fetchTagsAdmin() {
    const { data } = await symfony.get<TagAdmin[]>('/admin/tags');

    return data;
}

export async function ajouterTagAuUser(tagId: number, agentId: number) {
    const { data } = await symfony.post<{ message: string }>('/utilisateur/tags', { tagId, id: agentId });

    return data;
}

export async function removeTagFromUser(tagId: number, agentId: number) {
    const { data } = await symfony.delete<{ message: string }>(`/utilisateur/tags`, {
        data: { tagId, id: agentId }
    });

    return data;
}