import { FormControl, FormControlLabel, MenuItem, Radio, RadioGroup, Stack, TextField } from "@mui/material";
import { useContext, useMemo } from "react";
import { DictionnaireContext } from "../context/dictionnaireContext";
import {
    getCompatibilites,
    getEmploiTypesOptions,
    getGradeOptions,
    isCDD,
    isEpicEmployeur,
    resetOnCorpsChange,
    resetOnDoctorantChange,
    resetOnEmployeurChange,
    resetOnTypeContratChange,
    syncEmploiTypesWithBaps,
} from "../services/agentFormLogicService";
import LayoutLabelHintInput from "./LayoutLabelHintInput";
import type { Agent } from "../services/agentService";
import { formatBapLabel, formatCnuLabel, formatCorpsLabel, formatEmployeurLabel, formatGradeLabel, formatSectionCnrsLabel, formatUniteLabel, formatUniversiteLabel } from "../services/formatLabelService";

type AgentAdvancedFieldsProps = {
    value: Agent;
    onChange: (next: Agent) => void;
    disabled?: boolean;
};

function toNumberList(value: string | number | Array<string | number>) {
    if (Array.isArray(value)) {
        return value.map((item) => Number(item));
    }

    if (value === "" || value === null || value === undefined) {
        return [];
    }

    return [Number(value)];
}

export function AgentAdvancedFields({ value, onChange, disabled = false }: AgentAdvancedFieldsProps) {
    const dictionnaire = useContext(DictionnaireContext);

    const estCDD = isCDD(value.type_contrat_id ?? null, dictionnaire.typeContrat);
    const epic = isEpicEmployeur(value.employeur_id ?? null, dictionnaire.employeur);

    const availableGrades = useMemo(
        () => getGradeOptions(value.corps_id ?? null, dictionnaire.corps),
        [dictionnaire.corps, value.corps_id],
    );

    const { compatibleBap, compatibleCnu, compatibleSection } = useMemo(
        () => getCompatibilites(value.corps_id ?? null, dictionnaire.corps),
        [dictionnaire.corps, value.corps_id],
    );

    const emploiTypesOptions = useMemo(
        () => getEmploiTypesOptions(value.baps_ids ?? [], dictionnaire.bap),
        [dictionnaire.bap, value.baps_ids],
    );

    return (
        <>
            <Stack direction="row" spacing={2}>
                <TextField
                    label="Nom"
                    value={value.nom}
                    onChange={(event) =>
                        onChange({
                            ...value,
                            nom: event.target.value,
                        })
                    }
                    disabled={disabled}
                    fullWidth
                />
                <TextField
                    label="Prenom"
                    value={value.prenom}
                    onChange={(event) =>
                        onChange({
                            ...value,
                            prenom: event.target.value,
                        })
                    }
                    disabled={disabled}
                    fullWidth
                />
            </Stack>

            <TextField
                label="Email"
                type="email"
                value={value.email}
                onChange={(event) =>
                    onChange({
                        ...value,
                        email: event.target.value,
                    })
                }
                disabled={disabled}
                fullWidth
            />

            <TextField
                select
                label="Employeur"
                value={value.employeur_id ?? ""}
                onChange={(event) => {
                    const nextEmployeurId = event.target.value === "" ? null : Number(event.target.value);
                    onChange(resetOnEmployeurChange(value, nextEmployeurId, dictionnaire.employeur));
                }}
                disabled={disabled}
                fullWidth
            >
                {dictionnaire.employeur.map((employeur) => (
                    <MenuItem key={employeur.id} value={employeur.id}>
                        {formatEmployeurLabel(employeur)}
                    </MenuItem>
                ))}
            </TextField>

            <TextField
                select
                label="Unite"
                value={value.unite_id ?? ""}
                onChange={(event) => {
                    const nextUniteId = event.target.value === "" ? null : Number(event.target.value);
                    onChange({ ...value, unite_id: nextUniteId });
                }}
                disabled={disabled}
                fullWidth
            >
                {dictionnaire.unite.map((unite) => (
                    <MenuItem key={unite.id} value={unite.id}>
                        {formatUniteLabel(unite)}
                    </MenuItem>
                ))}
            </TextField>

            <TextField
                select
                label="Type de contrat"
                value={value.type_contrat_id ?? ""}
                onChange={(event) => {
                    const nextTypeContratId = event.target.value === "" ? null : Number(event.target.value);
                    onChange(resetOnTypeContratChange(value, nextTypeContratId));
                }}
                disabled={disabled}
                fullWidth
            >
                {dictionnaire.typeContrat.map((typeContrat) => (
                    <MenuItem key={typeContrat.id} value={typeContrat.id}>
                        {typeContrat.nom}
                    </MenuItem>
                ))}
            </TextField>

            {value.type_contrat_id && estCDD && (
                <FormControl disabled={disabled}>
                    <LayoutLabelHintInput label="Est-ce que l'agent est doctorant ?" helperText="">
                        <RadioGroup
                            row
                            value={value.doctorant === null ? "" : value.doctorant ? "true" : "false"}
                            onChange={(event) => {
                                const raw = event.target.value;
                                const nextDoctorant = raw === "" ? null : raw === "true";
                                onChange(resetOnDoctorantChange(value, nextDoctorant));
                            }}
                        >
                            <FormControlLabel value="true" control={<Radio />} label="Oui" />
                            <FormControlLabel value="false" control={<Radio />} label="Non" />
                        </RadioGroup>
                    </LayoutLabelHintInput>
                </FormControl>
            )}

            {value.type_contrat_id && estCDD && value.doctorant === true && (
                <TextField
                    select
                    label="Universite de rattachement"
                    value={value.universite_rattachement_id ?? ""}
                    onChange={(event) => {
                        const nextUniversiteId = event.target.value === "" ? null : Number(event.target.value);
                        onChange({ ...value, universite_rattachement_id: nextUniversiteId });
                    }}
                    disabled={disabled}
                    fullWidth
                >
                    {dictionnaire.universite.map((universite) => (
                        <MenuItem key={universite.id} value={universite.id}>
                            {formatUniversiteLabel(universite)}
                        </MenuItem>
                    ))}
                </TextField>
            )}

            {value.type_contrat_id && (!estCDD || value.doctorant === false) && (
                <Stack direction="row" spacing={2}>
                    <TextField
                        select
                        label="Corps"
                        value={value.corps_id ?? ""}
                        onChange={(event) => {
                            const nextCorpsId = event.target.value === "" ? null : Number(event.target.value);
                            onChange(resetOnCorpsChange(value, nextCorpsId));
                        }}
                        disabled={disabled}
                        fullWidth
                    >
                        {dictionnaire.corps.map((corps) => (
                            <MenuItem key={corps.id} value={corps.id}>
                                {formatCorpsLabel(corps)}
                            </MenuItem>
                        ))}
                    </TextField>

                    <TextField
                        select
                        label="Grade"
                        value={value.grade_id ?? ""}
                        onChange={(event) => {
                            const nextGradeId = event.target.value === "" ? null : Number(event.target.value);
                            onChange({ ...value, grade_id: nextGradeId });
                        }}
                        disabled={disabled || !value.corps_id}
                        fullWidth
                    >
                        {availableGrades.map((grade) => (
                            <MenuItem key={grade.id} value={grade.id}>
                                {formatGradeLabel(grade)}
                            </MenuItem>
                        ))}
                    </TextField>
                </Stack>
            )}

            {value.type_contrat_id && compatibleBap && !epic && (
                <Stack direction="row" spacing={2}>
                    <TextField
                        select
                        slotProps={{
                            select: {
                                multiple: true,
                            },
                        }}
                        label="BAP"
                        value={value.baps_ids ?? []}
                        onChange={(event) => {
                            const nextBapsIds = toNumberList(event.target.value as unknown as Array<string | number>);
                            onChange(syncEmploiTypesWithBaps(value, nextBapsIds, dictionnaire.bap));
                        }}
                        disabled={disabled}
                        fullWidth
                    >
                        {dictionnaire.bap.map((bap) => (
                            <MenuItem key={bap.id} value={bap.id}>
                                {formatBapLabel(bap)}
                            </MenuItem>
                        ))}
                    </TextField>

                    <TextField
                        select
                        slotProps={{
                            select: {
                                multiple: true,
                            },
                        }}
                        label="Emploi Types"
                        value={value.emploi_types_ids ?? []}
                        onChange={(event) => {
                            const nextEmploiTypesIds = toNumberList(event.target.value as unknown as Array<string | number>);
                            onChange({ ...value, emploi_types_ids: nextEmploiTypesIds });
                        }}
                        disabled={disabled}
                        fullWidth
                    >
                        {emploiTypesOptions.map((emploiType) => (
                            <MenuItem key={emploiType.id} value={emploiType.id}>
                                {emploiType.nom}
                            </MenuItem>
                        ))}
                    </TextField>
                </Stack>
            )}

            {value.type_contrat_id && compatibleCnu && !epic && !estCDD && (
                <TextField
                    select
                    label="CNU"
                    value={value.cnu_id ?? ""}
                    onChange={(event) => {
                        const nextCnuId = event.target.value === "" ? null : Number(event.target.value);
                        onChange({ ...value, cnu_id: nextCnuId });
                    }}
                    disabled={disabled}
                    fullWidth
                >
                    {dictionnaire.cnu.map((item) => (
                        <MenuItem key={item.id} value={item.id}>
                            {formatCnuLabel(item)}
                        </MenuItem>
                    ))}
                </TextField>
            )}

            {value.type_contrat_id && compatibleSection && !epic && !estCDD && (
                <TextField
                    select
                    label="Section CNRS"
                    value={value.section_cnrs_id ?? ""}
                    onChange={(event) => {
                        const nextSectionCnrsId = event.target.value === "" ? null : Number(event.target.value);
                        onChange({ ...value, section_cnrs_id: nextSectionCnrsId });
                    }}
                    disabled={disabled}
                    fullWidth
                >
                    {dictionnaire.sectionCnrs.map((item) => (
                        <MenuItem key={item.id} value={item.id}>
                            {formatSectionCnrsLabel(item)}
                        </MenuItem>
                    ))}
                </TextField>
            )}
        </>
    );
}
