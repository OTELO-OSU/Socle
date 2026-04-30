import { Box, CircularProgress, Typography } from "@mui/material";
import { useContext, useEffect } from "react";
import { AuthContext } from "../context/authContext";
import { useNavigate, useSearchParams } from "react-router-dom";
import { SnackbarContext } from "../context/snackbarContext";
import type { AxiosError } from "axios";
import type { ApiError } from "../utils/axios";
import { connexionViaOrcid } from "../services/authService";

export default function OrcidCallback() {

    const authContext = useContext(AuthContext);
    const snackbarContext = useContext(SnackbarContext);
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();

    useEffect(() => {
        const code = searchParams.get("code");

        if (!code) {
            navigate('/')
            return;
        }

        (async () => {
            try {
                const { data } = await connexionViaOrcid(code);
                if (data.redirect !== undefined) {
                    const url = new URL(data.redirect);
                    const redirect = url.pathname + url.search;
                    navigate(redirect);
                    return;
                }
                navigate('/');
            } catch (error) {
                const errorAxios = error as AxiosError<ApiError>;
                snackbarContext?.trigger({
                    message: errorAxios.response?.data.message || "Une erreur est survenue lors de la connexion via Orcid",
                    couleur: "error",
                });
                navigate('/connexion');
            }
        })();

    }, [authContext, searchParams, snackbarContext, navigate]);

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
                Connexion en cours
            </Typography>
        </Box>
    )
}