import { createContext } from 'react'
import type { SnackbarPayload } from '../provider/SnackbarProvider';

export type SnackbarContextType = 
  SnackbarPayload & {
    open: boolean;
  trigger: (payload: SnackbarPayload) => void;
  close: () => void;
  }


export const SnackbarContext = createContext<SnackbarContextType | undefined>(undefined)
