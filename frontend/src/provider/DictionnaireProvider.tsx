import { useEffect, useState } from 'react'
import { DictionnaireContext } from '../context/dictionnaireContext';
import { fetchBaps, fetchCnus, fetchCorps, fetchEmployeurs, fetchSectionCnrs, fetchTypeContrats, fetchUnites, fetchVilles, type Bap, type Cnu, type Corps, type Employeur, type SectionCnrs, type TypeContrat, type Unite, type Ville } from '../services/dictionnaireService';
import { formatEmployeurLabel, formatUniteLabel, formatVilleLabel } from '../services/formatLabelService';

type SelectOption = {
    value: string | number;
    label: string;
};

export type DictionnaireType = {
    ville: Ville[],
    villeOptions: SelectOption[],
    tutelle: {
        value: string;
        label: string;
    }[],
    unite: Unite[];
    uniteOptions: SelectOption[],
    bap: Bap[];
    sectionCnrs: SectionCnrs[];
    typeContrat: TypeContrat[];
    typeContratOptions: SelectOption[];
    cnu: Cnu[];
    employeur: Employeur[];
    employeurOptions: SelectOption[];
    corps: Corps[];
}

const defaultDictionnaire: DictionnaireType = {
    ville: [],
    villeOptions: [],
    tutelle: [
        { value: 'Université de Paris', label: 'Université de Paris' },
        { value: 'Université de Lyon', label: 'Université de Lyon' },
        { value: 'Université de Marseille', label: 'Université de Marseille' },
    ],
    unite: [],
    uniteOptions: [],
    bap: [],
    sectionCnrs: [],
    typeContrat: [],
    typeContratOptions: [],
    cnu: [],
    employeur: [],
    employeurOptions: [],
    corps: [],
};

export function DictionnaireProvider({ children }: { children: React.ReactNode }) {
    const [dictionnaire, setDictionnaire] = useState<DictionnaireType>(defaultDictionnaire);

    useEffect(() => {
        let isMounted = true;

        const loadDictionnaire = async () => {
            const unite = await fetchUnites();
            const ville = await fetchVilles();
            const corps = await fetchCorps();
            const bap = await fetchBaps();
            const sectionCnrs = await fetchSectionCnrs();
            const typeContrat = await fetchTypeContrats();
            const cnu = await fetchCnus();
            const employeur = await fetchEmployeurs();
            if (isMounted) {
                const villeOptions = ville.map((item) => ({ value: item.id, label: formatVilleLabel(item) }));
                const uniteOptions = unite.map((item) => ({ value: item.id, label: formatUniteLabel(item) }));
                const employeurOptions = employeur.map((item) => ({ value: item.id, label: formatEmployeurLabel(item) }));
                const typeContratOptions = typeContrat.map((item) => ({ value: item.id, label: item.nom }));

                setDictionnaire((current) => ({
                    ...current,
                    unite,
                    ville,
                    villeOptions,
                    uniteOptions,
                    employeurOptions,
                    corps,
                    bap,
                    sectionCnrs,
                    typeContrat,
                    cnu,
                    employeur,
                    typeContratOptions,
                }));
            }
        };

        loadDictionnaire();

        return () => {
            isMounted = false;
        };
    }, []);

    return <DictionnaireContext.Provider value={dictionnaire}>{children}</DictionnaireContext.Provider>
}
