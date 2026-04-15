import { useCallback, useEffect, useMemo, useState } from 'react'
import { STORAGE_KEY, AuthContext } from '../context/authContext'
import { registerTokenClearer, registerTokenSetter } from '../bridge/tokenBridge'
import { decodeToken } from '../utils/jwt'
import * as authService from '../services/authService';

export function AuthProvider({ children }: { children: React.ReactNode }) {
    const [token, setTokenState] = useState<string | null>(() => localStorage.getItem(STORAGE_KEY))

    const setToken = useCallback((value: string | null) => {
        setTokenState(value)

        if (value) {
            localStorage.setItem(STORAGE_KEY, value)
            return
        }

        localStorage.removeItem(STORAGE_KEY)
    }, [])

    const clearToken = useCallback(() => {
        setToken(null)
    }, [setToken])

    const decodedToken = useMemo(() => {
        if (!token) return null
        return decodeToken(token)
    }, [token])

    const connexion = useCallback(async (email: string, mdp: string) => {
        const { data } = await authService.connexion(email, mdp);
        setToken(data.token);
    }, [setToken]);

    const deconnexion = useCallback(async () => {
        if (!token) return;
        await authService.deconnexion(token);
        clearToken();
    }, [token, clearToken]);

    const contextValue = useMemo(
        () => ({
            token,
            setToken,
            clearToken,
            connexion,
            deconnexion,
            estConnecte: Boolean(token),
            estAdmin: decodedToken?.roles?.includes('ROLE_ADMIN') || false,
            id: decodedToken?.id || null,
            email: decodedToken?.email || null,
            prenom: decodedToken?.prenom || null,
            nom: decodedToken?.nom || null,
        }),
        [clearToken, setToken, connexion, deconnexion, token, decodedToken],
    )

    useEffect(() => {
        registerTokenSetter(setToken);
    }, [setToken]);

    useEffect(() => {
        registerTokenClearer(clearToken);
    }, [clearToken]);

    return <AuthContext.Provider value={contextValue}>{children}</AuthContext.Provider>
}
