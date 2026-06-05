import { symfony } from "../utils/axios";

export type UtilisateurEnAttente = {
    id: number;
    email: string;
    nom: string;
    prenom: string;
    plateformeDemande: string | null;
    uniteDemande: string | null;
    osuDemande: boolean;
}

export async function fetchUtilisateurEnAttente() {
    const { data } = await symfony.get<UtilisateurEnAttente[]>('/utilisateurs/attente-de-validation');

    return data;
}

export async function fetchUtilisateurEnAttenteDuDU() {
    const { data } = await symfony.get<UtilisateurEnAttente[]>('/agent/en-attente-du-du');

    return data;
}

export async function approuverUtilisateur(email: string) {
    const { data } = await symfony.post<{ message: string }>(`/utilisateurs/approuver`, {
        email
    });

    return data;
}

export async function refuserUtilisateur(email: string) {
    const { data } = await symfony.post<{ message: string }>(`/utilisateurs/refuser`, {
        email
    });

    return data;
}

export async function gererUtilisateurEnAttenteDU(id: number, approbation: boolean) {
    const { data } = await symfony.patch<{ message: string }>(`/agent`, {
        utilisateurId: id,
        approbation
    });

    return data;
}