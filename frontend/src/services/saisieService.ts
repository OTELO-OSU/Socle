import { symfony } from "../utils/axios";

export async function fetchAnneeDeSaisie() {
    const { data } = await symfony.get<{ annee: number }>('/saisie');

    return data;
}

export async function setAnneeDeSaisie(annee: number) {
    const { data } = await symfony.patch(`/saisie`, { annee });

    return data;
}