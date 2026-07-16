import { Box, CircularProgress, Typography } from "@mui/material";
import { useContext, useEffect } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import { SnackbarContext } from "../context/snackbarContext";

export default function FedeCallback() {

    const snackbarContext = useContext(SnackbarContext);
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();

    useEffect(() => {
        const status = searchParams.get("status");

        if (!status) {
            navigate('/')
            return;
        }

        if (status === "success") {
            snackbarContext?.trigger({
                message: "Connexion réussie",
                couleur: "success",
            });
            navigate('/', { replace: true });
            return;
        } else if (status === "nonAdmis") {
            snackbarContext?.trigger({
                message: "Votre compte n'a pas encore été admis. Un email vous sera envoyé une fois votre compte activé.",
                couleur: "warning",
            });
            navigate('/connexion', { replace: true });
            return;
        } else {
            snackbarContext?.trigger({
                message: "Une erreur est survenue lors de la connexion via le fédérateur d'identité",
                couleur: "error",
            });
            navigate('/connexion', { replace: true });
            return;
        }

    }, []);

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