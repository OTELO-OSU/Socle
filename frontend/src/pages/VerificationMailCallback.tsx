import { Box, CircularProgress, Typography } from "@mui/material";
import { useContext, useEffect } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import { SnackbarContext } from "../context/snackbarContext";
import type { AxiosError } from "axios";
import type { ApiError } from "../utils/axios";
import { verifierMail } from "../services/authService";

export default function VerificationMailCallback() {

    const snackbarContext = useContext(SnackbarContext);
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();

    useEffect(() => {
        const token = searchParams.get("token");

        if (!token) {
            navigate('/')
            return;
        }

        (async () => {
            try {
                const { data } = await verifierMail(token!);
                snackbarContext?.trigger({
                    message: data.message || "Votre adresse mail a été validée avec succès",
                    couleur: "success",
                });
                navigate('/');
            } catch (error) {
                const errorAxios = error as AxiosError<ApiError>;
                snackbarContext?.trigger({
                    message: errorAxios.response?.data.message || "Une erreur est survenue lors de la validation de votre adresse mail",
                    couleur: "error",
                });
                navigate('/connexion');
            }
        })();

    }, [searchParams, snackbarContext, navigate]);

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