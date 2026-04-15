import axios, { AxiosError } from "axios";
import { API_URL } from "./constante";
import { showSnackbar } from "../bridge/snackbarBridge";
import { clearTokenOutside } from "../bridge/tokenBridge";

export const symfony = axios.create({
    baseURL: API_URL,
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
    },
})

// Intercepteur pour refresh le token si une requete retourne une erreur 401 avec le message "Token expiré"
symfony.interceptors.response.use(
    response => response,
    async error => {
        if (error.response && error.response.status === 401 && error.response.data?.message === "Expired JWT Token") {
            try {
                await axios.post(`${API_URL}/refresh`, {}, { withCredentials: true });
                // Refaire la requete originale
                return symfony.request(error.config);
            } catch (refreshError) {
                const refreshErrorResponse = refreshError as AxiosError<ApiError>;
                // Si le refresh échoue, rediriger vers la page de connexion
                if (refreshErrorResponse.response?.status === 403 && refreshErrorResponse.response.data?.message === "Le refresh token est invalide ou expiré.") {
                    clearTokenOutside();
                    showSnackbar({
                        message: "Votre session a expiré, veuillez vous reconnecter.",
                        couleur: "error",
                    });
                window.location.href = "/connexion";
                }
                return Promise.reject(refreshError);
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

export type ApiError = {
  message: string;
};