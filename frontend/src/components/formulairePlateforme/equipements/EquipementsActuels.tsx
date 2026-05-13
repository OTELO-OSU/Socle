import { Box, Button, Dialog, Grid, MenuItem, Select, Stack, Typography } from "@mui/material";
import { useEffect, useState } from "react";
import { useForm, useFormContext, useWatch } from "react-hook-form";
import { RHFTextField } from "../../RHFTextField";
import TreeViewFinance from "../../TreeSelectFinance";

type InstrumentActuelProps = {
    id: number | string;
    nom: string | null;
    marque: string | null;
    modele: string | null;
    anneeAcquisition: number | null;
    cout: number | null;
    valeurRemplacement: number | null;
    dureeUtilisation: number | null;
    sourceFinancement: sourceFinancement[];
    futur: boolean;
};

type sourceFinancement = {
    financeurId: number | null;
    taux: number;
}

type InstrumentFormValues = {
    nom?: string;
    marque?: string;
    modele?: string;
    anneeAcquisition?: string;
    cout?: string;
    valeurRemplacement?: string;
    dureeUtilisation?: string;
    sourceFinancement?: Array<{ financeurId: number | null; taux: number }>;
};

function InstrumentActuelCard({ instrument }: { instrument: InstrumentActuelProps }) {
    return (
        <Box sx={{ border: "1px solid #ccc", borderRadius: 2, p: 2, mb: 2 }}>
            <Typography variant="h6">{instrument.nom}</Typography>
            <Typography>Marque : {instrument.marque}</Typography>
            <Typography>Modèle : {instrument.modele}</Typography>
            <Typography>Année d'acquisition : {instrument.anneeAcquisition}</Typography>
            <Typography>Coût : {instrument.cout} €</Typography>
            <Typography>Valeur de remplacement : {instrument.valeurRemplacement} €</Typography>
            <Typography>Durée d'utilisation : {instrument.dureeUtilisation} ans</Typography>
            <Typography>Sources de financement :</Typography>
            <ul>
                {instrument.sourceFinancement.map((source) => (
                    <li key={source.financeurId ?? "none"}>Source ID: {source.financeurId}, Taux: {source.taux}%</li>
                ))}
            </ul>
        </Box>
    );
}

function DialogAjoutInstrumentActuel({ onAdd }: { onAdd: (instrument: InstrumentActuelProps) => void }) {
    const [open, setOpen] = useState(false);
    const { control, handleSubmit, reset, setValue, getValues } = useForm();
    const [step, setStep] = useState(0);

    const sourceFinancement = (useWatch({
        control,
        name: "sourceFinancement",
    }) as sourceFinancement[] | undefined) || [];

    const onSubmit = (data: InstrumentFormValues) => {
        const newInstrument: InstrumentActuelProps = {
            id: Date.now(), // Utiliser un ID temporaire basé sur le timestamp
            nom: data.nom ?? null,
            marque: data.marque ?? null,
            modele: data.modele ?? null,
            anneeAcquisition: data.anneeAcquisition ? parseInt(data.anneeAcquisition) : null,
            cout: data.cout ? parseFloat(data.cout) : null,
            valeurRemplacement: data.valeurRemplacement ? parseFloat(data.valeurRemplacement) : null,
            dureeUtilisation: data.dureeUtilisation ? parseInt(data.dureeUtilisation) : null,
            sourceFinancement: (data.sourceFinancement || []).map((source) => ({
                financeurId: source.financeurId ?? null,
                taux: source.taux,
            })),
            futur: false,
        };
        onAdd(newInstrument);
        setOpen(false);
        reset();
        setStep(0);
    }

    return (
        <>
            <Button size="small" variant="contained" color="primary" onClick={() => { setOpen(true) }}>
                Ajouter un équipement
            </Button>
            <Dialog open={open} onClose={() => setOpen(false)}>

                <Box component="form" onSubmit={(e) => {
                    e?.preventDefault()
                    e?.stopPropagation();
                    handleSubmit(onSubmit)();
                }} sx={{ p: 2 }}>
                    <Typography variant="h6" gutterBottom>
                        Ajouter un équipement actuel
                    </Typography>
                    {step === 0 &&
                        <>
                            <Typography variant="subtitle1" sx={{ mb: 1, fontWeight: "bold" }}>
                                Général
                            </Typography>
                            <RHFTextField rules={{ required: "Le champs nom est requis" }} name="nom" control={control} label="Nom de l'équipement" fullWidth sx={{ mb: 2 }} />
                            <Stack direction="row" spacing={2} sx={{ mb: 2 }}>
                                <RHFTextField name="marque" control={control} label="Marque" fullWidth sx={{ mb: 2 }} />
                                <RHFTextField name="modele" control={control} label="Modèle" fullWidth sx={{ mb: 2 }} />
                            </Stack>
                            <RHFTextField name="anneeAcquisition" control={control} label="Année d'acquisition" type="number" fullWidth sx={{ mb: 2 }} />
                            <Stack direction="row" spacing={2} sx={{ mb: 2 }}>
                                <RHFTextField name="cout" control={control} label="Coût (€)" type="number" fullWidth sx={{ mb: 2 }} />
                                <RHFTextField name="valeurRemplacement" control={control} label="Valeur de remplacement (€)" type="number" fullWidth sx={{ mb: 2 }} />
                            </Stack>


                            <RHFTextField name="dureeUtilisation" control={control} label="Durée d'utilisation (ans)" type="number" fullWidth sx={{ mb: 2 }} />
                            <Stack direction="row" spacing={2} sx={{ justifyContent: "end" }}>
                                <Button variant="contained" color="secondary" onClick={() => setOpen(false)}>
                                    Annuler
                                </Button>
                                <Button variant="contained" color="primary" onClick={() => setStep(1)}>
                                    Suivant
                                </Button>

                            </Stack>
                        </>
                    }
                    {step === 1 && <>
                        <Typography variant="subtitle1" sx={{ mb: 1, fontWeight: "bold" }}>
                            Sources de financement
                        </Typography>
                        <Grid container spacing={2} sx={{ mb: 2 }}>
                            <Grid size={{ xs: 6 }}>
                                <Typography variant="body2">Combien il y a t'il de sources de financement pour cette equipement ?</Typography>
                            </Grid>
                            <Grid size={{ xs: 6 }}>
                                <Select fullWidth size="small" defaultValue={0} onChange={(e) => {
                                    const value = e.target.value;
                                    const currentSources = getValues("sourceFinancement") || [];
                                    if (value > currentSources.length) {
                                        const newSources = [...currentSources];
                                        for (let i = currentSources.length; i < value; i++) {
                                            newSources.push({ financeurId: null, taux: 0 });
                                        }
                                        setValue("sourceFinancement", newSources);
                                    } else if (value < currentSources.length) {
                                        setValue("sourceFinancement", currentSources.slice(0, value));
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
                        {sourceFinancement.map((_, index) => (
                            <Stack key={index} direction="row" spacing={2} sx={{ mb: 2 }}>
                                <TreeViewFinance
                                    selectedId={sourceFinancement[index].financeurId ?? undefined}
                                    onSelectedIdChange={(id) => {
                                        const currentSources = getValues("sourceFinancement") || [];
                                        const updatedSources = [...currentSources];
                                        updatedSources[index] = {
                                            ...updatedSources[index],
                                            financeurId: id,
                                        };
                                        setValue("sourceFinancement", updatedSources);
                                    }}
                                />
                                <RHFTextField name={`sourceFinancement.${index}.taux`} control={control} label="Taux (%)" type="number" fullWidth sx={{ mb: 2 }} />
                            </Stack>
                        ))}
                        <Stack direction="row" spacing={2} sx={{ mt: 2, justifyContent: "end" }}>
                            <Button variant="contained" color="secondary" onClick={() => setStep(0)}>
                                Retour
                            </Button>
                            <Button variant="contained" color="primary" type="submit">
                                Ajouter
                            </Button>
                        </Stack>
                    </>
                    }
                </Box>
            </Dialog >
        </>
    )
}

export function EquipementActuels() {
    const { control, getValues, setValue } = useFormContext();
    useEffect(() => {
        const equipements = getValues("equipements") as InstrumentActuelProps[] | undefined;
        if (!Array.isArray(equipements)) {
            setValue("equipements", []);
        }
    }, [getValues, setValue]);

    const equipements = (useWatch({
        control,
        name: "equipements",
    }) as InstrumentActuelProps[] | undefined) || [];
    const actuels = equipements?.filter(e => !e.futur) || [];
    return (
        <>
            <Stack direction="row" spacing={2} sx={{ mb: 2, alignItems: "center", justifyContent: "space-between" }}>
                <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 2 }}>
                    Equipements actuels
                </Typography>
                <DialogAjoutInstrumentActuel onAdd={(instrument) => {
                    const equipements = getValues("equipements") as InstrumentActuelProps[] | undefined;
                    const newEquipements = [...(equipements || []), instrument];
                    setValue("equipements", newEquipements);
                }} />
            </Stack>

            {actuels.length === 0 && (
                <Typography variant="body1" sx={{ mt: 1 }}>
                    Aucune information sur les équipements actuels de la plateforme.
                </Typography>
            )}
            {actuels.map((instrument) => (
                <InstrumentActuelCard key={instrument.id} instrument={instrument} />
            ))}
        </>
    )
}