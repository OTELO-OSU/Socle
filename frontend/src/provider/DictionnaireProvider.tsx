import { useContext, useEffect, useState } from 'react'
import { AuthContext } from '../context/authContext';
import { DictionnaireContext } from '../context/dictionnaireContext';
import { fetchBaps, fetchCnus, fetchCorps, fetchDeterminationTarifPlateforme, fetchEmployeurs, fetchOperations, fetchSectionCnrs, fetchSourcesFinancement, fetchTreeViewFinancement, fetchTypeContrats, fetchTypesContratsMaintenance, fetchUnites, fetchUniversites, fetchVilles, type Bap, type Cnu, type Corps, type Employeur, type MethodeDeterminationTarif, type Operation, type SectionCnrs, type SourceFinancement, type TreeNode, type TypeContrat, type TypeContratMaintenance, type Unite, type Universite, type Ville } from '../services/dictionnaireService';
import { formatBapLabel, formatCnuLabel, formatEmployeurLabel, formatSectionCnrsLabel, formatUniteLabel, formatUniversiteLabel, formatVilleLabel } from '../services/formatLabelService';
import { fetchTags, type Tag } from '../services/tagsService';

export type SelectOption = {
    value: string | number;
    label: string;
};

export type DictionnaireType = {
    ville: Ville[],
    villeOptions: SelectOption[],
    unite: Unite[];
    uniteOptions: SelectOption[],
    bap: Bap[];
    bapOptions: SelectOption[],
    sectionCnrs: SectionCnrs[];
    sectionCnrsOptions: SelectOption[];
    typeContrat: TypeContrat[];
    typeContratOptions: SelectOption[];
    cnu: Cnu[];
    cnuOptions: SelectOption[];
    employeur: Employeur[];
    employeurOptions: SelectOption[];
    corps: Corps[];
    financeurTreeView: TreeNode[];
    sourcesFinancement: SourceFinancement[];
    operations: Operation[];
    operationsOptions: SelectOption[];
    typeContratMaintenance: TypeContratMaintenance[];
    determinationTarifPlateforme: MethodeDeterminationTarif[];
    determinationTarifPlateformeOptions: SelectOption[];
    universite: Universite[];
    universiteOptions: SelectOption[];
    tags: Tag[];
    tagsOptions: SelectOption[];
}

const defaultDictionnaire: DictionnaireType = {
    ville: [],
    villeOptions: [],
    unite: [],
    uniteOptions: [],
    bap: [],
    bapOptions: [],
    sectionCnrs: [],
    sectionCnrsOptions: [],
    typeContrat: [],
    typeContratOptions: [],
    cnu: [],
    cnuOptions: [],
    employeur: [],
    employeurOptions: [],
    corps: [],
    financeurTreeView: [],
    sourcesFinancement: [],
    operations: [],
    operationsOptions: [],
    typeContratMaintenance: [],
    determinationTarifPlateforme: [],
    determinationTarifPlateformeOptions: [],
    universite: [],
    universiteOptions: [],
    tags: [],
    tagsOptions: []
};

export function DictionnaireProvider({ children }: { children: React.ReactNode }) {
    const [dictionnaire, setDictionnaire] = useState<DictionnaireType>(defaultDictionnaire);
    const authContext = useContext(AuthContext);

    useEffect(() => {
        let isMounted = true;

        if (authContext?.isAuthLoading) {
            return;
        }

        const getSettledValue = <T,>(
            result: PromiseSettledResult<T>,
            fallback: T,
        ) => (result.status === 'fulfilled' ? result.value : fallback);

        const loadDictionnaire = async () => {
            const [
                uniteResult,
                villeResult,
                corpsResult,
                bapResult,
                sectionCnrsResult,
                typeContratResult,
                cnuResult,
                employeurResult,
                financeurTreeViewResult,
                sourcesFinancementResult,
                operationsResult,
                typeContratMaintenanceResult,
                determinationTarifPlateformeResult,
                universiteResult,
                tagsResult
            ] = await Promise.allSettled([
                fetchUnites(),
                fetchVilles(),
                fetchCorps(),
                fetchBaps(),
                fetchSectionCnrs(),
                fetchTypeContrats(),
                fetchCnus(),
                fetchEmployeurs(),
                fetchTreeViewFinancement(),
                fetchSourcesFinancement(),
                fetchOperations(),
                fetchTypesContratsMaintenance(),
                fetchDeterminationTarifPlateforme(),
                fetchUniversites(),
                fetchTags()
            ]);

            const unite = getSettledValue(uniteResult, defaultDictionnaire.unite);
            const ville = getSettledValue(villeResult, defaultDictionnaire.ville);
            const corps = getSettledValue(corpsResult, defaultDictionnaire.corps);
            const bap = getSettledValue(bapResult, defaultDictionnaire.bap);
            const sectionCnrs = getSettledValue(sectionCnrsResult, defaultDictionnaire.sectionCnrs);
            const typeContrat = getSettledValue(typeContratResult, defaultDictionnaire.typeContrat);
            const cnu = getSettledValue(cnuResult, defaultDictionnaire.cnu);
            const employeur = getSettledValue(employeurResult, defaultDictionnaire.employeur);
            const financeurTreeView = getSettledValue(financeurTreeViewResult, defaultDictionnaire.financeurTreeView);
            const sourcesFinancement = getSettledValue(sourcesFinancementResult, defaultDictionnaire.sourcesFinancement);
            const operations = getSettledValue(operationsResult, defaultDictionnaire.operations);
            const typeContratMaintenance = getSettledValue(typeContratMaintenanceResult, defaultDictionnaire.typeContratMaintenance);
            const determinationTarifPlateforme = getSettledValue(determinationTarifPlateformeResult, defaultDictionnaire.determinationTarifPlateforme);
            const universite = getSettledValue(universiteResult, defaultDictionnaire.universite);
            const tags = getSettledValue(tagsResult, defaultDictionnaire.tags);
            if (isMounted) {
                const villeOptions = ville.map((item) => ({ value: item.id, label: formatVilleLabel(item) }));
                const uniteOptions = unite.map((item) => ({ value: item.id, label: formatUniteLabel(item) }));
                const employeurOptions = employeur.map((item) => ({ value: item.id, label: formatEmployeurLabel(item) }));
                const typeContratOptions = typeContrat.map((item) => ({ value: item.id, label: item.nom }));
                const operationsOptions = operations.map((item) => ({ value: item.id, label: item.nom }));
                const determinationTarifPlateformeOptions = determinationTarifPlateforme.map((item) => ({ value: item.id, label: item.nom }));
                const universiteOptions = universite.map((item) => ({ value: item.id, label: formatUniversiteLabel(item) }));
                const bapOptions = bap.map((item) => ({ value: item.id, label: formatBapLabel(item) }));
                const sectionCnrsOptions = sectionCnrs.map((item) => ({ value: item.id, label: formatSectionCnrsLabel(item) }));
                const cnuOptions = cnu.map((item) => ({ value: item.id, label: formatCnuLabel(item) }));
                const tagsOptions = tags.map((item) => ({ value: item.id, label: item.nom }));
                setDictionnaire((current) => ({
                    ...current,
                    unite,
                    ville,
                    villeOptions,
                    uniteOptions,
                    employeurOptions,
                    corps,
                    bap,
                    bapOptions,
                    sectionCnrs,
                    typeContrat,
                    cnu,
                    employeur,
                    typeContratOptions,
                    financeurTreeView,
                    sourcesFinancement,
                    operations,
                    operationsOptions,
                    typeContratMaintenance,
                    determinationTarifPlateforme,
                    determinationTarifPlateformeOptions,
                    universite,
                    universiteOptions,
                    sectionCnrsOptions,
                    cnuOptions,
                    tags,
                    tagsOptions
                }));
            }
        };

        loadDictionnaire();

        return () => {
            isMounted = false;
        };
    }, [authContext?.isAuthLoading]);

    return <DictionnaireContext.Provider value={dictionnaire}>{children}</DictionnaireContext.Provider>
}
