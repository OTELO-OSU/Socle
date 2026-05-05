import { createContext } from 'react'

export type DictionnaireOption = {
	value: string;
	label: string;
}

export type DictionnaireType = {
	ville: DictionnaireOption[];
	laboratoire: DictionnaireOption[];
	tutelle: DictionnaireOption[];
}

export const defaultDictionnaire: DictionnaireType = {
	ville: [],
	laboratoire: [],
	tutelle: [],
}

export const DictionnaireContext = createContext<DictionnaireType>(defaultDictionnaire)
