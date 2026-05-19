import { Box, FormControl, FormControlLabel, FormLabel, Grid, MenuItem, Radio, RadioGroup, Select, Stack, TextField, Typography } from "@mui/material";
import { useContext, useEffect } from "react";
import { useFormContext, useWatch } from "react-hook-form";
import { DictionnaireContext } from "../../../context/dictionnaireContext";

type InstrumentActuelMaintenanceProps = {
    id: number | string;
    nom: string;
    contratMaintenanceId: number | null;
    commentaireNatureContrat: string | null;
    fournisseurContrat: string | null;
    mutualiseAutreInstrument: boolean | null;
    mutualiseAutreInstrumentDetail: number[];
    mutualiseUmrOsu: boolean | null;
    mutualiseSite: boolean | null;
    coutMaintenance: number | null;
    futur: boolean;
};

function InstrumentActuelMaintenanceCard({
    instrument,
    instrumentList,
    onEdit,
}: {
    instrument: InstrumentActuelMaintenanceProps;
    instrumentList: InstrumentActuelMaintenanceProps[];
    onEdit: (instrument: InstrumentActuelMaintenanceProps) => void;
}) {
    const dictionnaireContext = useContext(DictionnaireContext);

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
                    <Grid container spacing={1.5} sx={{ mb: 1.5, alignItems: "center" }}>
                        <Grid size={{ xs: "grow" }}>
                            <FormControl>
                                <RadioGroup
                                    value={instrument.contratMaintenanceId ?? ""}
                                    onChange={(event) => onEdit({ ...instrument, contratMaintenanceId: Number(event.target.value) })}
                                    aria-labelledby={`${instrument.id}-label`}
                                    name="radio-buttons-group"
                                >
                                    {dictionnaireContext.typeContratMaintenance.map((type) => (
                                        <FormControlLabel value={type.id} control={<Radio />} label={type.nom} key={type.id} />
                                    ))}
                                </RadioGroup>
                            </FormControl>
                        </Grid>
                        <Grid size={{ xs: "grow" }}>
                            <FormControl>
                                <TextField
                                    multiline
                                    rows={4}
                                    value={instrument.commentaireNatureContrat}
                                    onChange={(event) => onEdit({ ...instrument, commentaireNatureContrat: event.target.value })}
                                    placeholder="Commentaire"
                                />
                            </FormControl>
                        </Grid>
                        <Grid size={{ xs: "grow" }}>
                            <FormControl>
                                <TextField
                                    multiline
                                    rows={4}
                                    value={instrument.fournisseurContrat}
                                    onChange={(event) => onEdit({ ...instrument, fournisseurContrat: event.target.value })}
                                    placeholder="fournisseur / entreprise"
                                />
                            </FormControl>
                        </Grid>
                        <Grid size={{ xs: "grow" }}>
                            <FormControl>
                                <RadioGroup
                                    value={instrument.mutualiseAutreInstrument ? "mutualiseAutreInstrument" : instrument.mutualiseUmrOsu ? "mutualiseUmrOsu" : instrument.mutualiseSite ? "mutualiseSite" : ""}

                                    onChange={(event) => {
                                        const selectedValue = event.target.value;
                                        onEdit({
                                            ...instrument,
                                            mutualiseAutreInstrument: selectedValue === "mutualiseAutreInstrument",
                                            mutualiseUmrOsu: selectedValue === "mutualiseUmrOsu",
                                            mutualiseSite: selectedValue === "mutualiseSite",
                                            mutualiseAutreInstrumentDetail: [],
                                        });
                                    }}
                                    aria-labelledby={`${instrument.id}-label`}
                                    name="radio-buttons-group"
                                >
                                    <FormControlLabel value="mutualiseAutreInstrument" control={<Radio />} label="Mutualisé avec d'autres instruments de la plateforme" />
                                    <FormControlLabel value="mutualiseUmrOsu" control={<Radio />} label="Mutualisé avec l'UMR/OSU" />
                                    <FormControlLabel value="mutualiseSite" control={<Radio />} label="Mutualisé à l'échelle du site (Université)" />
                                    <Select
                                        sx={{ mt: 2 }}
                                        value={instrument.mutualiseAutreInstrumentDetail || []}
                                        onChange={(event) => {
                                            const selectedValue = event.target.value;
                                            const selectedIds = Array.isArray(selectedValue)
                                                ? selectedValue.map((value) => Number(value)).filter((value) => Number.isFinite(value))
                                                : [Number(selectedValue)].filter((value) => Number.isFinite(value));

                                            onEdit({ ...instrument, mutualiseAutreInstrumentDetail: selectedIds });
                                        }}
                                        multiple
                                        displayEmpty
                                        disabled={!instrument.mutualiseAutreInstrument}
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
                        <Grid size={{ xs: "grow" }}>
                            <FormControl>
                                <TextField
                                    multiline
                                    type="number"
                                    rows={4}
                                    value={instrument.coutMaintenance}
                                    onChange={(event) => onEdit({ ...instrument, coutMaintenance: event.target.value ? Number(event.target.value) : null })}
                                    placeholder="Coût K€/an"
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
        const equipements = getValues("equipements") as InstrumentActuelMaintenanceProps[] | undefined;
        if (!Array.isArray(equipements)) {
            setValue("equipements", []);
        }
    }, [getValues, setValue]);

    const equipements = (useWatch({
        control,
        name: "equipements",
    }) as InstrumentActuelMaintenanceProps[] | undefined) || [];
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
                    onEdit={(instrumentToUpdate) => setValue("equipements", equipements.map(e => e.id === instrument.id ? instrumentToUpdate : e))}
                />
            ))}
        </>
    )
}