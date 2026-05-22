import { Box, TextField, Typography } from "@mui/material";
import RadioTextFieldHint from "../../RadioTextFieldHint";
import { Controller, useFormContext, useWatch } from "react-hook-form";
import TauxTextFieldHint from "../../TauxTextFieldHint";
import CoutTextFieldHint from "../../CoutTextFieldHint";

export default function Accesibilite() {
    const { setValue } = useFormContext();

    const possedeLabellisation = useWatch({ name: "labellisation_qualite", compute: (value) => value === "true" });
    if (!possedeLabellisation) {
        setValue("nom_labellisation_qualite", "");
    }
    return (
        <Box>
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 2 }}>
                Accès et production de la plateforme
            </Typography>
            <Box sx={{ display: "flex", flexDirection: "column", gap: 2 }}>
                <RadioTextFieldHint label="Votre plateforme a-t-elle une labellisation/certification qualité (ISO, Cofrac...) ?" name="labellisation_qualite" helperText="" />
                {(possedeLabellisation) && (
                    <>
                        <CoutTextFieldHint label="Quel est le nom de la labellisation/certification qualité ?" name="" helperText="">
                            <Controller
                                name="nom_labellisation_qualite"
                                render=
                                {({ field }) => (
                                    <TextField fullWidth size="small" label="Nom de la labellisation" {...field} />
                                )}>
                            </Controller>
                        </CoutTextFieldHint>
                    </>
                )}
                <TauxTextFieldHint label="Pourcentage d'accueil international (Projets et prestations internationaux / Projets et prestations totaux)" name="accueil_international_taux" helperText="" />
            </Box>
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 4 }}>
                Votre plateforme a-t-elle une procédure de sélection transparente pour l'accès aux instruments ? (Formulaire, Projet, et comité de pilotage?)
            </Typography>
            <Box sx={{ display: "flex", flexDirection: "column", gap: 2 }}>
                <RadioTextFieldHint label="Pour les projets en collaboration et les prestations extérieures" name="procedure_transparente_prestation_exterieure" helperText="" />
                <RadioTextFieldHint label="Pour tous les projets (y compris ceux des chercheurs de la plateforme)" name="procedure_transparente_tous" helperText="" />
            </Box>
        </Box>
    )
}