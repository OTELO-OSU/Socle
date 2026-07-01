import { Box, FormControl, Grid, InputLabel, MenuItem, Select, TextField, Typography } from "@mui/material";
import { useFormContext, useWatch } from "react-hook-form";
import { useContext, useEffect } from "react";
import { DictionnaireContext } from "../../../context/dictionnaireContext";
import type { PosteFutur } from "../../../services/plateformeService";
import type { EmploiType } from "../../../services/dictionnaireService";

const PERSONNELS_FIELD_NAME = "postes_futurs";

function ChampPosteFutur({ children, label }: { children: React.ReactNode; label: string }) {
    return (
        <Grid container spacing={2} sx={{ mb: 2 }}>
            <Grid size={{ xs: 3, md: 2 }} sx={{ display: "flex", alignItems: "center" }}>
                <Typography variant="subtitle1" component="h3">
                    {label}
                </Typography>
            </Grid>
            <Grid size={{ xs: 9, md: 4 }}>
                {children}
            </Grid>
        </Grid>
    )
}

function PersonnelFuturCarte({ poste, onChange }: { poste: PosteFutur; onChange?: (updatedPoste: PosteFutur) => void }) {
    const { employeurOptions, corps, bap, modalitesRecrutement } = useContext(DictionnaireContext);

    const emploiTypeDisponible: EmploiType[] = []
    poste.baps_ids.forEach((bapId) => {
        const et = bap.find((b) => b.id === bapId)?.emploiTypes;
        if (et) {
            emploiTypeDisponible.push(...et);
        }
    });


    return (
        <Box sx={{ border: "1px solid", borderColor: "grey.300", borderRadius: 1, p: 2 }}>
            <ChampPosteFutur label="Employeur">
                <Select
                    fullWidth
                    value={poste.employeur_id ?? ""}
                    onChange={(e) => onChange?.({ ...poste, employeur_id: e.target.value as number })}
                    displayEmpty
                >
                    {employeurOptions.map((option) => (
                        <MenuItem key={option.value} value={option.value}>
                            {option.label}
                        </MenuItem>
                    ))}
                </Select>
            </ChampPosteFutur>
            <ChampPosteFutur label="Modalité de recrutement">
                <Select
                    fullWidth
                    value={poste.modalite_recrutement_id ?? ""}
                    onChange={(e) => onChange?.({ ...poste, modalite_recrutement_id: e.target.value as number })}
                    displayEmpty
                >
                    {modalitesRecrutement.map((option) => (
                        <MenuItem key={option.id} value={option.id}>
                            {option.nom}
                        </MenuItem>
                    ))}
                </Select>
            </ChampPosteFutur>
            <ChampPosteFutur label="Corps">
                <Select
                    fullWidth
                    value={poste.corps_id ?? ""}
                    onChange={(e) => onChange?.({ ...poste, corps_id: e.target.value as number })}
                    displayEmpty
                >
                    {corps.map((option) => (
                        <MenuItem key={option.id} value={option.id}>
                            {option.nom}
                        </MenuItem>
                    ))}
                </Select>
            </ChampPosteFutur>
            <ChampPosteFutur label="BAP">
                <Select
                    multiple
                    fullWidth
                    value={poste.baps_ids ?? []}
                    onChange={(e) => {
                        const nextBapIds = (Array.isArray(e.target.value) ? e.target.value : [e.target.value]).map(Number);
                        onChange?.({ ...poste, baps_ids: nextBapIds, emploi_types_ids: [] });
                    }}
                    displayEmpty
                >
                    {bap.map((option) => (
                        <MenuItem key={option.id} value={option.id}>
                            {option.nom}
                        </MenuItem>
                    ))}
                </Select>
            </ChampPosteFutur>
            <ChampPosteFutur label="Emploi type">
                <Select
                    multiple
                    fullWidth
                    value={poste.emploi_types_ids ?? []}
                    onChange={(e) => {
                        const nextEmploiTypeIds = (Array.isArray(e.target.value) ? e.target.value : [e.target.value]).map(Number);
                        onChange?.({ ...poste, emploi_types_ids: nextEmploiTypeIds });
                    }}
                    displayEmpty
                    disabled={!poste.baps_ids?.length}
                >
                    {emploiTypeDisponible?.map((option) => (
                        <MenuItem key={option.id} value={option.id}>
                            {option.nom}
                        </MenuItem>
                    ))}
                </Select>
            </ChampPosteFutur>
            <ChampPosteFutur label="Quotité">
                <TextField
                    slotProps={{
                        htmlInput: {
                            min: 0,
                            step: 1,
                        },
                    }}
                    fullWidth
                    value={poste.quotite ?? null}
                    onChange={(e) => {
                        if (e.target.value === "") {
                            onChange?.({ ...poste, quotite: null });
                            return;
                        } else if (isNaN(Number(e.target.value)) || Number(e.target.value) < 0) {
                            return;
                        }
                        onChange?.({ ...poste, quotite: Number(e.target.value) });
                    }}
                />
            </ChampPosteFutur>
            <ChampPosteFutur label="Rang">
                <Grid container spacing={2} sx={{ alignItems: "center" }}>
                    <Grid size={{ xs: 5 }}>
                        <TextField
                            slotProps={{
                                htmlInput: {
                                    min: 0,
                                    step: 1,
                                },
                            }}
                            fullWidth
                            value={poste.rang_numerateur ?? null}
                            onChange={(e) => {
                                if (e.target.value === "") {
                                    onChange?.({ ...poste, rang_numerateur: null });
                                    return;
                                } else if (isNaN(Number(e.target.value)) || Number(e.target.value) < 0) {
                                    return;
                                }
                                onChange?.({ ...poste, rang_numerateur: Number(e.target.value) });
                            }}
                        />
                    </Grid>
                    <Grid size={{ xs: 2 }} sx={{ textAlign: "center" }}>
                        <Typography component={"span"} variant="h3">/</Typography>
                    </Grid>
                    <Grid size={{ xs: 5 }}>
                        <TextField
                            slotProps={{
                                htmlInput: {
                                    min: 0,
                                    step: 1,
                                },
                            }}
                            fullWidth
                            value={poste.rang_denominateur ?? null}
                            onChange={(e) => {
                                if (e.target.value === "") {
                                    onChange?.({ ...poste, rang_denominateur: null });
                                    return;
                                } else if (isNaN(Number(e.target.value)) || Number(e.target.value) < 0) {
                                    return;
                                }
                                onChange?.({ ...poste, rang_denominateur: Number(e.target.value) });
                            }}
                        />
                    </Grid>
                </Grid>
            </ChampPosteFutur>
        </Box>
    )
}

export function PersonnelFuturs() {
    const { control, getValues, setValue } = useFormContext();

    function handleNombrePostesChange(nombre: number) {
        const currentPostes = (getValues(PERSONNELS_FIELD_NAME) as PosteFutur[] | undefined) ?? [];
        if (nombre > currentPostes.length) {
            // Ajouter des postes vides
            const postesToAdd = Array.from({ length: nombre - currentPostes.length }, () => ({
                employeur_id: null,
                modalite_recrutement_id: null,
                corps_id: null,
                baps_ids: [],
                emploi_types_ids: [],
                quotite: null,
                rang_numerateur: null,
                rang_denominateur: null,
            }));
            setValue(PERSONNELS_FIELD_NAME, [...currentPostes, ...postesToAdd], { shouldDirty: true });
        } else if (nombre < currentPostes.length) {
            // Retirer les postes en trop
            setValue(PERSONNELS_FIELD_NAME, currentPostes.slice(0, nombre), { shouldDirty: true });
        }
    }

    // Remplissage initial
    useEffect(() => {
        const initialValue = getValues(PERSONNELS_FIELD_NAME) as PosteFutur[] | undefined;
        if (!Array.isArray(initialValue)) {
            setValue(PERSONNELS_FIELD_NAME, [], { shouldDirty: false, shouldValidate: false });
        }
    }, [getValues, setValue]);

    const postFutures = (useWatch({ control, name: PERSONNELS_FIELD_NAME }) as PosteFutur[] | undefined) ?? [];
    const nombrePostes = postFutures.length;

    return (
        <Box sx={{ p: 1 }}>
            <Typography variant="h6" component="h2">
                Postes Futurs
            </Typography>
            <Grid container spacing={2} sx={{ mt: 2, alignItems: "center" }}>
                <Grid size={{ xs: 6, md: 8 }}><Typography variant="subtitle1" component="h3" sx={{ fontWeight: "bold" }}>
                    Combien de postes votre unité a-t-elle fait remonter officiellement comme besoin RH dans DIALOG cet été dans le périmètre de votre plateforme ?
                </Typography>
                </Grid>
                <Grid size={"grow"}>
                    <FormControl fullWidth>
                        <InputLabel id="personnels-futurs-nombre-label">Nombre de postes</InputLabel>
                        <Select labelId="personnels-futurs-nombre-label" id="personnels-futurs-nombre" label="Nombre de postes" value={nombrePostes} onChange={(e) => handleNombrePostesChange(e.target.value as number)}>
                            <MenuItem value={0}>0</MenuItem>
                            <MenuItem value={1}>1</MenuItem>
                            <MenuItem value={2}>2</MenuItem>
                            <MenuItem value={3}>3</MenuItem>
                            <MenuItem value={4}>4</MenuItem>
                            <MenuItem value={5}>5</MenuItem>
                        </Select>
                    </FormControl>
                </Grid>
            </Grid>
            {postFutures.map((poste, index) => (
                <Box key={index} sx={{ mt: 2 }}>
                    <Typography variant="subtitle1" component="h3" sx={{ fontWeight: "bold" }}>
                        Poste n°{index + 1}
                    </Typography>
                    <PersonnelFuturCarte
                        poste={poste}
                        onChange={(updatedPoste) => {
                            const currentPostes = (getValues(PERSONNELS_FIELD_NAME) as PosteFutur[] | undefined) ?? [];
                            const nextPostes = currentPostes.map((currentPoste, currentIndex) =>
                                currentIndex === index ? updatedPoste : currentPoste
                            );
                            setValue(PERSONNELS_FIELD_NAME, nextPostes, { shouldDirty: true });
                        }}
                    />
                </Box>
            ))}

        </Box>
    )
}