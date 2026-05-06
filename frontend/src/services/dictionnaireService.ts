import { symfony } from "../utils/axios";

export type Unite = {
    id: number;
    nom: string;
    acronyme: string;
    codeUnite: string;
};

export async function fetchUnites() {
    const { data } = await symfony.get<Unite[]>("/dictionnaire/unites");

    return data;
}