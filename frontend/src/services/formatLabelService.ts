import type { Bap, Cnu, Corps, Employeur, Grade, SectionCnrs, Unite, Universite, Ville } from "./dictionnaireService";

export const formatUniteLabel = (unite: Unite) => {
    let str = ""
    if (unite.acronyme) str += `${unite.acronyme} - `
    str += unite.nom
    if (unite.codeUnite) str += ` (${unite.codeUnite})`
    return str
}

export const formatVilleLabel = (ville: Ville) => {
    let str = ""
    str += ville.nom
    str += ` (${ville.numero_departement} - ${ville.departement})`
    return str
}

export const formatUniversiteLabel = (universite: Universite | null | undefined) => {
    let str = ""
    if (universite?.acronyme) str += `${universite.acronyme} - `
    str += universite?.nom || ""
    return str
}

export const formatEmployeurLabel = (employeur: Employeur | null | undefined) => {
    let str = ""
    if (employeur?.acronyme) str += `${employeur.acronyme} - `
    str += employeur?.nom || ""
    return str
}

export const formatBapLabel = (bap: Bap | null | undefined) => {
    if (!bap) return ""
    return bap?.lettre
}

export const formatSectionCnrsLabel = (section: SectionCnrs | null | undefined) => {
    if (!section) return ""
    return String(section?.numero)
}

export const formatCnuLabel = (cnu: Cnu | null | undefined) => {
    if (!cnu) return ""
    return String(cnu?.numero)
}

export const formatCorpsLabel = (corps: Corps | null | undefined) => {
    if (!corps) return ""
    let str = ""
    if (corps.acronyme) str += `${corps.acronyme} - `
    str += corps.nom
    return str
}

export const formatGradeLabel = (grade: Grade | null | undefined) => {
    if (!grade) return ""
    return grade.nom
}

export const typePersonnelLabel = (corps: string | null | undefined, typeContrat: string | null | undefined, doctorant: boolean | null | undefined) => {
    if (doctorant) {
        return "Doctorant"
    }
    let label = ""
    const listeIt = ["IR", "IE", "AI", "T", "AJT"];
    const listeChercheur = ["DR", "PR", "CR", "PHYS", "PHYS-A", "ASTRO", "ASTRO-A"];
    if (listeIt.includes(corps || "")) {
        label += "IT"
    }
    if (listeChercheur.includes(corps || "")) {
        label += "Chercheur"
    }

    if (typeContrat) {
        label += ` ${typeContrat}`
    }
    return label
}