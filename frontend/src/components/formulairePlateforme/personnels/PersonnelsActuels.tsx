import { Box, Button, Chip, Dialog, Link, Stack, TextField, Typography } from "@mui/material";
import Account from '@mui/icons-material/AccountCircle';
import { FormProvider, useForm, useFormContext, useWatch } from "react-hook-form";
import { useContext, useEffect, useMemo, useState } from "react";
import { AgentAutocompleteField } from "../../AgentAutocompleteField";
import { type Agent, type typeContrat, type Corps, type Grade, soumettreAgentAuDu } from "../../../services/agentService";
import { typePersonnelLabel } from "../../../services/formatLabelService";
import { RHFTextField } from "../../RHFTextField";
import { RHFSelect } from "../../RHFTextSelect";
import { DictionnaireContext } from "../../../context/dictionnaireContext";
import { SnackbarContext } from "../../../context/snackbarContext";
import RadioTextFieldHint from "../../RadioTextFieldHint";

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
        universite_ratachement: number | null;
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
                universite_ratachement: agent.universite_ratachement ?? null,
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
        universite_ratachement: number | null;
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
            universite_ratachement: null,
        },
    });

    const { handleSubmit, control, reset } = localForm;

    const snackbar = useContext(SnackbarContext);

    const { employeurOptions, corps, typeContratOptions, uniteOptions, typeContrat, universiteOptions } = useContext(DictionnaireContext);
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
        universite_ratachement: number | null;
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
                universite_ratachement: data.universite_ratachement,
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
                universite_ratachement: res.utilisateur.universite_ratachement,
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
                            <Stack direction="row" spacing={2}>
                                <RHFSelect name="corps" label="Corps" control={control} items={corpsOptions} />
                                <RHFSelect name="grade" label="Grade" control={control} items={gradeOptions} disabled={!selectedCorpsId} />
                            </Stack>
                        )}
                        {contratActuel && estCDD && (
                            <>
                                <RadioTextFieldHint name="doctorant" label="Est-ce que le personnel est un doctorant" helperText="" />
                                {estDoctorant === true && (
                                    <RHFSelect name="universite_ratachement" label="Université de rattachement" control={control} items={universiteOptions} />
                                )}
                                {estDoctorant === false && (
                                    <Stack direction="row" spacing={2}>
                                        <RHFSelect name="corps" label="Corps" control={control} items={corpsOptions} />
                                        <RHFSelect name="grade" label="Grade" control={control} items={gradeOptions} disabled={!selectedCorpsId} />
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
        </Box>
    )
}