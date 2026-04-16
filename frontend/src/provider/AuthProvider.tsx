import { useCallback, useEffect, useMemo, useState } from 'react'
import { AuthContext } from '../context/authContext'
import { registerTokenClearer, registerTokenSetter, syncTokenOutside } from '../bridge/tokenBridge'
import { decodeToken } from '../utils/jwt'
import * as authService from '../services/authService';

export function AuthProvider({ children }: { children: React.ReactNode }) {
    const [token, setTokenState] = useState<string | null>(null)
    const [isAuthLoading, setIsAuthLoading] = useState(true)

    const setToken = useCallback((value: string | null) => {
        syncTokenOutside(value)
        setTokenState(value)
    }, [])

    const clearToken = useCallback(() => {
        setToken(null)
    }, [setToken])

    useEffect(() => {
        let isMounted = true;

        (async () => {
            try {
                const { data } = await authService.refreshSession();
                if (typeof data?.token === 'string' && data.token.length > 0) {
                    setToken(data.token);
                }
            } catch {
                clearToken();
            } finally {
                if (isMounted) {
                    setIsAuthLoading(false);
                }
            }
        })();

        return () => {
            isMounted = false;
        };
    }, [clearToken, setToken])

    const decodedToken = useMemo(() => {
        if (!token) return null
        return decodeToken(token)
    }, [token])

    const connexion = useCallback(async (email: string, mdp: string) => {
        const { data } = await authService.connexion(email, mdp);
        setToken(data.token);
    }, [setToken]);

    const connexionViaOrcid = useCallback(async (code: string) => {
        const { data } = await authService.connexionViaOrcid(code);
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
            connexionViaOrcid,
            deconnexion,
            isAuthLoading,
            estConnecte: Boolean(token),
            estAdmin: decodedToken?.roles?.includes('ROLE_ADMIN') || false,
            id: decodedToken?.id || null,
            email: decodedToken?.email || null,
            prenom: decodedToken?.prenom || null,
            nom: decodedToken?.nom || null,
        }),
        [clearToken, setToken, connexion, connexionViaOrcid, deconnexion, isAuthLoading, token, decodedToken],
    )

    useEffect(() => {
        registerTokenSetter(setToken);
    }, [setToken]);

    useEffect(() => {
        registerTokenClearer(clearToken);
    }, [clearToken]);

    return <AuthContext.Provider value={contextValue}>{children}</AuthContext.Provider>
}
