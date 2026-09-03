import { Box, FormControl, FormControlLabel, FormLabel, Grid, MenuItem, Radio, RadioGroup, Select, Stack, TextField, Typography } from "@mui/material";
import { useContext, useEffect } from "react";
import { useFormContext, useWatch } from "react-hook-form";
import { DictionnaireContext } from "../../../context/dictionnaireContext";
import type { Instrument } from "../../../services/plateformeService";

function InstrumentActuelMaintenanceCard({
    instrument,
    instrumentList,
    onEdit,
}: {
    instrument: Instrument;
    instrumentList: Instrument[];
    onEdit: (instrument: Instrument) => void;
}) {
    const dictionnaireContext = useContext(DictionnaireContext);
    const selectedMutualisation = instrument.mutualise_autre_instrument
        ? "mutualiseAutreInstrument"
        : instrument.mutualise_umr_osu
            ? "mutualiseUmrOsu"
            : instrument.mutualise_site
                ? "mutualiseSite"
                : "";

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
            <Typography variant="subtitle2" color="primary" sx={{ mb: 1 }}>
                {instrument.nom}
            </Typography>
            <Box sx={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", gap: 2 }}>
                <Box sx={{ flex: 1 }}>
                    <Typography variant="h6" sx={{ fontWeight: 700, mb: 1 }}>
                        {instrument.nom || "Equipement sans nom"}
                    </Typography>

                    <Grid container spacing={1.5} sx={{ mb: 1.5, alignItems: "end" }}>
                        <Grid size={{ xs: "grow" }}>
                            <FormControl>
                                <FormLabel id={`${instrument.id}-label`}>Quel type de contrat de maintenance avez vous sur cette instrument ?</FormLabel>
                            </FormControl>
                        </Grid>
                        <Grid size={{ xs: "grow" }}>
                            <FormControl>
                                <FormLabel id={`${instrument.id}-commentaire-nature-contrat`}>Commentaire si besoin de préciser la nature du contrat</FormLabel>
                            </FormControl>
                        </Grid>
                        <Grid size={{ xs: "grow" }}>
                            <FormControl>
                                <FormLabel id={`${instrument.id}-fournisseur-contrat`}>Avec quel fournisseur / entreprise ?</FormLabel>
                            </FormControl>
                        </Grid>
                        <Grid size={{ xs: "grow" }}>
                            <FormControl>
                                <FormLabel id={`${instrument.id}-label`}>Contrat mutualisé avec d’autres instruments ?</FormLabel>
                            </FormControl>
                        </Grid>
                        <Grid size={{ xs: "grow" }}>
                            <FormControl>
                                <FormLabel id={`${instrument.id}-cout-maintenance`}>Coût K€/an</FormLabel>
                            </FormControl>
                        </Grid>
                    </Grid>
                    <Grid container spacing={1.5} sx={{ mb: 1.5, alignItems: "stretch" }}>
                        <Grid size={{ xs: "grow" }} sx={{ display: "flex" }}>
                            <FormControl sx={{ height: "100%", width: "100%" }}>
                                <RadioGroup
                                    value={instrument.contrat_maintenance_id ?? ""}
                                    onChange={(event) => onEdit({ ...instrument, contrat_maintenance_id: Number(event.target.value) })}
                                    aria-labelledby={`${instrument.id}-label`}
                                    name="radio-buttons-group"
                                >
                                    {dictionnaireContext.typeContratMaintenance.map((type) => (
                                        <FormControlLabel
                                            value={type.id}
                                            control={
                                                <Radio
                                                    onClick={(event) => {
                                                        if (instrument.contrat_maintenance_id === type.id) {
                                                            event.preventDefault();
                                                            onEdit({ ...instrument, contrat_maintenance_id: null });
                                                        }
                                                    }}
                                                />
                                            }
                                            label={type.nom}
                                            key={type.id}
                                        />
                                    ))}
                                </RadioGroup>
                            </FormControl>
                        </Grid>
                        <Grid size={{ xs: "grow" }} sx={{ display: "flex" }}>
                            <FormControl sx={{ height: "100%", width: "100%" }}>
                                <TextField
                                    multiline
                                    value={instrument.commentaire_nature_contrat}
                                    onChange={(event) => onEdit({ ...instrument, commentaire_nature_contrat: event.target.value })}
                                    placeholder="Commentaire"
                                    sx={{ height: "100%", "& .MuiInputBase-root": { height: "100%", alignItems: "flex-start" } }}
                                />
                            </FormControl>
                        </Grid>
                        <Grid size={{ xs: "grow" }} sx={{ display: "flex" }}>
                            <FormControl sx={{ height: "100%", width: "100%" }}>
                                <TextField
                                    multiline
                                    value={instrument.fournisseur_contrat}
                                    onChange={(event) => onEdit({ ...instrument, fournisseur_contrat: event.target.value })}
                                    placeholder="fournisseur / entreprise"
                                    sx={{ height: "100%", "& .MuiInputBase-root": { height: "100%", alignItems: "flex-start" } }}
                                />
                            </FormControl>
                        </Grid>
                        <Grid size={{ xs: "grow" }} sx={{ display: "flex" }}>
                            <FormControl sx={{ height: "100%", width: "100%" }}>
                                <RadioGroup
                                    value={selectedMutualisation}

                                    onChange={(event) => {
                                        const selectedValue = event.target.value;
                                        onEdit({
                                            ...instrument,
                                            mutualise_autre_instrument: selectedValue === "mutualiseAutreInstrument",
                                            mutualise_umr_osu: selectedValue === "mutualiseUmrOsu",
                                            mutualise_site: selectedValue === "mutualiseSite",
                                            mutualise_autre_instrument_ids: [],
                                        });
                                    }}
                                    aria-labelledby={`${instrument.id}-label`}
                                    name="radio-buttons-group"
                                >
                                    <FormControlLabel
                                        value="mutualiseAutreInstrument"
                                        control={
                                            <Radio
                                                onClick={(event) => {
                                                    if (selectedMutualisation === "mutualiseAutreInstrument") {
                                                        event.preventDefault();
                                                        onEdit({
                                                            ...instrument,
                                                            mutualise_autre_instrument: false,
                                                            mutualise_umr_osu: false,
                                                            mutualise_site: false,
                                                            mutualise_autre_instrument_ids: [],
                                                        });
                                                    }
                                                }}
                                            />
                                        }
                                        label="Mutualisé avec d'autres instruments de la plateforme"
                                    />
                                    <FormControlLabel
                                        value="mutualiseUmrOsu"
                                        control={
                                            <Radio
                                                onClick={(event) => {
                                                    if (selectedMutualisation === "mutualiseUmrOsu") {
                                                        event.preventDefault();
                                                        onEdit({
                                                            ...instrument,
                                                            mutualise_autre_instrument: false,
                                                            mutualise_umr_osu: false,
                                                            mutualise_site: false,
                                                            mutualise_autre_instrument_ids: [],
                                                        });
                                                    }
                                                }}
                                            />
                                        }
                                        label="Mutualisé avec l'UMR/OSU"
                                    />
                                    <FormControlLabel
                                        value="mutualiseSite"
                                        control={
                                            <Radio
                                                onClick={(event) => {
                                                    if (selectedMutualisation === "mutualiseSite") {
                                                        event.preventDefault();
                                                        onEdit({
                                                            ...instrument,
                                                            mutualise_autre_instrument: false,
                                                            mutualise_umr_osu: false,
                                                            mutualise_site: false,
                                                            mutualise_autre_instrument_ids: [],
                                                        });
                                                    }
                                                }}
                                            />
                                        }
                                        label="Mutualisé à l'échelle du site (Université)"
                                    />
                                    <Select
                                        sx={{ mt: 2 }}
                                        value={instrument.mutualise_autre_instrument_ids || []}
                                        onChange={(event) => {
                                            const selectedValue = event.target.value;
                                            const selectedIds = Array.isArray(selectedValue)
                                                ? selectedValue.map((value) => Number(value)).filter((value) => Number.isFinite(value))
                                                : [Number(selectedValue)].filter((value) => Number.isFinite(value));

                                            onEdit({ ...instrument, mutualise_autre_instrument_ids: selectedIds });
                                        }}
                                        multiple
                                        displayEmpty
                                        disabled={!instrument.mutualise_autre_instrument}
                                    >
                                        {instrumentList.map((item) => (
                                            <MenuItem key={item.id} value={item.id}>
                                                {item.nom}
                                            </MenuItem>
                                        ))}
                                    </Select>
                                </RadioGroup>
                            </FormControl>
                        </Grid>
                        <Grid size={{ xs: "grow" }} sx={{ display: "flex" }}>
                            <FormControl sx={{ height: "100%", width: "100%" }}>
                                <TextField
                                    multiline
                                    type="number"
                                    value={instrument.cout_maintenance}
                                    onChange={(event) => {
                                        let v = event.target.value;
                                        v = v.replace(/[^0-9.]/g, "");
                                        v = v.replace(/(\..*)\./g, "$1");

                                        onEdit({ ...instrument, cout_maintenance: event.target.value ? Number(v) : null });
                                    }}
                                    placeholder="Coût K€/an"
                                    sx={{ height: "100%", "& .MuiInputBase-root": { height: "100%", alignItems: "flex-start" } }}
                                />
                            </FormControl>
                        </Grid>
                    </Grid>
                </Box>
            </Box>
        </Box>
    );
}

export function EquipementActuelsMaintenance() {
    const { control, getValues, setValue } = useFormContext();
    useEffect(() => {
        const equipements = getValues("instruments") as Instrument[] | undefined;
        if (!Array.isArray(equipements)) {
            setValue("instruments", []);
        }
    }, [getValues, setValue]);

    const equipements = (useWatch({
        control,
        name: "instruments",
    }) as Instrument[] | undefined) || [];
    const actuels = equipements?.filter(e => !e.futur) || [];
    return (
        <>
            <Stack direction="row" spacing={2} sx={{ mb: 2, alignItems: "center", justifyContent: "space-between" }}>
                <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 2 }}>
                    Equipements actuels
                </Typography>
            </Stack>

            {actuels.length === 0 && (
                <Typography variant="body1" sx={{ mt: 1 }}>
                    Aucune information sur les équipements actuels de la plateforme.
                </Typography>
            )}
            {actuels.map((instrument) => (
                <InstrumentActuelMaintenanceCard
                    key={instrument.id}
                    instrument={instrument}
                    instrumentList={equipements}
                    onEdit={(instrumentToUpdate) => setValue("instruments", equipements.map(e => e.id === instrument.id ? instrumentToUpdate : e))}
                />
            ))}
        </>
    )
}