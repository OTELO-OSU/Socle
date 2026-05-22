import { Box, Button, Card, Container, Tab, Tabs, Typography } from "@mui/material";
import { useState } from "react";
import { useForm, type FieldError, FormProvider } from "react-hook-form";
import { Presentation } from "../components/formulairePlateforme/Presentation";
import { CustomTabPanel } from "../components/CustomTabPanel";
import { allyProps } from "../components/formulairePlateforme/allyProps";
import { Personnels } from "../components/formulairePlateforme/Personnels";
import { Equipements } from "../components/formulairePlateforme/Equipements";
import { Fonctionnement } from "../components/formulairePlateforme/Fontionnement";
import { Activites } from "../components/formulairePlateforme/Activites";
import { Publications } from "../components/formulairePlateforme/Publlications";


export type FormValues = {
    id: number;
    nom_officiel: string;
    forme_developpee_nom_officiel: string;
    site_web: string;
    logo_url: string;
    autre_type_labellisation: string;
    latitude: number | null;
    longitude: number | null;
    espace_bureau: number | null;
    personnels: {
        id: number;
        quotite: number | null;
    }[]
    espace_laboratoire: number | null;
    materiel_consommable: number | null;
    contrat_entretien: number | null;
    etude_prestation: number | null;
    fluide_energie: number | null;
    mission_frais_generaux: number | null;
    contribution_tutelle_taux: number | null;
    contribution_fond_public_taux: number | null;
    contribution_fond_prive_taux: number | null;
    contribution_credit_taux: number | null;
    contribution_projet_recherche_taux: number | null;
    contribution_structure_pia_taux: number | null;
    dotation_instrument_national: number;
    labellisation_qualite: boolean | null;
    nom_labellisation_qualite: string;
    procedure_transparente_tous: boolean;
    procedure_transparente_prestation_exterieure: boolean;
    accueil_international_taux: number | null;
    modele_77_2424_taux: number | null;
    modele_77_1224_taux: number | null;
    modele_57_2424_taux: number | null;
    modele_57_1224_taux: number | null;
    utilisation_fonctionnement_taux: number | null;
    utilisation_maintenance_taux: number | null;
    utilisation_developpement_taux: number | null;
    utilisation_panne_taux: number | null;
    utilisation_temps_mort_taux: number | null;
    utilisation_equipe_operante_taux: number | null;
    utilisation_collaboration_interne_taux: number | null;
    utilisation_collaboration_externe_taux: number | null;
    utilisation_prestation_service_taux: number | null;
    bges_realise: boolean | null;
    bges_domicile_travail: number | null;
    bges_mission: number | null;
    bges_mission_ext: number | null;
    bges_consommable: number | null;
    bges_equipement: number | null;
    bges_envoi_echantillon: number | null;
    bges_batiment: number | null;
    bges_dechet: number | null;
    publication_equipe_operante: number | null;
    publication_collaboration_interne: number | null;
    publication_collaboration_externe: number | null;
    publication_autre_type: number | null;
    publication_these: number | null;
    publications: {
        numero: number;
        doi: string;
    }[]
}

export default function ModificationPlateforme() {
    const methods = useForm<FormValues>({
        defaultValues: {
            publications: []
        }
    });
    const { handleSubmit, formState: { errors } } = methods;

    function onSubmit(data: FormValues) {
        console.log(data);
    }

    const [value, setValue] = useState(0);

    const handleChange = (_: React.SyntheticEvent, newValue: number) => {
        setValue(newValue);
    };

    return (
        <Container sx={{ py: 4 }}>
            <FormProvider {...methods}>
                <form noValidate onSubmit={handleSubmit(onSubmit)}>
                    <Card sx={{ display: "flex", flexDirection: "column", p: 4 }}>
                        <Typography variant="h4" component="h1">
                            Modifier une Plateforme
                        </Typography>
                        <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
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