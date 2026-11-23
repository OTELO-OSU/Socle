import { Box, CircularProgress, Typography } from "@mui/material";
import { useContext, useEffect, useRef } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import { SnackbarContext } from "../context/snackbarContext";
import type { AxiosError } from "axios";
import type { ApiError } from "../utils/axios";
import { verifierMail } from "../services/authService";

export default function VerificationMailCallback() {

    const trigger = useContext(SnackbarContext)?.trigger;
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();
    const token = searchParams.get("token");
    const verification = useRef<{ token: string; promise: ReturnType<typeof verifierMail> } | null>(null);

    useEffect(() => {
        if (!token) {
            navigate('/')
            return;
        }

        // StrictMode rejoue l'effet : la même promesse évite de consommer deux fois le token.
        if (verification.current?.token !== token) {
            verification.current = { token, promise: verifierMail(token) };
        }

        let active = true;
        const promise = verification.current.promise;
        (async () => {
            try {
                const { data } = await promise;
                if (!active) return;
                trigger?.({
                    message: data.message || "Votre adresse mail a été validée avec succès",
                    couleur: "success",
                });
                navigate('/');
            } catch (error) {
                if (!active) return;
                const errorAxios = error as AxiosError<ApiError>;
                trigger?.({
                    message: errorAxios.response?.data.message || "Une erreur est survenue lors de la validation de votre adresse mail",
                    couleur: "error",
                });
                navigate('/connexion');
            }
        })();

        return () => {
            active = false;
        };
    }, [token, trigger, navigate]);

    return (
        <Box
            sx={{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                height: '100%',
                minHeight: (theme) => `calc(100vh - ${theme.mixins.toolbar.minHeight}px)`
            }}
        >
            <CircularProgress size={50} />
            <Typography variant="h3" component="div" sx={{ ml: 2 }}>
                Validation de votre adresse mail
            </Typography>
        </Box>
    )
}