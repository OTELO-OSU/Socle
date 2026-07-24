import type { Agent } from "./agentService";
import type { Bap, Corps, Employeur, TypeContrat } from "./dictionnaireService";

export function isCDD(typeContratId: number | null, typeContrats: TypeContrat[]): boolean {
    if (!typeContratId) {
        return false;
    }

    const typeContrat = typeContrats.find((item) => item.id === typeContratId);
    return typeContrat?.nom === "CDD";
}

export function isEpicEmployeur(employeurId: number | null, employeurs: Employeur[]): boolean {
    if (!employeurId) {
        return false;
    }

    const employeur = employeurs.find((item) => item.id === employeurId);
    return employeur?.epic ?? false;
}

export function getSelectedCorps(corpsId: number | null, corps: Corps[]): Corps | null {
    if (!corpsId) {
        return null;
    }

    return corps.find((item) => item.id === corpsId) ?? null;
}

export function getGradeOptions(corpsId: number | null, corps: Corps[]) {
    const selectedCorps = getSelectedCorps(corpsId, corps);
    return selectedCorps?.grades ?? [];
}

export function getCompatibilites(corpsId: number | null, corps: Corps[]) {
    const selectedCorps = getSelectedCorps(corpsId, corps);

    return {
        compatibleBap: selectedCorps?.compatible_bap ?? false,
        compatibleCnu: selectedCorps?.compatible_cnu ?? false,
        compatibleSection: selectedCorps?.compatible_section ?? false,
    };
}

export function getEmploiTypesOptions(bapsIds: number[], baps: Bap[], corps: Corps | null) {
    if (!corps) {
        return [] as { id: number; nom: string }[];
    }

    const CORPSACRO_CHIFFRE = {
        "IR": 1,
        "IE": 2,
        "AI": 3,
        "T": 4,
        "AJT": 5,
    }

    if (bapsIds.length === 0) {
        return [] as { id: number; nom: string }[];
    }

    const selectedBaps = baps.filter((item) => {
        return bapsIds.includes(item.id);
    });
    const unique = new Map<number, { id: number; nom: string }>();

    selectedBaps.forEach((bapItem) => {
        (bapItem.emploiTypes ?? []).forEach((emploiType) => {
            const corpsAcronyme = corps?.acronyme ?? "";
            const chiffreET = CORPSACRO_CHIFFRE[corpsAcronyme as keyof typeof CORPSACRO_CHIFFRE] ?? 0;
            if (emploiType.acronyme?.charAt(1) !== String(chiffreET)) {
                return false;
            }
            unique.set(emploiType.id, { id: emploiType.id, nom: `${emploiType.acronyme} - ${emploiType.nom}` });
        });
    });

    return Array.from(unique.values());
}

export function resetOnTypeContratChange(state: Agent, nextTypeContratId: number | null): Agent {
    return {
        ...state,
        type_contrat_id: nextTypeContratId,
        doctorant: null,
        universite_rattachement_id: null,
        corps_id: null,
        grade_id: null,
        baps_ids: [],
        emploi_types_ids: [],
        section_cnrs_id: null,
        cnu_id: null,
    };
}

export function resetOnDoctorantChange(state: Agent, nextDoctorant: boolean | null): Agent {
    return {
        ...state,
        doctorant: nextDoctorant,
        universite_rattachement_id: null,
        corps_id: null,
        grade_id: null,
        baps_ids: [],
        emploi_types_ids: [],
        section_cnrs_id: null,
        cnu_id: null,
    };
}

export function resetOnCorpsChange(state: Agent, nextCorpsId: number | null): Agent {
    return {
        ...state,
        corps_id: nextCorpsId,
        grade_id: null,
        baps_ids: [],
        emploi_types_ids: [],
        section_cnrs_id: null,
        cnu_id: null,
    };
}

export function resetOnEmployeurChange(state: Agent, nextEmployeurId: number | null, employeurs: Employeur[]): Agent {
    const epic = isEpicEmployeur(nextEmployeurId, employeurs);

    if (!epic) {
        return {
            ...state,
            employeur_id: nextEmployeurId,
        };
    }

    return {
        ...state,
        employeur_id: nextEmployeurId,
        baps_ids: [],
        emploi_types_ids: [],
        section_cnrs_id: null,
        cnu_id: null,
    };
}

export function syncEmploiTypesWithBaps(state: Agent, nextBapsIds: number[], baps: Bap[], corps: Corps | null): Agent {
    const allowedIds = getEmploiTypesOptions(nextBapsIds, baps, corps).map((item) => item.id);

    return {
        ...state,
        baps_ids: nextBapsIds,
        emploi_types_ids: (state.emploi_types_ids ?? []).filter((id) => allowedIds.includes(id)),
    };
}
