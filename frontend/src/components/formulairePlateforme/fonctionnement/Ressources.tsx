import { Box, TextField, Typography } from "@mui/material";
import { useFormContext } from "react-hook-form";
import TauxTextFieldHint from "../../TauxTextFieldHint";
import CoutTextFieldHint from "../../CoutTextFieldHint";



export default function Ressources() {
    const { getValues, watch } = useFormContext();
    watch(["contribution_tutelle_taux", "contribution_fond_public_taux", "contribution_fond_prive_taux", "contribution_credit_taux", "contribution_projet_recherche_taux", "contribution_structure_pia_taux"], () => {
        totalTaux();
    });
    function totalTaux() {
        const contribution_tutelle_taux = Number(getValues("contribution_tutelle_taux")) || 0;
        const contribution_fond_public_taux = Number(getValues("contribution_fond_public_taux")) || 0;
        const contribution_fond_prive_taux = Number(getValues("contribution_fond_prive_taux")) || 0;
        const contribution_credit_taux = Number(getValues("contribution_credit_taux")) || 0;
        const contribution_projet_recherche_taux = Number(getValues("contribution_projet_recherche_taux")) || 0;
        const contribution_structure_pia_taux = Number(getValues("contribution_structure_pia_taux")) || 0;
        return contribution_tutelle_taux + contribution_fond_public_taux + contribution_fond_prive_taux + contribution_credit_taux + contribution_projet_recherche_taux + contribution_structure_pia_taux;
    }
    return (
        <Box>
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold" }}>
                Origine des ressources</Typography>
            <Box sx={{ display: "flex", flexDirection: "column", gap: 1, mt: 2 }}>
                <TauxTextFieldHint label="Contribution directe aux recettes de la plateforme sous forme de subvention fléchées des tutelles ? (exemple INSU pour INs, IRD pour ces plateformes...)" name="contribution_tutelle_taux" helperText="" />
                <TauxTextFieldHint label="Contribution via la tarification des analyses par la plateforme sur des fonds publics" name="contribution_fond_public_taux" helperText="" />
                <TauxTextFieldHint label="Contribution via la tarification des analyses par la plateforme sur des fonds privés" name="contribution_fond_prive_taux" helperText="" />
                <TauxTextFieldHint label="Contribution directe du laboratoire sur ses crédits" name="contribution_credit_taux" helperText="" />
                <TauxTextFieldHint label="Contribution directe de projets de recherche (ANR, ERC, INSU, ...)" name="contribution_projet_recherche_taux" helperText="" />
                <TauxTextFieldHint label="Contribution directe de structures PIA (LABEX, Equipex, ...)" name="contribution_structure_pia_taux" helperText="" />
                <CoutTextFieldHint label="Somme des coûts" name="" helperText="" >
                    <TextField
                        label="Somme des %"
                        variant="outlined"
                        size="small"
                        value={totalTaux()}
                        fullWidth
                        slotProps={{
                            input: {
                                readOnly: true,
                                endAdornment: "%",
                            }
                        }}
                    />
                </CoutTextFieldHint>
            </Box>
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 4 }}>
                Information Instrument National
            </Typography>
            <CoutTextFieldHint label="Dotation de fonctionnement de la part de l’INSU à titre d’Instrument National" name="dotation_instrument_national" helperText="" />
        </Box>

    );
}