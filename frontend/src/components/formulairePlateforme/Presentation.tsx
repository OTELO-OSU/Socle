import { Divider, Grid, Stack, Typography } from "@mui/material";
import { useFormContext } from "react-hook-form";
import { useContext } from "react";
import { DictionnaireContext } from "../../context/dictionnaireContext";
import { RHFTextField } from "../RHFTextField";
import { RHFSelect } from "../RHFTextSelect";
import NombreTextField from "../NombreTextField";

export function Presentation() {
    const { control } = useFormContext();

    const { villeOptions, uniteOptions, employeurOptions, tagsOptions } = useContext(DictionnaireContext);

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
                            name="nom_officiel"
                            control={control}
                            rules={{ required: "Le nom de la plateforme est requis" }}
                            label="Nom officiel de la plateforme"
                            placeholder="<SARM>"
                        />
                        <RHFTextField
                            name="forme_developpee_nom_officiel"
                            control={control}
                            label="Signification de l'acronyme (si le nom officiel en est un)"
                            placeholder="<Service d'Analyse des Roches et des Minéraux>"
                        />
                    </Stack>
                    <RHFTextField
                        sx={{ mt: 2 }}
                        name="site_web"
                        control={control}
                        label="Site web"
                        placeholder="<https://sarm.cnrs.fr/>"
                    />
                    <RHFSelect name="ville_id" control={control} id="ville" label="Ville" items={villeOptions} sx={{ mt: 2 }} />
                    <RHFTextField
                        sx={{ mt: 2 }}
                        name="logo_url"
                        control={control}
                        label="URL du logo de la plateforme"
                        placeholder="<https://sarm.cnrs.fr/images/logo.png>"
                    />
                    <RHFTextField
                        sx={{ mt: 2 }}
                        name="autre_type_labellisation"
                        control={control}
                        label="Autre type de labellisation"
                    />
                </Grid>
            </Grid>
            <Divider orientation="horizontal" flexItem sx={{ mt: 2 }} />
            <Grid container spacing={2} sx={{ mt: 2 }}>
                <Grid size={{ xs: 1, md: 3 }}>
                    <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold" }}>
                        Tags
                    </Typography>
                </Grid>
                <Grid size="grow">
                    <RHFSelect name="tags_ids" multiple control={control} label="Tags" items={tagsOptions} />
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
                    <RHFSelect name="laboratoire_operateur_id" control={control} label="Laboratoire opérateur" items={uniteOptions} />
                    <Stack direction="row" spacing={2} sx={{ mt: 2 }}>
                        <RHFSelect name="tutelles_principales_ids" control={control} label="Tutelle(s) principale(s)" multiple items={employeurOptions} />
                        <RHFSelect name="tutelles_secondaires_ids" control={control} label="Tutelle(s) secondaire(s)" multiple items={employeurOptions} />
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
                    <NombreTextField name="latitude" size="medium" label="Latitude" placeholder="48.65609" />
                    <NombreTextField name="longitude" size="medium" label="Longitude" placeholder="6.15496" sx={{ mt: 2 }} />
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
                    <NombreTextField name="espace_bureau" size="medium" label="Espace bureau en m²" placeholder="90" />
                    <NombreTextField name="espace_laboratoire" size="medium" label="Espace laboratoire en m²" placeholder="210" sx={{ mt: 2 }} />
                </Grid>
            </Grid>
        </>
    )
}