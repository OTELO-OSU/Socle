import { useCallback, useEffect, useMemo, useState } from 'react'
import { AuthContext } from '../context/authContext'
import { decodeToken } from '../utils/jwt'
import * as authService from '../services/authService';
import { registerAuthBridge } from '../bridge/tokenBridge';
import { loadToken, saveToken } from '../utils/sessionStorageToken';

export function AuthProvider({ children }: { children: React.ReactNode }) {
    const [token, setTokenState] = useState<string | null>(() => loadToken());
    const [isAuthLoading, setIsAuthLoading] = useState(true)

    const setToken = useCallback((value: string | null) => {
        setTokenState(value)
    }, [])

    const clearToken = useCallback(() => {
        setToken(null)
    }, [setToken])

    useEffect(() => {
        let isMounted = true;

        if (token) {
            setIsAuthLoading(false);
            return;
        }

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
    }, [token, clearToken, setToken])

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
        saveToken(token);
    }, [token]);

    useEffect(() => {
        registerAuthBridge({
            getToken: () => token,
            setToken,
            clearToken,
        });
    }, [token, setToken, clearToken]);

    return <AuthContext.Provider value={contextValue}>{children}</AuthContext.Provider>
}
