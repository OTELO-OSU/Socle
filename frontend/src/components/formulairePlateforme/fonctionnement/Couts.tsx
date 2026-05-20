import { Box, FormControl, Grid, TextField, Typography } from "@mui/material";
import { RHFTextField } from "../../RHFTextField";
import { useFormContext } from "react-hook-form";
import { RHFSelect } from "../../RHFTextSelect";
import { useContext } from "react";
import { DictionnaireContext } from "../../../context/dictionnaireContext";

function CoutRow({ label, name, helperText, children }: { label: string; name: string; helperText: string, children?: React.ReactNode }) {
    const { control } = useFormContext();
    return (
        <FormControl fullWidth sx={{ mt: 2 }}>
            <Grid container spacing={2}>
                <Grid size={{ xs: 9 }} sx={{ backgroundColor: "whitesmoke", alignItems: 'center', display: 'flex', px: 1 }}>
                    <Typography variant="body1" component="p">
                        {label}
                    </Typography>
                </Grid>
                <Grid size={{ xs: 3 }}>
                    {children || (
                        <RHFTextField
                            name={name}
                            label="Coût"
                            control={control}
                            size="small"
                            slotProps={{
                                input: {
                                    endAdornment: "k€",
                                }
                            }}
                        />
                    )}
                </Grid>
            </Grid>
            <Grid container spacing={2}>
                <Grid size={{ xs: 9 }}>
                    <Typography variant="caption" component="p">
                        {helperText}
                    </Typography>
                </Grid>
            </Grid>
        </FormControl>
    );
}

export default function Couts() {
    const { getValues, watch, control } = useFormContext();
    const dictionnaireContext = useContext(DictionnaireContext);
    watch(["materiel_consommable", "contrat_entretien", "etude_prestation", "fluide_energie", "mission_frais_generaux"], () => {
        totalCoutFonctionnement();
    });
    function totalCoutFonctionnement() {
        const materiel_consommable = Number(getValues("materiel_consommable")) || 0;
        const contrat_entretien = Number(getValues("contrat_entretien")) || 0;
        const etude_prestation = Number(getValues("etude_prestation")) || 0;
        const fluide_energie = Number(getValues("fluide_energie")) || 0;
        const mission_frais_generaux = Number(getValues("mission_frais_generaux")) || 0;
        return materiel_consommable + contrat_entretien + etude_prestation + fluide_energie + mission_frais_generaux;
    }
    return (
        <Box>
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold" }}>
                Coût annuel de fonctionnement</Typography>
            <Box sx={{ display: "flex", flexDirection: "column", gap: 1, mt: 2 }}>
                <CoutRow label="Matériel et consommables" name="materiel_consommable" helperText="Tout petit équipement nécessaire au fonctionnement quotidien de la plateforme (gaz, contenant, solvants, acides, EPI...)" />
                <CoutRow label="Contrats d'entretien" name="contrat_entretien" helperText="Contrats avec des entreprises extérieures (ou le fournisseur de l’équipement) opérant sur la maintenance, calibration, SAV, de l’instrument" />
                <CoutRow label="Études et prestations" name="etude_prestation" helperText="Tout type de sous-traitance externe (hors entretien)" />
                <CoutRow label="Fluides et énergies (chauffage, électricité, eau, refroidissement...)" name="fluide_energie" helperText="Dépenses liées à la consommation en eau et en électricité de la plateforme et des instruments (fonctionnement des instruments, chauffage, refroidissement climatisation...), si payées directement par la plateforme, ou via une contribution de la plateforme aux frais de fluides et d’énergies du laboratoire" />
                <CoutRow label="Missions et frais généraux" name="mission_frais_generaux" helperText="Déplacements en formations/réunions/congrès du personnel de la plateforme et autres frais généraux" />
                <CoutRow label="Somme des coûts" name="" helperText="" >
                    <TextField
                        label="Somme des coûts"
                        variant="outlined"
                        size="small"
                        value={totalCoutFonctionnement()}
                        fullWidth
                        slotProps={{
                            input: {
                                readOnly: true,
                                endAdornment: "k€",
                            }
                        }}
                    />
                </CoutRow>
            </Box>
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 4 }}>
                Comment sont déterminés les tarifs de la plateforme</Typography>
            <RHFSelect control={control} name="determination_tarif_plateforme_id" label={""} size="small" items={dictionnaireContext.determinationTarifPlateformeOptions} />
        </Box>

    );
}