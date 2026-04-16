import axios, { AxiosError } from "axios";
import { API_URL } from "./constante";
import { showSnackbar } from "../bridge/snackbarBridge";
import { clearTokenOutside, getTokenOutside, setTokenOutside } from "../bridge/tokenBridge";
import { navigateOutside } from "../bridge/navigationBridge";

type RetriableRequestConfig = {
    _retry?: boolean;
    url?: string;
    headers?: Record<string, string>;
};

export const symfony = axios.create({
    baseURL: API_URL,
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
    },
})

let refreshPromise: Promise<string | null> | null = null;

const refreshAccessToken = async () => {
    if (!refreshPromise) {
        refreshPromise = axios
            .post(`${API_URL}/refresh`, {}, { withCredentials: true })
            .then((response) => {
                const refreshedToken = response.data?.token;

                if (typeof refreshedToken === "string" && refreshedToken.length > 0) {
                    setTokenOutside(refreshedToken);
                    return refreshedToken;
                }

                return null;
            })
            .finally(() => {
                refreshPromise = null;
            });
    }

    return refreshPromise;
};

const forceLogout = () => {
    clearTokenOutside();
    showSnackbar({
        message: "Votre session a expire, veuillez vous reconnecter.",
        couleur: "error",
    });
    navigateOutside("/connexion");
};

// Intercepteur pour refresh le token si une requete retourne une erreur 401 avec le message "Token expiré"
symfony.interceptors.response.use(
    response => response,
    async error => {
        const status = error.response?.status;
        const originalRequest = error.config as RetriableRequestConfig | undefined;
        const isRefreshRequest = originalRequest?.url?.includes('/refresh');

        if (status === 401 && originalRequest && !originalRequest._retry && !isRefreshRequest) {
            originalRequest._retry = true;

            try {
                const refreshedToken = await refreshAccessToken();

                if (typeof refreshedToken === "string" && refreshedToken.length > 0) {
                    originalRequest.headers = originalRequest.headers || {};
                    originalRequest.headers.Authorization = `Bearer ${refreshedToken}`;
                    return symfony.request(originalRequest);
                }

                forceLogout();
            } catch (refreshError) {
                const refreshErrorResponse = refreshError as AxiosError<ApiError>;
                if (refreshErrorResponse.response?.status === 401 || refreshErrorResponse.response?.status === 403) {
                    forceLogout();
                }
            }
        }

        return Promise.reject(error);
    }
)

// Intercepteur pour afficher une snackbar en cas d'erreur 500
symfony.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 500) {
            showSnackbar({
                message: "Une erreur est survenue",
                couleur: "error",
            });
        }
        return Promise.reject(error);
    }
)

// Interceptor pour mettre le bearer token dans les headers de chaque requete
symfony.interceptors.request.use(
    config => {
        const token = getTokenOutside();
        if (token) {
            config.headers = config.headers || {};
            config.headers.Authorization = `Bearer ${token}`;
        }

        return config;
    },
    error => Promise.reject(error),
)

export type ApiError = {
    message: string;
    messageArray: string[];
};