import { Box, Button, Chip, Dialog, Link, Stack, TextField, Typography } from "@mui/material";
import Account from '@mui/icons-material/AccountCircle';
import { FormProvider, useForm, useFormContext, useWatch } from "react-hook-form";
import { useContext, useEffect, useMemo, useState } from "react";
import { AgentAutocompleteField } from "../../AgentAutocompleteField";
import { type Agent, type Grade, type typeContrat, soumettreAgentAuDu } from "../../../services/agentService";
import { typePersonnelLabel } from "../../../services/formatLabelService";
import { RHFTextField } from "../../RHFTextField";
import { RHFSelect } from "../../RHFTextSelect";
import { DictionnaireContext } from "../../../context/dictionnaireContext";
import { SnackbarContext } from "../../../context/snackbarContext";
import RadioTextFieldHint from "../../RadioTextFieldHint";
import type { Cnu, Corps, SectionCnrs } from "../../../services/dictionnaireService";
import NombreTextFieldHint from "../../NombreTextFieldHint";

type PersonnelActuelFormValue = {
    utilisateur_id: number;
    utilisateur: {
        id: number;
        nom: string;
        prenom: string;
        email: string;
        corps: Corps | null;
        grade: Grade | null;
        type_contrat: typeContrat | null;
        doctorant: boolean | null;
        universite_rattachement: number | null;
        baps_ids: number[];
        emploi_types_ids: number[];
        section_cnrs: SectionCnrs | null;
        cnu: Cnu | null;
    }
    quotite: number;
    responsable_scientifique: boolean;
    responsable_technique: boolean;
    responsable_plateforme: boolean;
};

const PERSONNELS_FIELD_NAME = "personnels";

type DialogAjoutPersonnelProps = {
    onAdd: (personnel: PersonnelActuelFormValue) => void;
};

function DialogAjoutPersonnel({ onAdd }: DialogAjoutPersonnelProps) {
    const [open, setOpen] = useState(false);
    const [agent, setAgent] = useState<Agent | null>(null);
    const [quotite, setQuotite] = useState("");
    const [errorMessage, setErrorMessage] = useState<string | null>(null);

    const handleAgentChange = (value: Agent | null) => {
        setAgent(value);
        setErrorMessage(null);
    };

    const handleClickOpen = () => {
        setOpen(true);
    };

    const handleClose = () => {
        setOpen(false);
        setAgent(null);
        setQuotite("");
        setErrorMessage(null);
    };

    const handleAdd = () => {
        if (!agent) {
            setErrorMessage("Veuillez sélectionner un agent.");
            return;
        }

        console.log(agent);


        const quotiteNumber = Number(quotite);
        const isQuotiteInvalid = Number.isNaN(quotiteNumber) || quotiteNumber < 0 || quotiteNumber > 100;

        if (isQuotiteInvalid) {
            setErrorMessage("La quotité doit être un nombre entre 0 et 100.");
            return;
        }

        onAdd({
            utilisateur_id: agent.id,
            utilisateur: {
                id: agent.id,
                nom: agent.nom,
                prenom: agent.prenom,
                email: agent.email,
                corps: agent.corps ?? null,
                grade: agent.grade ?? null,
                type_contrat: agent.type_contrat ?? null,
                doctorant: agent.doctorant ?? null,
                universite_rattachement: agent.universite_rattachement ?? null,
                baps_ids: agent.baps_ids ?? [],
                emploi_types_ids: agent.emploi_types_ids ?? [],
                section_cnrs: agent.section_cnrs ?? null,
                cnu: agent.cnu ?? null,
            },
            quotite: quotiteNumber,
            responsable_scientifique: false,
            responsable_technique: false,
            responsable_plateforme: false,
        });

        handleClose();
    };

    return (
        <>
            <Button variant="contained" color="primary" size="small" onClick={handleClickOpen}>
                Ajouter un personnel
            </Button>
            <Dialog open={open} onClose={handleClose}>
                <Box sx={{ p: 2, display: "flex", flexDirection: "column", gap: 2 }}>
                    <Typography variant="h6" component="h2">
                        Ajouter un personnel
                    </Typography>
                    <AgentAutocompleteField label="Agent" value={agent} onChange={handleAgentChange} />
                    <TextField
                        label="Quotité de travail (%)"
                        placeholder="50"
                        type="number"
                        value={quotite}
                        onChange={(event) => {
                            setQuotite(event.target.value);
                            setErrorMessage(null);
                        }}
                        slotProps={{ htmlInput: { min: 0, max: 100 } }}
                    />
                    {errorMessage && (
                        <Typography color="error" variant="body2">
                            {errorMessage}
                        </Typography>
                    )}
                    <DialogSoumettreAuDu onUtilisateurCree={setAgent} />
                    <Box sx={{ display: "flex", justifyContent: "end", gap: 1 }}>
                        <Button onClick={handleClose} color="primary">
                            Annuler
                        </Button>
                        <Button onClick={handleAdd} variant="contained" color="primary">
                            Ajouter
                        </Button>
                    </Box>
                </Box>
            </Dialog>
        </>
    )
}

function DialogSoumettreAuDu({ onUtilisateurCree }: { onUtilisateurCree: (agent: Agent) => void }) {
    const [open, setOpen] = useState(false);
    const localForm = useForm<{
        nom: string;
        prenom: string;
        email: string;
        employeur: number | null;
        corps: number | null;
        grade: number | null;
        type_contrat: number | null;
        unite: number | null;
        doctorant: boolean | null;
        universite_rattachement: number | null;
        baps_ids: number[];
        emploi_types_ids: number[];
        section_cnrs: number | null;
        cnu: number | null;
    }>({
        defaultValues: {
            nom: "",
            prenom: "",
            email: "",
            employeur: null,
            corps: null,
            grade: null,
            type_contrat: null,
            unite: null,
            doctorant: null,
            universite_rattachement: null,
            baps_ids: [],
            emploi_types_ids: [],
            section_cnrs: null,
            cnu: null,
        },
    });

    const { handleSubmit, control, reset } = localForm;

    const snackbar = useContext(SnackbarContext);

    const { employeurOptions, corps, typeContratOptions, uniteOptions, typeContrat, universiteOptions, bap, bapOptions, cnu, sectionCnrs, employeur } = useContext(DictionnaireContext);
    const selectedCorpsId = useWatch({ control, name: "corps" });

    const corpsOptions = useMemo(
        () => corps.map((item) => ({ value: item.id, label: item.acronyme ?? item.nom })),
        [corps],
    );

    const gradeOptions = useMemo(() => {
        if (!selectedCorpsId) {
            return [];
        }

        const selectedCorps = corps.find((item) => item.id === Number(selectedCorpsId));

        return selectedCorps?.grades?.map((item) => ({ value: item.id, label: item.nom })) ?? [];
    }, [corps, selectedCorpsId]);

    const onSubmit = async (data: {
        nom: string;
        prenom: string;
        email: string;
        employeur: number | null;
        corps: number | null;
        grade: number | null;
        type_contrat: number | null;
        unite: number | null;
        doctorant: boolean | null;
        universite_rattachement: number | null;
        baps_ids: number[];
        emploi_types_ids: number[];
        section_cnrs: number | null;
        cnu: number | null;
    }) => {
        try {
            const res = await soumettreAgentAuDu({
                nom: data.nom,
                prenom: data.prenom,
                email: data.email,
                employeur: data.employeur,
                corps: data.corps,
                grade: data.grade,
                type_contrat: data.type_contrat,
                unite: data.unite,
                doctorant: data.doctorant,
                universite_rattachement: data.universite_rattachement,
                section_cnrs: data.section_cnrs,
                cnu: data.cnu,
                baps_ids: data.baps_ids,
                emploi_types_ids: data.emploi_types_ids,
            });

            onUtilisateurCree({
                id: res.utilisateur.id,
                nom: res.utilisateur.nom,
                prenom: res.utilisateur.prenom,
                email: res.utilisateur.email,
                employeur: res.utilisateur.employeur,
                corps: res.utilisateur.corps,
                grade: res.utilisateur.grade,
                type_contrat: res.utilisateur.type_contrat,
                unite: res.utilisateur.unite,
                doctorant: res.utilisateur.doctorant,
                universite_rattachement: res.utilisateur.universite_rattachement,
                section_cnrs: res.utilisateur.section_cnrs,
                cnu: res.utilisateur.cnu,
                baps_ids: res.utilisateur.baps_ids,
                emploi_types_ids: res.utilisateur.emploi_types_ids,
            });


            reset();
            setOpen(false);
            snackbar?.trigger({
                couleur: "success",
                message: res.message,
            })
        }
        catch (error) {
            const axiosError = error as { response?: { data?: { message?: string } } };
            const message = axiosError.response?.data?.message ?? "Une erreur est survenue lors de la création de l'agent. Veuillez réessayer.";
            snackbar?.trigger({
                couleur: "error",
                message: message,
            })
        }
    };

    const onError = (errors: unknown) => {
        console.log(errors);
    };

    const handleClickOpen = () => {
        setOpen(true);
    };

    const handleClose = () => {
        setOpen(false);
    };

    const contratActuel = useWatch({ control, name: "type_contrat" });
    const estCDD = typeContrat.find((item) => item.id === contratActuel)?.nom === "CDD";

    const estDoctorant = useWatch({ control, name: "doctorant" });

    const corpsActuel = useWatch({ control, name: "corps" });

    const entiteCorpsActuel = useMemo(() => {
        if (!corpsActuel) {
            return null;
        }

        return corps.find((item) => item.id === corpsActuel) ?? null;
    }, [corps, corpsActuel]);

    const watchContrat = useWatch({ control, name: "type_contrat" });
    const watchEmployeur = useWatch({ control, name: "employeur" });
    const watchDoctorant = useWatch({ control, name: "doctorant" });
    const watchCorps = useWatch({ control, name: "corps" });

    const entiteEmployeur = useMemo(() => {
        if (!watchEmployeur) {
            return null;
        }

        return employeur.find((item) => item.id === watchEmployeur) ?? null;
    }, [employeur, watchEmployeur]);

    const epic = entiteEmployeur?.epic ?? false;

    useEffect(() => {
        localForm.setValue("doctorant", null);
        localForm.setValue("universite_rattachement", null);
        localForm.setValue("corps", null);
        localForm.setValue("grade", null);
        localForm.setValue("baps_ids", []);
        localForm.setValue("emploi_types_ids", []);
        localForm.setValue("section_cnrs", null);
        localForm.setValue("cnu", null);
    }, [watchContrat, localForm]);

    useEffect(() => {
        if (employeur) {
            const employeurEntite = employeur.find((item) => item.id === watchEmployeur)
            if (employeurEntite?.epic) {
                localForm.setValue("baps_ids", []);
                localForm.setValue("emploi_types_ids", []);
                localForm.setValue("section_cnrs", null);
                localForm.setValue("cnu", null);
            }
        }

    }, [watchEmployeur, localForm, employeur]);

    useEffect(() => {
        localForm.setValue("corps", null);
        localForm.setValue("grade", null);
        localForm.setValue("baps_ids", []);
        localForm.setValue("emploi_types_ids", []);
        localForm.setValue("section_cnrs", null);
        localForm.setValue("cnu", null);
        localForm.setValue("universite_rattachement", null);
    }, [watchDoctorant, localForm]);

    useEffect(() => {
        localForm.setValue("grade", null);
        localForm.setValue("baps_ids", []);
        localForm.setValue("emploi_types_ids", []);
        localForm.setValue("section_cnrs", null);
        localForm.setValue("cnu", null);
    }, [watchCorps, localForm]);

    const compatibleBap = entiteCorpsActuel?.compatible_bap ?? false;
    const compatibleCnu = entiteCorpsActuel?.compatible_cnu ?? false;
    const compatibleSection = entiteCorpsActuel?.compatible_section ?? false;

    const cnuOptions = useMemo(
        () => cnu.map((item) => ({ value: item.id, label: `${item.numero} - ${item.nom}` })),
        [cnu],
    );

    const sectionCnrsOptions = useMemo(
        () => sectionCnrs.map((item) => ({ value: item.id, label: `${item.numero} - ${item.nom}` })),
        [sectionCnrs],
    );

    const baps_ids = useWatch({ control, name: "baps_ids" });

    const emploiTypesOptions = useMemo(() => {
        if (!baps_ids || baps_ids.length === 0) {
            return [];
        }

        const bapsSelect = bap.filter((bapItem) => baps_ids.includes(bapItem.id));
        const etDispo: { id: number; nom: string }[] = [];
        bapsSelect.forEach((bapItem) => {
            if (bapItem.emploiTypes) {
                etDispo.push(...bapItem.emploiTypes);
            }
        });

        return etDispo.map((item) => ({ value: item.id, label: item.nom }));
    }, [baps_ids, bap]);

    return (
        <>
            <FormProvider {...localForm}>
                <Link
                    onClick={(event) => {
                        event.preventDefault();
                        handleClickOpen();
                    }}
                    variant="body2"
                    href="#"
                    underline="hover"
                >
                    L'agent
                    n'existe pas ? Cliquez ici pour le créer.
                </Link>
                <Dialog open={open} onClose={handleClose}>
                    <Box component={"form"} onSubmit={(e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        handleSubmit(onSubmit, onError)(e);
                    }} sx={{ p: 2, display: "flex", flexDirection: "column", gap: 2 }}>
                        <Typography variant="h6" component="h2">
                            Soumettre une création d'agent au DU
                        </Typography>
                        <Stack direction="row" spacing={2}>
                            <RHFTextField
                                control={control}
                                label="Nom"
                                name="nom"
                                rules={{ required: "Le nom est requis" }}
                            />
                            <RHFTextField
                                control={control}
                                label="Prenom"
                                name="prenom"
                                rules={{ required: "Le prénom est requis" }}
                            />
                        </Stack>
                        <RHFTextField
                            control={control}
                            label="Email"
                            name="email"
                            rules={{ required: "L'email est requis", pattern: { value: /^\S+@\S+$/i, message: "L'email n'est pas valide" } }}
                        />
                        <RHFSelect name="employeur" label="Employeur" control={control} items={employeurOptions} />
                        <RHFSelect name="unite" label="Unité" control={control} items={uniteOptions} />
                        <RHFSelect name="type_contrat" label="Type de contrat" control={control} items={typeContratOptions} />
                        {contratActuel && !estCDD && (
                            <>
                                <Stack direction="row" spacing={2}>
                                    <RHFSelect name="corps" label="Corps" control={control} items={corpsOptions} />
                                    <RHFSelect name="grade" label="Grade" control={control} items={gradeOptions} disabled={!selectedCorpsId} />
                                </Stack>
                                {(compatibleBap && !epic) && (
                                    <Stack direction="row" spacing={2}>
                                        <RHFSelect name="baps_ids" label="BAP" control={control} items={bapOptions} multiple />
                                        <RHFSelect name="emploi_types_ids" label="Emploi Types" control={control} items={emploiTypesOptions} multiple />
                                    </Stack>
                                )}
                                {(compatibleCnu && !epic) && (
                                    <RHFSelect name="cnu" label="CNU" control={control} items={cnuOptions} />
                                )}
                                {(compatibleSection && !epic) && (
                                    <RHFSelect name="section_cnrs" label="Section CNRS" control={control} items={sectionCnrsOptions} />
                                )}
                            </>

                        )}
                        {contratActuel && estCDD && (
                            <>
                                <RadioTextFieldHint name="doctorant" label="Est-ce que le personnel est un doctorant" helperText="" />
                                {estDoctorant === true && (
                                    <RHFSelect name="universite_rattachement" label="Université de rattachement" control={control} items={universiteOptions} />
                                )}
                                {estDoctorant === false && (
                                    <Stack direction="row" spacing={2}>
                                        <RHFSelect name="corps" label="Corps" control={control} items={corpsOptions} />
                                        <RHFSelect name="grade" label="Grade" control={control} items={gradeOptions} disabled={!selectedCorpsId} />
                                    </Stack>
                                )}
                                {(compatibleBap && !epic) && (
                                    <Stack direction="row" spacing={2}>
                                        <RHFSelect name="baps_ids" label="BAP" control={control} items={bapOptions} multiple />
                                        <RHFSelect name="emploi_types_ids" label="Emploi Types" control={control} items={emploiTypesOptions} multiple />
                                    </Stack>
                                )}

                            </>)}


                        <Button type="submit" variant="contained" color="primary">
                            Soumettre l'agent au DU
                        </Button>
                    </Box>
                </Dialog>
            </FormProvider>
        </>
    )
}

type PersonnelCardProps = {
    personnel: PersonnelActuelFormValue;
    onDelete: () => void;
};

function PersonnelCard({ personnel, onDelete }: PersonnelCardProps) {
    const estResponsableScientifique = personnel.responsable_scientifique;
    const estResponsableTechnique = personnel.responsable_technique;
    const estResponsablePlateforme = personnel.responsable_plateforme;
    const backgroundGradient = estResponsableScientifique
        ? "linear-gradient(90deg, #ede7f6 0%, #ffffff 100%)"
        : estResponsableTechnique
            ? "linear-gradient(90deg, #e8f5e9 0%, #ffffff 100%)"
            : estResponsablePlateforme
                ? "linear-gradient(90deg, #fff3e0 0%, #ffffff 100%)"
                : "background.paper";

    return (
        <Box sx={{
            p: 2, py: 1, my: 2, border: "1px solid", borderColor: "divider", borderRadius: 5, display: "flex", justifyContent: "space-between", gap: 2,
            background: backgroundGradient,
        }}>


            <Box sx={{
                display: "flex", alignItems: "center", gap: 1
            }}>
                <Account />
                <Typography>{personnel.utilisateur.nom}</Typography>
                <Typography>{personnel.utilisateur.prenom}</Typography>
                <Typography>{personnel.utilisateur.email}</Typography>
                <Chip label={`${personnel.quotite}%`} size="small" />
                {(personnel.utilisateur.type_contrat || personnel.utilisateur.corps) && <Chip color="info" label={typePersonnelLabel(personnel.utilisateur.corps?.acronyme, personnel.utilisateur.type_contrat?.nom, personnel.utilisateur.doctorant)} size="small" />}
                {estResponsableScientifique && <Chip color="secondary" label="Responsable scientifique" size="small" />}
                {estResponsableTechnique && <Chip color="secondary" label="Responsable technique" size="small" />}
                {estResponsablePlateforme && <Chip color="secondary" label="Responsable plateforme" size="small" />}
            </Box >
            <Button color="error" disabled={estResponsableScientifique || estResponsableTechnique || estResponsablePlateforme} onClick={onDelete}>
                Supprimer
            </Button>
        </Box >
    )

}

export function PersonnelActuels() {
    const { getValues, setValue, watch } = useFormContext();

    // Remplissage initial
    useEffect(() => {
        const initialValue = getValues(PERSONNELS_FIELD_NAME) as PersonnelActuelFormValue[] | undefined;
        if (!Array.isArray(initialValue)) {
            setValue(PERSONNELS_FIELD_NAME, [], { shouldDirty: false, shouldValidate: false });
        }
    }, [getValues, setValue]);

    // On attache un watcher sur les personnels pour forcer le re-render du composant à chaque modification de la liste des personnels
    const personnels = (watch(PERSONNELS_FIELD_NAME) as PersonnelActuelFormValue[] | undefined) ?? [];

    const handleAddPersonnel = (personnel: PersonnelActuelFormValue) => {
        // Ici personnel et le nouvel agent
        const currentPersonnels = (getValues(PERSONNELS_FIELD_NAME) as PersonnelActuelFormValue[] | undefined) ?? [];
        const existingPersonnelIndex = currentPersonnels.findIndex((current) => current.utilisateur_id === personnel.utilisateur_id);

        // On modifie la personne si elle existe déjà
        if (existingPersonnelIndex >= 0) {
            const updated = [...currentPersonnels];
            updated[existingPersonnelIndex] = personnel;
            setValue(PERSONNELS_FIELD_NAME, updated, { shouldDirty: true, shouldTouch: true, shouldValidate: true });
            return;
        } else {
            // On modifie la valeur pour y mettre un tableau naissant de la destructuration de l'ancien tableau + le nouveau personnel
            setValue(PERSONNELS_FIELD_NAME, [...currentPersonnels, personnel], {
                shouldDirty: true,
                shouldTouch: true,
                shouldValidate: true,
            });
        }
    };

    const handleDeletePersonnel = (utilisateur_id: number) => {
        const currentPersonnels = (getValues(PERSONNELS_FIELD_NAME) as PersonnelActuelFormValue[] | undefined) ?? [];
        const nextPersonnels = currentPersonnels.filter((personnel) => personnel.utilisateur_id !== utilisateur_id);

        setValue(PERSONNELS_FIELD_NAME, nextPersonnels, {
            shouldDirty: true,
            shouldTouch: true,
            shouldValidate: true,
        });
    };

    return (
        <Box sx={{ p: 1 }}>
            <Box sx={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
                <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold" }}>
                    Personnels actuels
                </Typography>
                <DialogAjoutPersonnel onAdd={handleAddPersonnel} />
            </Box>

            {personnels.map((personnel) => (
                <PersonnelCard
                    key={personnel.utilisateur_id}
                    personnel={personnel}
                    onDelete={() => handleDeletePersonnel(personnel.utilisateur_id)}
                />
            ))}
            {personnels.length === 0 && (
                <Typography sx={{ mt: 2 }} color="text.secondary">
                    Aucun personnel actuel.
                </Typography>
            )}
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 4 }}>
                Autres étudiants en formation longue sur la plateforme
            </Typography>
            <NombreTextFieldHint helperText="" name="nombre_autres_etudiants" label="Combien d’étudiants sont actuellement en formation directe auprès de la plateforme (stage recherche, Licence, Master, M2,BTS, DUT...) ?" />
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 4 }}>
                Apprentis/alternants
            </Typography>
            <NombreTextFieldHint helperText="" name="nombre_apprentis_alternants" label="Combien d’apprentis/alternants sont actuellement en formation directe auprès de la plateforme ?" />
        </Box>
    )
}