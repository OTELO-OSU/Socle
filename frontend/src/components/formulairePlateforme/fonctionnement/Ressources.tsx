import { Autocomplete, Box, Button, Dialog, Grid, IconButton, MenuItem, Select, Stack, TextField, Typography } from "@mui/material";
import { useFormContext } from "react-hook-form";
import TauxTextFieldHint from "../../TauxTextFieldHint";
import EditIcon from '@mui/icons-material/Edit';
import { useContext, useState } from "react";
import { DictionnaireContext } from "../../../context/dictionnaireContext";
import { formatEmployeurLabel } from "../../../services/formatLabelService";
import type { SelectOption } from "../../../provider/DictionnaireProvider";
import LayoutLabelHintInput from "../../LayoutLabelHintInput";
import NombreTextFieldHint from "../../NombreTextFieldHint";
import type { SourceFinancement } from "../../../services/plateformeService";

function DialogModifDetailTutelle() {
    const [open, setOpen] = useState(false);
    const { getValues, setValue, watch } = useFormContext();
    const contribution_tutelle_detail = watch("contribution_tutelle_detail") || [];
    const dictionnaireContext = useContext(DictionnaireContext);
    const tauxTotaleTutelle = contribution_tutelle_detail.reduce((acc: number, source: SourceFinancement) => acc + (source.taux || 0), 0);
    const estTauxTutelleValide = tauxTotaleTutelle === 100;
    const estAucunTauxA0 = !contribution_tutelle_detail.some((source: SourceFinancement) => source.taux === 0);
    const tutelleNonRenseignee = contribution_tutelle_detail.some((source: SourceFinancement) => source.financeur_id === null);
    return (
        <>
            <IconButton size="small" sx={{ width: "fit-content", mt: 1 }} onClick={() => setOpen(true)}>
                <EditIcon />
            </IconButton>
            <Dialog open={open} onClose={() => setOpen(false)}>
                <Box sx={{ p: 3, display: "flex", flexDirection: "column", gap: 2 }}>
                    <Typography variant="h6" component="h2">
                        Détail de la contribution des tutelles
                    </Typography>
                    <Grid container spacing={2} sx={{ mb: 4 }}>
                        <Grid size={{ xs: 6 }}>
                            <Typography variant="body2">Combien il y a t'il de sources de financement pour cette equipement ?</Typography>
                        </Grid>
                        <Grid size={{ xs: 6 }}>
                            <Select fullWidth size="small" value={contribution_tutelle_detail.length} onChange={(e) => {
                                const value = Number(e.target.value);
                                const currentSources = getValues("contribution_tutelle_detail") || [];
                                if (value > currentSources.length) {
                                    const newSources = [...currentSources];
                                    for (let i = currentSources.length; i < value; i++) {
                                        newSources.push({ financeur_id: null, taux: 0 });
                                    }
                                    setValue("contribution_tutelle_detail", newSources);
                                } else if (value < currentSources.length) {
                                    setValue("contribution_tutelle_detail", currentSources.slice(0, value));
                                }

                            }}>
                                <MenuItem value={0}>0</MenuItem>
                                <MenuItem value={1}>1</MenuItem>
                                <MenuItem value={2}>2</MenuItem>
                                <MenuItem value={3}>3</MenuItem>
                                <MenuItem value={4}>4</MenuItem>
                                <MenuItem value={5}>5</MenuItem>
                            </Select>
                        </Grid>
                    </Grid>
                    {contribution_tutelle_detail.map((source: SourceFinancement, index: number) => (
                        <Stack key={index} direction="row" spacing={2}>
                            <Autocomplete<SelectOption, false, false, false>
                                options={dictionnaireContext.employeurOptions}
                                fullWidth
                                value={dictionnaireContext.employeurOptions.find((option) => option.value === source.financeur_id) ?? null}
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
                            <TextField
                                fullWidth
                                size="small"
                                label={`Taux de contribution`}
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

export default function Ressources() {
    const { getValues, watch, setValue } = useFormContext();
    watch(["contribution_tutelle_taux", "contribution_fond_public_taux", "contribution_fond_prive_taux", "contribution_credit_taux", "contribution_projet_recherche_taux", "contribution_structure_pia_taux"], () => {
        totalTaux();
    });
    const dictionnaireContext = useContext(DictionnaireContext);
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
                                <DialogModifDetailTutelle />
                            </Stack>
                            {detailTutelle.length > 0 ? detailTutelle.map((detail: SourceFinancement, index: number) => (
                                <Typography key={index} variant="body2" component="p">
                                    {formatEmployeurLabel(dictionnaireContext.employeur.find((tutelle) => tutelle.id === detail.financeur_id))}: {detail.taux}%
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
                <LayoutLabelHintInput label="Somme des pourcentages" helperText="" >
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
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 4 }}>
                Information Instrument National
            </Typography>
            <NombreTextFieldHint slotProps={{
                input: {
                    endAdornment: 'k€'
                }
            }} labelTF="Coût" label="Dotation de fonctionnement de la part de l’INSU à titre d’Instrument National" name="dotation_instrument_national" helperText="" />
        </Box>

    );
}