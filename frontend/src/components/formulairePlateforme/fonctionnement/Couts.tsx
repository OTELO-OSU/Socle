import { Box, TextField, Typography } from "@mui/material";
import { useFormContext } from "react-hook-form";
import { RHFSelect } from "../../RHFTextSelect";
import { useContext } from "react";
import { DictionnaireContext } from "../../../context/dictionnaireContext";
import LayoutLabelHintInput from "../../LayoutLabelHintInput";
import NombreTextFieldHint from "../../NombreTextFieldHint";
import type { PlateformeDictionnaire } from "../../../services/plateformeService";

export default function Couts() {
    const { getValues, watch, control } = useFormContext();
    const dictionnaireContext = useContext(DictionnaireContext);
    const plateformeDictionnaire = watch("dictionnaire") as PlateformeDictionnaire | undefined;
    const determinationTarifOptions = plateformeDictionnaire
        ? Object.values(plateformeDictionnaire.methodes_tarif).map((methode) => ({
            value: methode.id,
            label: methode.nom ?? "",
        }))
        : dictionnaireContext.determinationTarifPlateformeOptions;
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
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 2 }}>
                Coût annuel de fonctionnement</Typography>
            <Box sx={{ display: "flex", flexDirection: "column", gap: 1, mt: 2 }}>
                <NombreTextFieldHint labelTF="Coût" slotProps={
                    { input: { endAdornment: "k€" } }
                } label="Matériel et consommables" name="materiel_consommable" helperText="Tout petit équipement nécessaire au fonctionnement quotidien de la plateforme (gaz, contenant, solvants, acides, EPI...)" />
                <NombreTextFieldHint labelTF="Coût" slotProps={
                    { input: { endAdornment: "k€" } }
                } label="Contrats d'entretien" name="contrat_entretien" helperText="Contrats avec des entreprises extérieures (ou le fournisseur de l’équipement) opérant sur la maintenance, calibration, SAV, de l’instrument" />
                <NombreTextFieldHint labelTF="Coût" slotProps={
                    { input: { endAdornment: "k€" } }
                } label="Études et prestations" name="etude_prestation" helperText="Tout type de sous-traitance externe (hors entretien)" />
                <NombreTextFieldHint labelTF="Coût" slotProps={
                    { input: { endAdornment: "k€" } }
                } label="Fluides et énergies (chauffage, électricité, eau, refroidissement...)" name="fluide_energie" helperText="Dépenses liées à la consommation en eau et en électricité de la plateforme et des instruments (fonctionnement des instruments, chauffage, refroidissement climatisation...), si payées directement par la plateforme, ou via une contribution de la plateforme aux frais de fluides et d’énergies du laboratoire" />
                <NombreTextFieldHint labelTF="Coût" slotProps={
                    { input: { endAdornment: "k€" } }
                } label="Missions et frais généraux" name="mission_frais_generaux" helperText="Déplacements en formations/réunions/congrès du personnel de la plateforme et autres frais généraux" />
                <LayoutLabelHintInput label="Somme des coûts" helperText="" >
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
                </LayoutLabelHintInput>
            </Box>
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 4 }}>
                Comment sont déterminés les tarifs de la plateforme</Typography>
            <RHFSelect sx={{ mt: 2 }} control={control} name="determination_tarif_plateforme_id" label={""} size="small" items={determinationTarifOptions} />
        </Box>

    );
}