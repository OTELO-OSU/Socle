import { useCallback, useEffect, useMemo, useState } from 'react'
import { SnackbarContext } from '../context/snackbarContext'
import { setSnackbarHandler } from '../bridge/snackbarBridge';

export type SnackbarPayload = {
    message: string,
    couleur: 'success' | 'error' | 'info' | 'warning',
    duree?: number | null,
}


export function SnackbarProvider({ children }: { children: React.ReactNode }) {
    const [snackbar, setSnackbar] = useState<SnackbarPayload & { open: boolean }>({
        open: false,
        message: '',
        couleur: 'info',
        duree: 3000,
    });

    const trigger = useCallback(({ message, couleur, duree }: SnackbarPayload) => {
        setSnackbar({
            open: true,
            message,
            couleur,
            duree,
        });
    }, []);

    const close = useCallback(() => {
        setSnackbar((previous) => ({
            ...previous,
            open: false,
        }));
    }, []);

    const contextValue = useMemo(
        () => ({
            ...snackbar,
            trigger,
            close,
        }),
        [snackbar, trigger, close],
    )

    useEffect(() => {
        setSnackbarHandler(trigger);
    }, [trigger]);

    return <SnackbarContext.Provider value={contextValue}>{children}</SnackbarContext.Provider>
}
