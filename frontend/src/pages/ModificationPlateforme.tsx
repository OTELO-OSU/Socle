import { Box, Button, Card, Container, List, ListItem, ListItemText, Tab, Tabs, Typography } from "@mui/material";
import { useContext, useEffect, useState } from "react";
import { useForm, type FieldError, FormProvider } from "react-hook-form";
import { Presentation } from "../components/formulairePlateforme/Presentation";
import { CustomTabPanel } from "../components/CustomTabPanel";
import { allyProps } from "../components/formulairePlateforme/allyProps";
import { Personnels } from "../components/formulairePlateforme/Personnels";
import { Equipements } from "../components/formulairePlateforme/Equipements";
import { Fonctionnement } from "../components/formulairePlateforme/Fontionnement";
import { Activites } from "../components/formulairePlateforme/Activites";
import { Publications } from "../components/formulairePlateforme/Publlications";
import { enregistrerPlateformeBrouillon, enregistrerPlateformeValidee, fetchPlateformeDetails, testerPlateforme, type DernierHistorique } from "../services/plateformeService";
import WorkspacePremiumIcon from '@mui/icons-material/WorkspacePremium';
import SaveIcon from '@mui/icons-material/Save';
import SendIcon from '@mui/icons-material/Send';
import RefreshIcon from '@mui/icons-material/Refresh';
import { SnackbarContext } from "../context/snackbarContext";
import { AxiosError, isAxiosError } from "axios";
import { fetchDetailPlateformeHarvestee } from "../services/harvesterService";

export default function ModificationPlateforme() {
    const [errorsArray, setErrorsArray] = useState<string[]>([]);
    const searchParams = new URLSearchParams(window.location.search);
    const [readonly, setReadonly] = useState(false);
    const methods = useForm<DernierHistorique>({
        defaultValues: {
            publications: []
        },
        disabled: searchParams.get('harvester') === 'true' || readonly,
    });

    const {
        formState: { isDirty },
        reset,
    } = methods;

    const [verificationValide, setVerificationValide] = useState(false);
    const boutonActif = verificationValide && !isDirty;


    const { handleSubmit, formState: { errors } } = methods;
    const snackbarContext = useContext(SnackbarContext);

    function updateErrorsArray(error: unknown) {
        if (!isAxiosError(error)) {
            return;
        }

        if (error.response?.status === 400) {
            const data = error.response.data as { messageArray?: string[] };
            setErrorsArray(data.messageArray ?? []);
            return;
        }

        setErrorsArray([]);
    }

    function onSubmit(data: DernierHistorique) {
        console.log(data);
    }

    async function sauvegarderAuBroullon() {
        const data = methods.getValues();
        setErrorsArray([]);
        try {
            const res = await enregistrerPlateformeBrouillon(idPlateforme, data)
            snackbarContext?.trigger({
                couleur: "success",
                message: res.message,
            })
        } catch (error) {
            const axiosError = error as AxiosError<{ message: string }>;
            updateErrorsArray(error);

            snackbarContext?.trigger({
                couleur: "error",
                message: axiosError.response?.data.message || "Erreur lors de l'enregistrement au brouillon",
            })
        }

    }

    async function sauvegarder() {
        const data = methods.getValues();
        setErrorsArray([]);
        try {
            const res = await enregistrerPlateformeValidee(idPlateforme, data)
            snackbarContext?.trigger({
                couleur: "success",
                message: res.message,
            })
        } catch (error) {
            updateErrorsArray(error);

            console.error("Erreur lors de l'enregistrement valide :", error);
            snackbarContext?.trigger({
                couleur: "error",
                message: "Erreur lors de l'enregistrement valide",
            })
        }

    }

    async function testPlateforme() {
        const data = methods.getValues();
        setErrorsArray([]);
        try {
            const res = await testerPlateforme(idPlateforme, data)
            snackbarContext?.trigger({
                couleur: "success",
                message: res.message,
            })
            setVerificationValide(true);
            reset(methods.getValues());
        } catch (error) {
            updateErrorsArray(error);
            console.error("Erreur lors du test de la plateforme :", error);
            snackbarContext?.trigger({
                couleur: "error",
                message: "Erreur lors du test de la plateforme",
            })
        }
    }

    const [value, setValue] = useState(0);
    const [idPlateforme, setIdPlateforme] = useState(0);
    const [instrumentNational, setInstrumentNational] = useState(false);

    const handleChange = (_: React.SyntheticEvent, newValue: number) => {
        setValue(newValue);
    };

    useEffect(() => {
        // Récupérer l'id de la plateforme à partir de l'URL
        const searchParams = new URLSearchParams(window.location.search);
        const id = searchParams.get('id');
        const harvester = searchParams.get('harvester');
        const host = searchParams.get('host');

        if (id && harvester == "true") {
            if (!host) window.location.href = "/plateformes";
            fetchDetailPlateformeHarvestee({ id: parseInt(id), host: host! })
                .then((data) => {
                    setIdPlateforme(parseInt(id));
                    // Mettre à jour les valeurs du formulaire avec les données récupérées
                    methods.reset(data.dernier_historique);
                    methods.setValue("annee", undefined);
                    setInstrumentNational(data.instrument_national);
                    if (data.instrument_national) {
                        if (methods.getValues("dotation_instrument_national") === undefined) {
                            methods.setValue("dotation_instrument_national", null);
                        }
                    } else {
                        methods.setValue("dotation_instrument_national", undefined);
                    }
                }).catch((error) => {
                    console.error("Erreur lors du chargement des détails de la plateforme :", error);
                });
        } else if (id && harvester !== "true") {
            fetchPlateformeDetails(parseInt(id))
                .then((data) => {
                    setIdPlateforme(parseInt(id));
                    // Mettre à jour les valeurs du formulaire avec les données récupérées
                    methods.reset(data.dernier_historique);
                    methods.setValue("annee", undefined);
                    setReadonly(data.readonly);
                    setInstrumentNational(data.instrument_national);
                    if (data.instrument_national) {
                        if (methods.getValues("dotation_instrument_national") === undefined) {
                            methods.setValue("dotation_instrument_national", null);
                        }
                    } else {
                        methods.setValue("dotation_instrument_national", undefined);
                    }
                }).catch((error) => {
                    console.error("Erreur lors du chargement des détails de la plateforme :", error);
                });
        } else {
            // Redirect à la liste des plateformes
            window.location.href = "/plateformes";
        }
    }, [methods]);

    return (
        <Container sx={{ py: 4 }}>
            <FormProvider {...methods}>
                <form noValidate onSubmit={handleSubmit(onSubmit)}>
                    <Card sx={{ display: "flex", flexDirection: "column", p: 4 }}>
                        <Box sx={{ display: "flex", justifyContent: 'space-between' }}><Typography variant="h4" component="h1">
                            Modification Plateforme
                        </Typography>
                            {instrumentNational && <Typography sx={{ display: "flex", alignItems: 'center', bgcolor: 'primary.main', color: 'white', p: 2, borderRadius: 1 }} variant="h5">
                                <WorkspacePremiumIcon fontSize="large" />
                                Instrument national
                            </Typography>}
                        </Box>
                        <Box sx={{ borderBottom: 1, borderColor: 'divider', mt: 2 }}>
                            <Tabs value={value} onChange={handleChange} aria-label="basic tabs example">
                                <Tab label="Présentation" {...allyProps(0)} />
                                <Tab label="Personnels" {...allyProps(1)} />
                                <Tab label="Equipements" {...allyProps(2)} />
                                <Tab label="Fonctionnement" {...allyProps(3)} />
                                <Tab label="Activités" {...allyProps(4)} />
                                <Tab label="Publications" {...allyProps(5)} />
                            </Tabs>
                        </Box>
                        <CustomTabPanel value={value} index={0}>
                            <Presentation />
                        </CustomTabPanel>
                        <CustomTabPanel value={value} index={1}>
                            <Personnels />
                        </CustomTabPanel>
                        <CustomTabPanel value={value} index={2}>
                            <Equipements />
                        </CustomTabPanel>
                        <CustomTabPanel value={value} index={3}>
                            <Fonctionnement />
                        </CustomTabPanel>
                        <CustomTabPanel value={value} index={4}>
                            <Activites />
                        </CustomTabPanel>
                        <CustomTabPanel value={value} index={5}>
                            <Publications />
                        </CustomTabPanel>
                    </Card>
                    {errorsArray.length > 0 && (
                        <Box sx={{ mt: 2, p: 2, border: "1px solid", borderColor: "error.main", borderRadius: 1 }}>
                            <List dense>
                                {errorsArray.map((error, index) => (
                                    <ListItem key={index}>
                                        <ListItemText sx={{ color: "error.main" }} primary={error} />
                                    </ListItem>
                                ))}
                            </List>
                        </Box>
                    )}

                    {
                        searchParams.get('harvester') !== 'true' && (
                            <Box sx={{ display: "flex", gap: 1, mt: 2 }}>
                                <Button variant="contained" color="primary" sx={{ mt: 2 }} type="submit" onClick={sauvegarder} disabled={!boutonActif}>
                                    <SendIcon sx={{ mr: 1 }} />
                                    Valider
                                </Button>
                                <Button variant="contained" color="warning" sx={{ mt: 2 }} type="button" onClick={testPlateforme}>
                                    <RefreshIcon sx={{ mr: 1 }} />
                                    Tester
                                </Button>
                                <Button variant="contained" color="secondary" sx={{ mt: 2 }} type="button" onClick={sauvegarderAuBroullon}>
                                    <SaveIcon sx={{ mr: 1 }} />
                                    Enregistrer au brouillon
                                </Button>
                            </Box>
                        )
                    }
                </form>
                <ul>
                    {Object.entries(errors).map(([field, error]) => {
                        const err = error as FieldError;
                        return <li key={field}>{err.message}</li>
                    })}
                </ul>
            </FormProvider>
        </Container >
    )
}