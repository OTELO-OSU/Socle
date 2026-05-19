import { symfony } from "../utils/axios";

export type Unite = {
    id: number;
    nom: string;
    acronyme: string;
    codeUnite: string;
};

export type Corps = {
    id: number;
    nom: string;
    acronyme: string | null;
    grades: Grade[] | null;
}

export type Grade = {
    id: number;
    nom: string;
}

export type Ville = {
    id: number;
    nom: string;
    numeroDepartement: number;
    departement: string;
}

export type Employeur = {
    id: number;
    nom: string;
    acronyme: string | null;
}

export type Bap = {
    id: number;
    lettre: string;
    nom: string;
    emploiTypes: EmploiType[] | null;
}

export type EmploiType = {
    id: number;
    nom: string;
    acronyme: string | null;
}

export type Cnu = {
    id: number;
    numero: number;
    nom: string;
}

export type SectionCnrs = {
    id: number;
    numero: number;
    nom: string;
}

export type TypeContrat = {
    id: number;
    nom: string;
}

export type TreeNode = {
    id: number;
    label: string;
    children?: TreeNode[];
};

export type SourceFinancement = {
    id: number;
    nom: string;
    acronyme: string | null;
}

export type Operation = {
    id: number;
    nom: string;
    description: string | null;
}

export type TypeContratMaintenance = {
    id: number;
    nom: string;
}

export async function fetchUnites() {
    const { data } = await symfony.get<Unite[]>("/dictionnaire/unites");

    return data;
}

export async function fetchCorps() {
    const { data } = await symfony.get<Corps[]>("/dictionnaire/corps");

    return data;
}

export async function fetchVilles() {
    const { data } = await symfony.get<Ville[]>("/dictionnaire/villes");

    return data;
}

export async function fetchEmployeurs() {
    const { data } = await symfony.get<Employeur[]>("/dictionnaire/employeurs");

    return data;
}

export async function fetchBaps() {
    const { data } = await symfony.get<Bap[]>("/dictionnaire/baps");

    return data;
}

export async function fetchCnus() {
    const { data } = await symfony.get<Cnu[]>("/dictionnaire/cnus");

    return data;
}

export async function fetchSectionCnrs() {
    const { data } = await symfony.get<SectionCnrs[]>("/dictionnaire/sections_cnrs");

    return data;
}

export async function fetchTypeContrats() {
    const { data } = await symfony.get<TypeContrat[]>("/dictionnaire/type_contrat");

    return data;
}

export async function fetchTreeViewFinancement() {
    const { data } = await symfony.get<TreeNode[]>("/dictionnaire/treeview_financement");

    return data;
}

export async function fetchSourcesFinancement() {
    const { data } = await symfony.get<SourceFinancement[]>("/dictionnaire/sources_financement");

    return data;
}

export async function fetchOperations() {
    const { data } = await symfony.get<Operation[]>("/dictionnaire/operations");

    return data;
}

export async function fetchTypesContratsMaintenance() {
    const { data } = await symfony.get<TypeContratMaintenance[]>("/dictionnaire/type_contrat_maintenance");

    return data;
}