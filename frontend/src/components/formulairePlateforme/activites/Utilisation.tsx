import { Box, TextField, Typography } from "@mui/material";
import TauxTextFieldHint from "../../TauxTextFieldHint";
import { useFormContext } from "react-hook-form";
import LayoutLabelHintInput from "../../LayoutLabelHintInput";

export default function Utilisation() {
    const { getValues, watch } = useFormContext();
    watch(["modele_77_2424_taux", "modele_77_1224_taux", "modele_57_2424_taux", "modele_57_1224_taux"], () => {
        totalTauxModele();
    });
    watch(["utilisation_fonctionnement_taux", "utilisation_maintenance_taux", "utilisation_developpement_taux", "utilisation_panne_taux", "utilisation_temps_mort_taux"], () => {
        totalTauxUtilisation();
    });
    watch(["utilisation_equipe_operante_taux", "utilisation_collaboration_interne_taux", "utilisation_collaboration_externe_taux", "utilisation_prestation_service_taux"], () => {
        totalTauxUtilisationDetail();
    });
    function totalTauxModele() {
        const modele_77_2424_taux = Number(getValues("modele_77_2424_taux")) || 0;
        const modele_77_1224_taux = Number(getValues("modele_77_1224_taux")) || 0;
        const modele_57_2424_taux = Number(getValues("modele_57_2424_taux")) || 0;
        const modele_57_1224_taux = Number(getValues("modele_57_1224_taux")) || 0;
        return modele_77_2424_taux + modele_77_1224_taux + modele_57_2424_taux + modele_57_1224_taux;
    }

    function totalTauxUtilisation() {
        const utilisation_fonctionnement_taux = Number(getValues("utilisation_fonctionnement_taux")) || 0;
        const utilisation_maintenance_taux = Number(getValues("utilisation_maintenance_taux")) || 0;
        const utilisation_developpement_taux = Number(getValues("utilisation_developpement_taux")) || 0;
        const utilisation_panne_taux = Number(getValues("utilisation_panne_taux")) || 0;
        const utilisation_temps_mort_taux = Number(getValues("utilisation_temps_mort_taux")) || 0;
        return utilisation_fonctionnement_taux + utilisation_maintenance_taux + utilisation_developpement_taux + utilisation_panne_taux + utilisation_temps_mort_taux;
    }
    function totalTauxUtilisationDetail() {
        const utilisation_equipe_operante_taux = Number(getValues("utilisation_equipe_operante_taux")) || 0;
        const utilisation_collab_interne_taux = Number(getValues("utilisation_collaboration_interne_taux")) || 0;
        const utilisation_collab_externe_taux = Number(getValues("utilisation_collaboration_externe_taux")) || 0;
        const utilisation_prestation_service_taux = Number(getValues("utilisation_prestation_service_taux")) || 0;
        return utilisation_equipe_operante_taux + utilisation_collab_interne_taux + utilisation_collab_externe_taux + utilisation_prestation_service_taux;
    }
    return (
        <Box>
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 2 }}>
                Sur quel modèle fonctionne/mesure votre plateforme (en pourcentage) ?
            </Typography>
            <Box sx={{ display: "flex", flexDirection: "column", gap: 2 }}>
                <TauxTextFieldHint label="7j/7j, 24h/24h" name="modele_77_2424_taux" helperText="" />
                <TauxTextFieldHint label="7j/7j, 12h/24h" name="modele_77_1224_taux" helperText="" />
                <TauxTextFieldHint label="5j/7j, 24h/24h" name="modele_57_2424_taux" helperText="" />
                <TauxTextFieldHint label="5j/7j, 12h/24h" name="modele_57_1224_taux" helperText="" />
                <LayoutLabelHintInput label="Somme des taux" helperText="" >
                    <TextField
                        label="Somme des %"
                        variant="outlined"
                        size="small"
                        value={totalTauxModele()}
                        fullWidth
                        slotProps={{
                            input: {
                                readOnly: true,
                                endAdornment: "%",
                            }
                        }}
                    />
                </LayoutLabelHintInput>
            </Box>
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 4 }}>
                Quel est le taux d'utilisation de la plateforme à l'année ?
            </Typography>
            <Box sx={{ display: "flex", flexDirection: "column", gap: 2 }}>
                <TauxTextFieldHint label="En fonctionnement (analyse, prestation, contrôle qualité, blanc, standard...) " name="utilisation_fonctionnement_taux" helperText="" />
                <TauxTextFieldHint label="En maintenance" name="utilisation_maintenance_taux" helperText="" />
                <TauxTextFieldHint label="En développement" name="utilisation_developpement_taux" helperText="" />
                <TauxTextFieldHint label="En panne" name="utilisation_panne_taux" helperText="" />
                <TauxTextFieldHint label="En temps mort" name="utilisation_temps_mort_taux" helperText="" />
                <LayoutLabelHintInput label="Somme des taux" helperText="" >
                    <TextField
                        label="Somme des %"
                        variant="outlined"
                        size="small"
                        value={totalTauxUtilisation()}
                        fullWidth
                        slotProps={{
                            input: {
                                readOnly: true,
                                endAdornment: "%",
                            }
                        }}
                    />
                </LayoutLabelHintInput>
            </Box>
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 4 }}>
                Quel est le taux d'utilisation détaillé de la plateforme en fonctionnement à l'année ?
            </Typography>
            <Box sx={{ display: "flex", flexDirection: "column", gap: 2 }}>
                <TauxTextFieldHint label="Par l’équipe opérante: projet porté par un membre de la plateforme " name="utilisation_equipe_operante_taux" helperText="" />
                <TauxTextFieldHint label="En collaboration interne: projet porté par quelqu’un d’autre du laboratoire en collaboration avec la plateforme " name="utilisation_collaboration_interne_taux" helperText="" />
                <TauxTextFieldHint label="En collaboration externe: projet porté par quelqu’un d’un autre laboratoire en collaboration avec la plateforme " name="utilisation_collaboration_externe_taux" helperText="" />
                <TauxTextFieldHint label="Prestation de service: service à 100%, prestation sans collaboration, et pas d’association aux résultats " name="utilisation_prestation_service_taux" helperText="" />
                <LayoutLabelHintInput label="Somme des taux" helperText="" >
                    <TextField
                        label="Somme des %"
                        variant="outlined"
                        size="small"
                        value={totalTauxUtilisationDetail()}
                        fullWidth
                        slotProps={{
                            input: {
                                readOnly: true,
                                endAdornment: "%",
                            }
                        }}
                    />
                </LayoutLabelHintInput>
            </Box>
        </Box >
    )
}