import { Divider, Grid, Stack, Typography } from "@mui/material";
import { useFormContext } from "react-hook-form";
import { useContext } from "react";
import { DictionnaireContext } from "../../context/dictionnaireContext";
import { RHFTextField } from "../RHFTextField";
import { RHFSelect } from "../RHFTextSelect";

export function Presentation() {
    const { control } = useFormContext();

    const { ville, laboratoire, tutelle } = useContext(DictionnaireContext);

    return (
        <>
            <Grid container spacing={2} sx={{ mt: 2 }}>
                <Grid size={{ xs: 1, md: 3 }}>
                    <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold" }}>
                        Général
                    </Typography>
                </Grid>
                <Grid size="grow">
                    <Stack direction="row" spacing={2}>
                        <RHFTextField
                            name="nom"
                            control={control}
                            rules={{ required: "Le nom de la plateforme est requis" }}
                            label="Nom officiel de la plateforme"
                            placeholder="<SARM>"
                        />
                        <RHFTextField
                            name="nomDevelope"
                            control={control}
                            label="Signification de l'acronyme (si le nom officiel en est un)"
                            placeholder="<Service d'Analyse des Roches et des Minéraux>"
                        />
                    </Stack>
                    <RHFTextField
                        sx={{ mt: 2 }}
                        name="siteweb"
                        control={control}
                        label="Site web"
                        placeholder="<https://sarm.cnrs.fr/>"
                    />
                    <RHFSelect name="ville" control={control} id="ville" label="Ville" items={ville} sx={{ mt: 2 }} />
                    <RHFTextField
                        sx={{ mt: 2 }}
                        name="urlLogo"
                        control={control}
                        label="URL du logo de la plateforme"
                        placeholder="<https://sarm.cnrs.fr/images/logo.png>"
                    />
                    <RHFTextField
                        sx={{ mt: 2 }}
                        name="autreTypeLabellisation"
                        control={control}
                        label="Autre type de labellisation"
                    />
                </Grid>
            </Grid>
            <Divider orientation="horizontal" flexItem sx={{ mt: 2 }} />
            <Grid container spacing={2} sx={{ mt: 2 }}>
                <Grid size={{ xs: 1, md: 3 }}>
                    <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold" }}>
                        Appartenance
                    </Typography>
                </Grid>
                <Grid size="grow">
                    <RHFSelect name="laboratoireOperateur" control={control} label="Laboratoire opérateur" items={laboratoire} />
                    <Stack direction="row" spacing={2} sx={{ mt: 2 }}>
                        <RHFSelect name="tutellePrincipale" control={control} label="Tutelle principale" multiple items={tutelle} />
                        <RHFSelect name="tutelleSecondaire" control={control} label="Tutelle secondaire" multiple items={tutelle} />
                    </Stack>
                </Grid>
            </Grid>
            <Divider orientation="horizontal" flexItem sx={{ mt: 2 }} />
            <Grid container spacing={2} sx={{ mt: 2 }}>
                <Grid size={{ xs: 1, md: 3 }}>
                    <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold" }}>
                        Localisation
                    </Typography>
                </Grid>
                <Grid size="grow">
                    <RHFTextField name="latitude" control={control} label="Latitude" placeholder="48.65609" />
                    <RHFTextField name="longitude" control={control} label="Longitude" placeholder="6.15496" sx={{ mt: 2 }} />
                </Grid>
            </Grid>
            <Divider orientation="horizontal" flexItem sx={{ mt: 2 }} />
            <Grid container spacing={2} sx={{ mt: 2 }}>
                <Grid size={{ xs: 1, md: 3 }}>
                    <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold" }}>
                        Locaux utilisés par la plateforme
                    </Typography>
                </Grid>
                <Grid size="grow">
                    <RHFTextField name="espaceBureau" control={control} label="Espace bureau en m²" placeholder="90" />
                    <RHFTextField name="espaceLaboratoire" control={control} label="Espace laboratoire en m²" placeholder="210" sx={{ mt: 2 }} />
                </Grid>
            </Grid>
        </>
    )
}