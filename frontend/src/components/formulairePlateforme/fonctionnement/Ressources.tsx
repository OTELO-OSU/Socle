import { Autocomplete, Box, Button, Dialog, IconButton, Stack, TextField, Typography } from "@mui/material";
import { useFormContext } from "react-hook-form";
import TauxTextFieldHint from "../../TauxTextFieldHint";
import EditIcon from '@mui/icons-material/Edit';
import { Add, Delete } from "@mui/icons-material";
import { useContext, useEffect, useState } from "react";
import { DictionnaireContext } from "../../../context/dictionnaireContext";
import { formatEmployeurLabel } from "../../../services/formatLabelService";
import type { SelectOption } from "../../../provider/DictionnaireProvider";
import LayoutLabelHintInput from "../../LayoutLabelHintInput";
import NombreTextFieldHint from "../../NombreTextFieldHint";
import type { PlateformeDictionnaire, SourceFinancement } from "../../../services/plateformeService";

function DialogModifDetailTutelle({ disabled = false }: { disabled?: boolean }) {
    const [open, setOpen] = useState(false);
    const { getValues, setValue, watch } = useFormContext();
    const contribution_tutelle_detail = watch("contribution_tutelle_detail") || [];
    const dictionnaireContext = useContext(DictionnaireContext);
    const plateformeDictionnaire = watch("dictionnaire") as PlateformeDictionnaire | undefined;
    const financeurOptions = plateformeDictionnaire
        ? Object.values(plateformeDictionnaire.entites).map((entite) => ({
            value: entite.id,
            label: entite.acronyme ? `${entite.nom} (${entite.acronyme})` : entite.nom ?? "",
        }))
        : dictionnaireContext.employeurOptions;
    const tauxTotaleTutelle = contribution_tutelle_detail.reduce((acc: number, source: SourceFinancement) => acc + (source.taux || 0), 0);
    const estTauxTutelleValide = tauxTotaleTutelle === 100;
    const estAucunTauxA0 = !contribution_tutelle_detail.some((source: SourceFinancement) => source.taux === 0);
    const tutelleNonRenseignee = contribution_tutelle_detail.some((source: SourceFinancement) => source.financeur_id === null);

    const handleAddSource = () => {
        const currentSources = (getValues("contribution_tutelle_detail") as SourceFinancement[] | undefined) || [];
        setValue(
            "contribution_tutelle_detail",
            [...currentSources, { financeur_id: null, taux: 0 }],
            { shouldDirty: true, shouldValidate: true },
        );
    };

    const handleRemoveSource = (index: number) => {
        const currentSources = (getValues("contribution_tutelle_detail") as SourceFinancement[] | undefined) || [];
        setValue(
            "contribution_tutelle_detail",
            currentSources.filter((_, sourceIndex) => sourceIndex !== index),
            { shouldDirty: true, shouldValidate: true },
        );
    };

    useEffect(() => {
        if (open && contribution_tutelle_detail.length === 0) {
            setValue(
                "contribution_tutelle_detail",
                [{ financeur_id: null, taux: 0 }],
                { shouldDirty: true, shouldValidate: true },
            );
        }
    }, [open, contribution_tutelle_detail.length, setValue]);

    return (
        <>
            <IconButton disabled={disabled} size="small" sx={{ width: "fit-content", mt: 1 }} onClick={() => setOpen(true)}>
                <EditIcon />
            </IconButton>
            <Dialog open={open} onClose={() => setOpen(false)} fullWidth maxWidth="md">
                <Box component="fieldset" disabled={disabled} sx={{ p: 3, display: "flex", flexDirection: "column", gap: 2, border: 0 }}>
                    <Typography variant="h6" component="h2">
                        Détail de la contribution des tutelles
                    </Typography>
                    <Stack direction="row" sx={{ alignItems: "center", justifyContent: "space-between" }}>
                        <Typography variant="body2">Sources de financement des tutelles</Typography>
                        <IconButton aria-label="Ajouter une contribution de tutelle" color="primary" onClick={handleAddSource}>
                            <Add />
                        </IconButton>
                    </Stack>
                    {contribution_tutelle_detail.map((source: SourceFinancement, index: number) => (
                        <Stack
                            key={index}
                            direction={{ xs: "column", sm: "row" }}
                            spacing={1}
                            sx={{ alignItems: { xs: "stretch", sm: "center" } }}
                        >
                            <Box sx={{ flex: "2 1 0", width: { xs: "100%", sm: "auto" }, minWidth: 0 }}>
                                <Autocomplete<SelectOption, false, false, false>
                                    options={financeurOptions}
                                    fullWidth
                                    value={financeurOptions.find((option) => option.value === source.financeur_id) ?? null}
                                    onChange={(_, value) => {
                                        const selectedValue = value?.value;
                                        const financeurId = typeof selectedValue === "number" ? selectedValue : null;
                                        const currentSources = (getValues("contribution_tutelle_detail") as SourceFinancement[] | undefined) || [];
                                        const newSources = [...currentSources];
                                        newSources[index] = { ...newSources[index], financeur_id: financeurId };
                                        setValue("contribution_tutelle_detail", newSources);
                                    }}
                                    renderInput={(params) => <TextField {...params} label={`Tutelle financeur #${index + 1}`} size="small" />}
                                />
                            </Box>
                            <Box sx={{ flex: "1 1 0", width: { xs: "100%", sm: "auto" }, minWidth: 0 }}>
                                <TextField
                                    fullWidth
                                    size="small"
                                    label="Taux de contribution"
                                    value={source.taux}
                                    onChange={(e) => {
                                        const value = Number(e.target.value) || 0;
                                        const currentSources = getValues("contribution_tutelle_detail") || [];
                                        const newSources = [...currentSources];
                                        newSources[index] = { ...newSources[index], taux: value };
                                        setValue("contribution_tutelle_detail", newSources);
                                    }}
                                    slotProps={{
                                        input: {
                                            endAdornment: <Typography variant="body2">%</Typography>,
                                        }
                                    }}
                                />
                            </Box>
                            <IconButton
                                aria-label={`Supprimer la contribution de tutelle ${index + 1}`}
                                color="error"
                                onClick={() => handleRemoveSource(index)}
                                sx={{ alignSelf: { xs: "flex-end", sm: "center" }, flexShrink: 0 }}
                            >
                                <Delete />
                            </IconButton>
                        </Stack>
                    ))}
                    {contribution_tutelle_detail.length === 0 && (
                        <Typography variant="body2">
                            Aucune information détaillée enregistrée.
                        </Typography>
                    )}

                    <Typography
                        variant="body2"
                        sx={{
                            mt: 1,
                            color: estTauxTutelleValide
                                ? "success.main"
                                : "error.main",
                            fontWeight: 600,
                        }}
                    >
                        Total financement: {tauxTotaleTutelle}%
                        {!estTauxTutelleValide && " (la somme doit etre egale a 100%)"}
                    </Typography>
                    {!estAucunTauxA0 && (
                        <Typography variant="body2" color="error.main" sx={{ mt: 0.5, fontWeight: 600 }}>
                            Chaque source doit avoir un taux strictement superieur a 0%.
                        </Typography>
                    )}
                    {tutelleNonRenseignee && (
                        <Typography variant="body2" color="error.main" sx={{ mt: 0.5, fontWeight: 600 }}>
                            Chaque source doit avoir une tutelle renseignée.
                        </Typography>
                    )}
                    <Box sx={{ display: "flex", justifyContent: "flex-end" }}>
                        <Button color="primary" onClick={() => setOpen(false)} disabled={!estTauxTutelleValide || !estAucunTauxA0 || tutelleNonRenseignee}>
                            Fermer
                        </Button>
                    </Box>
                </Box>
            </Dialog>
        </>
    )
}

export default function Ressources({ disabled = false }: { disabled?: boolean }) {
    const { getValues, watch, setValue } = useFormContext();
    watch(["contribution_tutelle_taux", "contribution_fond_public_taux", "contribution_fond_prive_taux", "contribution_credit_taux", "contribution_projet_recherche_taux", "contribution_structure_pia_taux"], () => {
        totalTaux();
    });
    const dictionnaireContext = useContext(DictionnaireContext);
    const plateformeDictionnaire = watch("dictionnaire") as PlateformeDictionnaire | undefined;
    const entites = plateformeDictionnaire?.entites;
    const contribution_tutelle_taux = watch("contribution_tutelle_taux");
    if (contribution_tutelle_taux < 1) setValue("contribution_tutelle_detail", []);
    const detailTutelle = getValues("contribution_tutelle_detail") || [];
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
                {(Number(getValues("contribution_tutelle_taux")) || 0) > 0 && (
                    <>
                        <Box sx={{ display: "flex", flexDirection: "column", gap: 1, mt: 2, border: "1px solid", borderColor: "divider", borderRadius: 1, p: 2 }}>
                            <Stack direction={"row"} spacing={2} sx={{ justifyContent: "space-between" }}>
                                <Typography variant="subtitle2" component="h3" sx={{ fontWeight: "bold" }}>
                                    Détail de la contribution des tutelles
                                </Typography>
                                <DialogModifDetailTutelle disabled={disabled} />
                            </Stack>
                            {detailTutelle.length > 0 ? detailTutelle.map((detail: SourceFinancement, index: number) => (
                                <Typography key={index} variant="body2" component="p">
                                    {entites?.[String(detail.financeur_id)]?.nom
                                        ?? formatEmployeurLabel(dictionnaireContext.employeur.find((tutelle) => tutelle.id === detail.financeur_id))}
                                    : {detail.taux}%
                                </Typography>
                            )) : null}
                            {detailTutelle.length === 0 && (
                                <Typography variant="body2" component="p">
                                    Aucune information détaillée enregistrée.
                                </Typography>
                            )}
                        </Box>
                    </>
                )}
                <TauxTextFieldHint label="Contribution via la tarification des analyses par la plateforme sur des fonds publics" name="contribution_fond_public_taux" helperText="" />
                <TauxTextFieldHint label="Contribution via la tarification des analyses par la plateforme sur des fonds privés" name="contribution_fond_prive_taux" helperText="" />
                <TauxTextFieldHint label="Contribution directe du laboratoire sur ses crédits" name="contribution_credit_taux" helperText="" />
                <TauxTextFieldHint label="Contribution directe de projets de recherche (ANR, ERC, INSU, ...)" name="contribution_projet_recherche_taux" helperText="" />
                <TauxTextFieldHint label="Contribution directe de structures PIA (LABEX, Equipex, ...)" name="contribution_structure_pia_taux" helperText="" />
                <LayoutLabelHintInput label="Somme des taux" helperText="" >
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
                </LayoutLabelHintInput>
            </Box>
            {getValues("dotation_instrument_national") !== undefined && (
                <>
                    <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 4 }}>
                        Information Instrument National
                    </Typography>
                    <NombreTextFieldHint slotProps={{
                        input: {
                            endAdornment: 'k€'
                        }
                    }} labelTF="Coût" label="Dotation de fonctionnement de la part de l’INSU à titre d’Instrument National" name="dotation_instrument_national" helperText="" />
                </>
            )}
        </Box>

    );
}