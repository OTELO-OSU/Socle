import { useCallback, useMemo, useState } from 'react'
import { STORAGE_KEY, TokenContext } from '../context/tokenContext'

export function TokenProvider({ children }: { children: React.ReactNode }) {
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

    const contextValue = useMemo(
        () => ({
            token,
            setToken,
            clearToken,
            isAuthenticated: Boolean(token),
        }),
        [clearToken, setToken, token],
    )

    return <TokenContext.Provider value={contextValue}>{children}</TokenContext.Provider>
}
