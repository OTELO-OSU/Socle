import { Box, Button, Chip, Dialog, Divider, Grid, IconButton, Menu, MenuItem, Select, Stack, Typography } from "@mui/material";
import { useContext, useEffect, useState } from "react";
import { useForm, useFormContext, useWatch } from "react-hook-form";
import { RHFTextField } from "../../RHFTextField";
import TreeViewFinance from "../../TreeSelectFinance";
import TroisPoints from "@mui/icons-material/MoreVert";
import { DictionnaireContext } from "../../../context/dictionnaireContext";
import { RHFSelect } from "../../RHFTextSelect";

type InstrumentFuturProps = {
    id: number | string;
    nom: string | null;
    marque: string | null;
    modele: string | null;
    operation: number | null;
    anneeAchatEnvisage: number | null;
    cout: number | null;
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
    operation?: number | null;
    anneeAchatEnvisage?: string;
    cout?: string;
    sourceFinancement?: Array<{ financeurId: number | null; taux: number }>;
};

function InstrumentFuturCard({
    instrument,
    onEdit,
}: {
    instrument: InstrumentFuturProps;
    onEdit: (instrument: InstrumentFuturProps) => void;
}) {
    const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
    const open = Boolean(anchorEl);
    const dictionnaireContext = useContext(DictionnaireContext);


    const displayValue = (value: string | number | null | undefined, suffix = "") => {
        if (value === null || value === undefined || value === "") {
            return "Non renseigné";
        }
        return `${value}${suffix}`;
    };

    return (
        <Box
            sx={{
                border: "1px solid",
                borderColor: "divider",
                borderRadius: 3,
                p: 2.5,
                mb: 2,
                background: "linear-gradient(180deg, #ffffff 0%, #f9fbfd 100%)",
                boxShadow: "0 6px 18px rgba(15, 23, 42, 0.06)",
            }}
        >
            <Box sx={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", gap: 2 }}>
                <Box sx={{ flex: 1 }}>
                    <Typography variant="h6" sx={{ fontWeight: 700, mb: 1 }}>
                        {instrument.nom || "Equipement sans nom"}
                    </Typography>

                    <Grid container spacing={1.5} sx={{ mb: 1.5 }}>
                        <Grid size={{ xs: 12, sm: 6 }}>
                            <Typography variant="body2" color="text.secondary">Marque</Typography>
                            <Typography variant="body1">{displayValue(instrument.marque)}</Typography>
                        </Grid>
                        <Grid size={{ xs: 12, sm: 6 }}>
                            <Typography variant="body2" color="text.secondary">Modèle</Typography>
                            <Typography variant="body1">{displayValue(instrument.modele)}</Typography>
                        </Grid>
                        <Grid size={{ xs: 12, sm: 6 }}>
                            <Typography variant="body2" color="text.secondary">Année d'achat envisagée</Typography>
                            <Typography variant="body1">{displayValue(instrument.anneeAchatEnvisage)}</Typography>
                        </Grid>
                        <Grid size={{ xs: 12, sm: 6 }}>
                            <Typography variant="body2" color="text.secondary">Coût</Typography>
                            <Typography variant="body1">{displayValue(instrument.cout, " €")}</Typography>
                        </Grid>
                        <Grid size={{ xs: 12, sm: 6 }}>
                            <Typography variant="body2" color="text.secondary">Opération</Typography>
                            <Typography variant="body1">{displayValue(instrument.operation !== null ? dictionnaireContext.operations.find((o) => o.id === instrument.operation)?.nom : null)}</Typography>
                        </Grid>
                    </Grid>

                    <Divider sx={{ my: 1.5 }} />

                    <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                        Sources de financement
                    </Typography>
                    <Stack direction="row" spacing={1} useFlexGap sx={{ flexWrap: "wrap" }}>
                        {instrument.sourceFinancement.length === 0 && (
                            <Chip label="Aucune source" size="small" variant="outlined" />
                        )}
                        {instrument.sourceFinancement.map((source) => (
                            <Chip
                                key={source.financeurId}
                                size="small"
                                color="primary"
                                variant="outlined"
                                label={`${dictionnaireContext.sourcesFinancement.find((s) => s.id === source.financeurId)?.nom} • ${source.taux}%`}
                            />
                        ))}
                    </Stack>
                </Box>
                <Box>
                    <IconButton
                        size="small"
                        sx={{
                            border: "1px solid",
                            borderColor: "divider",
                            bgcolor: "background.paper",
                        }}
                        onClick={(e) => setAnchorEl(e.currentTarget)}
                    >
                        <TroisPoints />
                    </IconButton>
                    <Menu open={open} anchorEl={anchorEl} onClose={() => setAnchorEl(null)}>
                        <MenuItem onClick={() => {
                            onEdit(instrument);
                            setAnchorEl(null);
                        }}>
                            Modifier
                        </MenuItem>
                        <MenuItem>Supprimer</MenuItem>
                    </Menu>
                </Box>
            </Box>
        </Box>
    );
}

type DialogInstrumentFuturProps = {
    open: boolean;
    onClose: () => void;
    onSave: (instrument: InstrumentFuturProps) => void;
    instrumentToEdit?: InstrumentFuturProps;
};

function DialogInstrumentFutur({
    open,
    onClose,
    onSave,
    instrumentToEdit,
}: DialogInstrumentFuturProps) {
    const { control, handleSubmit, reset, setValue, getValues, trigger } = useForm<InstrumentFormValues>({
        shouldUnregister: false,
        defaultValues: {
            sourceFinancement: [],
        },
    });
    const [step, setStep] = useState(0);
    const dictionnaireContext = useContext(DictionnaireContext);

    const sourceFinancement = (useWatch({
        control,
        name: "sourceFinancement",
    }) as sourceFinancement[] | undefined) || [];

    const totalFinancement = sourceFinancement.reduce(
        (sum, source) => sum + (Number(source.taux) || 0),
        0,
    );

    const isTotalFinancementValid =
        Math.abs(totalFinancement - 100) < 0.0001;

    const hasInvalidTaux = sourceFinancement.some(
        (source) => (Number(source.taux) || 0) <= 0,
    );

    useEffect(() => {
        if (!open) {
            return;
        }

        if (!instrumentToEdit) {
            reset({
                nom: "",
                marque: "",
                modele: "",
                operation: null,
                anneeAchatEnvisage: "",
                cout: "",
                sourceFinancement: [],
            });
            return;
        }

        reset({
            nom: instrumentToEdit.nom ?? "",
            marque: instrumentToEdit.marque ?? "",
            modele: instrumentToEdit.modele ?? "",
            operation: instrumentToEdit.operation ?? null,
            anneeAchatEnvisage: instrumentToEdit.anneeAchatEnvisage?.toString() ?? "",
            cout: instrumentToEdit.cout?.toString() ?? "",
            sourceFinancement: instrumentToEdit.sourceFinancement.map((source) => ({
                financeurId: source.financeurId ?? null,
                taux: source.taux,
            })),
        });
    }, [open, instrumentToEdit, reset]);

    const onSubmit = (data: InstrumentFormValues) => {
        const total = (data.sourceFinancement || []).reduce(
            (sum, source) => sum + (Number(source.taux) || 0),
            0,
        );

        const hasInvalidRate = (data.sourceFinancement || []).some(
            (source) => (Number(source.taux) || 0) <= 0,
        );

        if (Math.abs(total - 100) >= 0.0001 || hasInvalidRate) {
            return;
        }

        const newInstrument: InstrumentFuturProps = {
            id: instrumentToEdit?.id ?? Date.now(),
            nom: data.nom ?? null,
            marque: data.marque ?? null,
            modele: data.modele ?? null,
            operation: data.operation ?? null,
            anneeAchatEnvisage: data.anneeAchatEnvisage ? parseInt(data.anneeAchatEnvisage) : null,
            cout: data.cout ? parseFloat(data.cout) : null,
            sourceFinancement: (data.sourceFinancement || []).map((source) => ({
                financeurId: source.financeurId ?? null,
                taux: Number(source.taux),
            })),
            futur: true,
        };
        onSave(newInstrument);
        onClose();
        reset();
        setStep(0);
    };

    const handleNextStep = async () => {
        const isStepOneValid = await trigger(["nom"]);

        if (isStepOneValid) {
            setStep(1);
        }
    };

    return (
        <Dialog
            open={open}
            onClose={onClose}
            fullWidth
            maxWidth="md"
            slotProps={{
                paper: {
                    sx: {
                        borderRadius: 3,
                    },
                },
            }}
        >

            <Box component="form" onSubmit={(e) => {
                e?.preventDefault()
                e?.stopPropagation();
                handleSubmit(onSubmit)();
            }} sx={{ p: 3 }}>
                <Typography variant="h6" gutterBottom>
                    {instrumentToEdit
                        ? "Modifier un équipement actuel"
                        : "Ajouter un équipement actuel"}
                </Typography>
                {step === 0 &&
                    <Box sx={{ border: "1px solid", borderColor: "divider", borderRadius: 2, p: 2 }}>
                        <Typography variant="subtitle1" sx={{ mb: 1, fontWeight: "bold" }}>
                            Général
                        </Typography>
                        <RHFTextField rules={{ required: "Le champs nom est requis" }} name="nom" control={control} label="Nom de l'équipement" fullWidth sx={{ mb: 2 }} />
                        <Stack direction="row" spacing={2} sx={{ mb: 2 }}>
                            <RHFTextField name="marque" control={control} label="Marque" fullWidth sx={{ mb: 2 }} />
                            <RHFTextField name="modele" control={control} label="Modèle" fullWidth sx={{ mb: 2 }} />
                        </Stack>
                        <RHFSelect name="operation" items={dictionnaireContext.operationsOptions} control={control} label="Type d'opération" fullWidth sx={{ mb: 2 }}>
                        </RHFSelect>
                        <RHFTextField name="anneeAchatEnvisage" control={control} label="Année d'achat envisagée" type="number" fullWidth sx={{ mb: 2 }} />
                        <RHFTextField name="cout" control={control} label="Coût (€)" type="number" fullWidth sx={{ mb: 2 }} />


                        <Stack direction="row" spacing={2} sx={{ justifyContent: "end" }}>
                            <Button variant="contained" color="secondary" onClick={onClose}>
                                Annuler
                            </Button>
                            <Button variant="contained" color="primary" onClick={handleNextStep}>
                                Suivant
                            </Button>

                        </Stack>
                    </Box>
                }
                {step === 1 && <Box sx={{ border: "1px solid", borderColor: "divider", borderRadius: 2, p: 2 }}>
                    <Typography variant="subtitle1" sx={{ mb: 1, fontWeight: "bold" }}>
                        Sources de financement
                    </Typography>
                    <Grid container spacing={2} sx={{ mb: 2 }}>
                        <Grid size={{ xs: 6 }}>
                            <Typography variant="body2">Combien il y a t'il de sources de financement pour cette equipement ?</Typography>
                        </Grid>
                        <Grid size={{ xs: 6 }}>
                            <Select fullWidth size="small" value={sourceFinancement.length} onChange={(e) => {
                                const value = Number(e.target.value);
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
                            <RHFTextField
                                name={`sourceFinancement.${index}.taux`}
                                control={control}
                                label="Taux (%)"
                                type="number"
                                fullWidth
                                sx={{ mb: 2 }}
                                rules={{
                                    required: "Le taux est requis",
                                    validate: (value) =>
                                        Number(value) > 0 || "Le taux doit etre strictement superieur a 0%",
                                }}
                                slotProps={{
                                    htmlInput: {
                                        min: 0.01,
                                        step: 0.01,
                                    },
                                }}
                            />
                        </Stack>
                    ))}

                    <Typography
                        variant="body2"
                        sx={{
                            mt: 1,
                            color: isTotalFinancementValid
                                ? "success.main"
                                : "error.main",
                            fontWeight: 600,
                        }}
                    >
                        Total financement: {totalFinancement}%
                        {!isTotalFinancementValid && " (la somme doit etre egale a 100%)"}
                    </Typography>

                    {hasInvalidTaux && (
                        <Typography variant="body2" color="error.main" sx={{ mt: 0.5, fontWeight: 600 }}>
                            Chaque source doit avoir un taux strictement superieur a 0%.
                        </Typography>
                    )}

                    <Stack direction="row" spacing={2} sx={{ mt: 2, justifyContent: "end" }}>
                        <Button variant="contained" color="secondary" onClick={() => setStep(0)}>
                            Retour
                        </Button>
                        <Button
                            variant="contained"
                            color="primary"
                            type="submit"
                            disabled={!isTotalFinancementValid || hasInvalidTaux}
                        >
                            {instrumentToEdit ? "Enregistrer" : "Ajouter"}
                        </Button>
                    </Stack>
                </Box>
                }
            </Box>
        </Dialog >
    )
}

export function EquipementFuturs() {
    const { control, getValues, setValue } = useFormContext();
    const [openCreateDialog, setOpenCreateDialog] = useState(false);
    const [instrumentToEdit, setInstrumentToEdit] = useState<InstrumentFuturProps | undefined>(undefined);
    useEffect(() => {
        const equipements = getValues("equipements") as InstrumentFuturProps[] | undefined;
        if (!Array.isArray(equipements)) {
            setValue("equipements", []);
        }
    }, [getValues, setValue]);

    const equipements = (useWatch({
        control,
        name: "equipements",
    }) as InstrumentFuturProps[] | undefined) || [];
    const futurs = equipements?.filter(e => e.futur) || [];
    return (
        <>
            <Stack direction="row" spacing={2} sx={{ mb: 2, alignItems: "center", justifyContent: "space-between" }}>
                <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 2 }}>
                    Equipements futurs
                </Typography>
                <Button size="small" variant="contained" color="primary" onClick={() => setOpenCreateDialog(true)}>
                    Ajouter un équipement
                </Button>
            </Stack>

            {openCreateDialog && (
                <DialogInstrumentFutur
                    open
                    onClose={() => setOpenCreateDialog(false)}
                    onSave={(instrument) => {
                        const equipements = getValues("equipements") as InstrumentFuturProps[] | undefined;
                        const newEquipements = [...(equipements || []), instrument];
                        setValue("equipements", newEquipements);
                    }}
                />
            )}

            {instrumentToEdit && (
                <DialogInstrumentFutur
                    open
                    instrumentToEdit={instrumentToEdit}
                    onClose={() => setInstrumentToEdit(undefined)}
                    onSave={(updatedInstrument) => {
                        const equipements = getValues("equipements") as InstrumentFuturProps[] | undefined;
                        const newEquipements = (equipements || []).map((instrument) =>
                            instrument.id === updatedInstrument.id
                                ? updatedInstrument
                                : instrument,
                        );
                        setValue("equipements", newEquipements);
                        setInstrumentToEdit(undefined);
                    }}
                />
            )}

            {futurs.length === 0 && (
                <Typography variant="body1" sx={{ mt: 1 }}>
                    Aucune information sur les équipements futurs de la plateforme.
                </Typography>
            )}
            {futurs.map((instrument) => (
                <InstrumentFuturCard
                    key={instrument.id}
                    instrument={instrument}
                    onEdit={(instrumentToUpdate) => setInstrumentToEdit(instrumentToUpdate)}
                />
            ))}
        </>
    )
}