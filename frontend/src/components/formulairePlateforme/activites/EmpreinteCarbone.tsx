import { Box, Link, TextField, Typography } from "@mui/material";
import RadioTextFieldHint from "../../RadioTextFieldHint";
import { useFormContext, useWatch } from "react-hook-form";
import LayoutLabelHintInput from "../../LayoutLabelHintInput";
import NombreTextFieldHint from "../../NombreTextFieldHint";

export default function EmpreinteCarbone() {
    const { getValues, watch } = useFormContext();
    watch(["bges_domicile_travail", "bges_mission", "bges_mission_ext", "bges_consommable", "bges_equipement", "bges_envoi_echantillon", "bges_batiment", "bges_dechet"], () => {
        totalBGES();
    });
    const bgesRealise = useWatch({ name: "bges_realise", compute: (value) => value === true });
    function totalBGES() {
        const bges_domicile_travail = Number(getValues("bges_domicile_travail")) || 0;
        const bges_mission = Number(getValues("bges_mission")) || 0;
        const bges_mission_ext = Number(getValues("bges_mission_ext")) || 0;
        const bges_consommable = Number(getValues("bges_consommable")) || 0;
        const bges_equipement = Number(getValues("bges_equipement")) || 0;
        const bges_envoi_echantillon = Number(getValues("bges_envoi_echantillon")) || 0;
        const bges_batiment = Number(getValues("bges_batiment")) || 0;
        const bges_dechet = Number(getValues("bges_dechet")) || 0;
        return bges_domicile_travail + bges_mission + bges_mission_ext + bges_consommable + bges_equipement + bges_envoi_echantillon + bges_batiment + bges_dechet;
    }
    return (
        <Box>
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 2 }}>
                Bilan de Gaz à effet de Serre (BGES) de la plateforme
            </Typography>
            <Typography variant="subtitle1" component="p">
                L’IR RéGEF recommande l’utilisation de l’outil Labo 1.5 <Link href="https://labos1point5.org/" target="_blank" rel="noopener noreferrer">https://labos1point5.org/</Link> et plus spécifiquement de la feuille de calcul dédiée aux Infrastructures de Recherche pour le calcul de votre Empreinte Carbone (EC en kg eCO2)
            </Typography>
            <RadioTextFieldHint label="Avez-vous réalisé un BGES à l’échelle de la plateforme ?" name="bges_realise" helperText="" />
            {bgesRealise && (
                <>
                    <NombreTextFieldHint label="Domicile-Travail" name="bges_domicile_travail" helperText="" slotProps={{ input: { endAdornment: "kg" } }} />
                    <NombreTextFieldHint label="Missions" name="bges_mission" helperText="" slotProps={{ input: { endAdornment: "kg" } }} />
                    <NombreTextFieldHint label="Missions ext." name="bges_mission_ext" helperText="" slotProps={{ input: { endAdornment: "kg" } }} />
                    <NombreTextFieldHint label="Consommables" name="bges_consommable" helperText="" slotProps={{ input: { endAdornment: "kg" } }} />
                    <NombreTextFieldHint label="Equipements" name="bges_equipement" helperText="" slotProps={{ input: { endAdornment: "kg" } }} />
                    <NombreTextFieldHint label="Envoi des échantillons" name="bges_envoi_echantillon" helperText="" slotProps={{ input: { endAdornment: "kg" } }} />
                    <NombreTextFieldHint label="Bâtiment" name="bges_batiment" helperText="" slotProps={{ input: { endAdornment: "kg" } }} />
                    <NombreTextFieldHint label="Déchets" name="bges_dechet" helperText="" slotProps={{ input: { endAdornment: "kg" } }} />
                    <LayoutLabelHintInput label="Somme des postes" helperText="" >
                        <TextField
                            label="Somme des kg eCO2"
                            variant="outlined"
                            size="small"
                            value={totalBGES()}
                            fullWidth
                            slotProps={{
                                input: {
                                    readOnly: true,
                                    endAdornment: "kg",
                                }
                            }}
                        />
                    </LayoutLabelHintInput>
                </>
            )}
        </Box>
    )
}