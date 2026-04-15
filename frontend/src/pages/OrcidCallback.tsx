import { Box, CircularProgress, Typography } from "@mui/material";
import { useContext, useEffect, useRef } from "react";
import { AuthContext } from "../context/authContext";
import { useNavigate, useSearchParams } from "react-router-dom";
import { SnackbarContext } from "../context/snackbarContext";
import type { AxiosError } from "axios";
import type { ApiError } from "../utils/axios";

export default function OrcidCallback() {

    const authContext = useContext(AuthContext);
    const snackbarContext = useContext(SnackbarContext);
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();

    const effectLance = useRef(false);

    useEffect(() => {
        const code = searchParams.get("code");

        if (!code) {
            navigate('/')
            return;
        }

        if (effectLance.current) return;
        effectLance.current = true;

        (async () => {
            try {
                await authContext?.connexionViaOrcid(code!);
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