import { Box, Button, Card, Container, Tab, Tabs, Typography } from "@mui/material";
import { useEffect, useState } from "react";
import { useForm, type FieldError, FormProvider } from "react-hook-form";
import { Presentation } from "../components/formulairePlateforme/Presentation";
import { CustomTabPanel } from "../components/CustomTabPanel";
import { allyProps } from "../components/formulairePlateforme/allyProps";
import { Personnels } from "../components/formulairePlateforme/Personnels";
import { Equipements } from "../components/formulairePlateforme/Equipements";
import { Fonctionnement } from "../components/formulairePlateforme/Fontionnement";
import { Activites } from "../components/formulairePlateforme/Activites";
import { Publications } from "../components/formulairePlateforme/Publlications";
import { fetchPlateformeDetails, type DernierHistorique } from "../services/plateformeService";
import WorkspacePremiumIcon from '@mui/icons-material/WorkspacePremium';


export default function ModificationPlateforme() {
    const methods = useForm<DernierHistorique>({
        defaultValues: {
            publications: []
        }
    });
    const { handleSubmit, formState: { errors } } = methods;

    function onSubmit(data: DernierHistorique) {
        console.log(data);
    }

    const [value, setValue] = useState(0);
    const [instrumentNational, setInstrumentNational] = useState(false);

    const handleChange = (_: React.SyntheticEvent, newValue: number) => {
        setValue(newValue);
    };

    useEffect(() => {
        // Récupérer l'id de la plateforme à partir de l'URL
        const searchParams = new URLSearchParams(window.location.search);
        const id = searchParams.get('id');

        if (id) {
            fetchPlateformeDetails(parseInt(id))
                .then((data) => {
                    // Mettre à jour les valeurs du formulaire avec les données récupérées
                    methods.reset(data.dernier_historique);
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
                    <Button variant="contained" color="primary" sx={{ mt: 2 }} type="submit">
                        Enregistrer les modifications
                    </Button>
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