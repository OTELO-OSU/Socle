import { useEffect, useState } from 'react'
import { DictionnaireContext } from '../context/dictionnaireContext';
import { fetchUnites, type Unite } from '../services/dictionnaireService';

export type DictionnaireType = {
    ville: {
        value: string;
        label: string;
    }[],
    laboratoire: {
        value: string;
        label: string;
    }[],
    tutelle: {
        value: string;
        label: string;
    }[],
    unite: Unite[];
}

const defaultDictionnaire: DictionnaireType = {
    ville: [
        { value: 'Paris', label: 'Paris' },
        { value: 'Lyon', label: 'Lyon' },
        { value: 'Marseille', label: 'Marseille' },
    ],
    laboratoire: [
        { value: 'CNRS', label: 'CNRS' },
        { value: 'INSERM', label: 'INSERM' },
        { value: 'INRAE', label: 'INRAE' },
    ],
    tutelle: [
        { value: 'Université de Paris', label: 'Université de Paris' },
        { value: 'Université de Lyon', label: 'Université de Lyon' },
        { value: 'Université de Marseille', label: 'Université de Marseille' },
    ],
    unite: [],
};

export function DictionnaireProvider({ children }: { children: React.ReactNode }) {
    const [dictionnaire, setDictionnaire] = useState<DictionnaireType>(defaultDictionnaire);

    useEffect(() => {
        let isMounted = true;

        const loadUnites = async () => {
            const unite = await fetchUnites();
            if (isMounted) {
                setDictionnaire((current) => ({
                    ...current,
                    unite,
                }));
            }
        };

        loadUnites();

        return () => {
            isMounted = false;
        };
    }, []);

    return <DictionnaireContext.Provider value={dictionnaire}>{children}</DictionnaireContext.Provider>
}
