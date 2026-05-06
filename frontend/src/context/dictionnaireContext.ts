import { createContext } from 'react'
import type { DictionnaireType } from '../provider/DictionnaireProvider'

export const DictionnaireContext = createContext<DictionnaireType>(undefined as unknown as DictionnaireType);
