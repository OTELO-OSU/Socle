import { Box, Button, Card, CardActions, CardContent, Chip, Dialog, IconButton, Link, TextField, Typography } from "@mui/material";
import Account from '@mui/icons-material/AccountCircle';
import { FormProvider, useForm, useFormContext, useWatch } from "react-hook-form";
import { useContext, useEffect, useState } from "react";
import { AgentAutocompleteField } from "../../AgentAutocompleteField";
import { type Agent, soumettreAgentAuDu } from "../../../services/agentService";
import { typePersonnelLabel } from "../../../services/formatLabelService";
import { AgentAdvancedFields } from "../../AgentAdvancedFields";
import { SnackbarContext } from "../../../context/snackbarContext";
import NombreTextFieldHint from "../../NombreTextFieldHint";
import { DictionnaireContext } from "../../../context/dictionnaireContext";
import { Delete, Edit } from "@mui/icons-material";
import NombreTextField from "../../NombreTextField";

type PersonnelActuelFormValue = {
    utilisateur_id: number;
    utilisateur: Agent;
    quotite: number | null;
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
    const [quotite, setQuotite] = useState<number | null>(null);
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
        setQuotite(null);
        setErrorMessage(null);
    };

    const handleAdd = () => {
        if (!agent) {
            setErrorMessage("Veuillez sélectionner un agent.");
            return;
        }

        let quotiteNumber = null;
        if (!agent.doctorant) {
            if (quotite === null || quotite === undefined) {
                setErrorMessage("Veuillez saisir la quotité de travail.");
                return;
            }
            quotiteNumber = Number(quotite);

            const isQuotiteInvalid = quotiteNumber < 0 || quotiteNumber > 100;

            if (isQuotiteInvalid) {
                setErrorMessage("La quotité doit être un nombre entre 0 et 100.");
                return;
            }
        }

        onAdd({
            utilisateur_id: agent.id!,
            utilisateur: {
                id: agent.id,
                nom: agent.nom,
                prenom: agent.prenom,
                email: agent.email,
                corps_id: agent.corps_id ?? null,
                grade_id: agent.grade_id ?? null,
                type_contrat_id: agent.type_contrat_id ?? null,
                doctorant: agent.doctorant ?? null,
                universite_rattachement_id: agent.universite_rattachement_id ?? null,
                baps_ids: agent.baps_ids ?? [],
                emploi_types_ids: agent.emploi_types_ids ?? [],
                section_cnrs_id: agent.section_cnrs_id ?? null,
                cnu_id: agent.cnu_id ?? null,
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
                Modifier les personnels actuels
            </Button>
            <Dialog open={open} onClose={handleClose}>
                <Box sx={{ p: 2, display: "flex", flexDirection: "column", gap: 2 }}>
                    <Typography variant="h6" component="h2">
                        Modifier les personnels actuels
                    </Typography>
                    <AgentAutocompleteField label="Agent" value={agent} onChange={handleAgentChange} />
                    {agent && !agent.doctorant && (
                        <TextField
                            label="Quotité de travail (%)"
                            placeholder="50"
                            type="number"
                            value={quotite}
                            onChange={(event) => {
                                setQuotite(event.target.value ? Number(event.target.value) : null);
                                setErrorMessage(null);
                            }}
                            slotProps={{ htmlInput: { min: 0, max: 100 } }}
                        />
                    )}
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
        if (!data.nom.trim() || !data.prenom.trim()) {
            snackbar?.trigger({
                couleur: "error",
                message: "Le nom et le prénom sont requis",
            });
            return;
        }

        if (!/^\S+@\S+$/i.test(data.email)) {
            snackbar?.trigger({
                couleur: "error",
                message: "L'email n'est pas valide",
            });
            return;
        }

        try {
            const res = await soumettreAgentAuDu({
                nom: data.nom,
                prenom: data.prenom,
                email: data.email,
                employeur_id: data.employeur,
                corps_id: data.corps,
                grade_id: data.grade,
                type_contrat_id: data.type_contrat,
                unite_id: data.unite,
                doctorant: data.doctorant,
                universite_rattachement_id: data.universite_rattachement,
                section_cnrs_id: data.section_cnrs,
                cnu_id: data.cnu,
                baps_ids: data.baps_ids,
                emploi_types_ids: data.emploi_types_ids,
            });

            onUtilisateurCree({
                id: res.utilisateur.id,
                nom: res.utilisateur.nom,
                prenom: res.utilisateur.prenom,
                email: res.utilisateur.email,
                employeur_id: res.utilisateur.employeur_id ?? null,
                corps_id: res.utilisateur.corps_id ?? null,
                grade_id: res.utilisateur.grade_id ?? null,
                type_contrat_id: res.utilisateur.type_contrat_id ?? null,
                unite_id: res.utilisateur.unite_id ?? null,
                doctorant: res.utilisateur.doctorant,
                universite_rattachement_id: res.utilisateur.universite_rattachement_id ?? null,
                section_cnrs_id: res.utilisateur.section_cnrs_id ?? null,
                cnu_id: res.utilisateur.cnu_id ?? null,
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

    const employeur_id = useWatch({ control, name: "employeur" }) ?? null;
    const unite_id = useWatch({ control, name: "unite" }) ?? null;
    const type_contrat_id = useWatch({ control, name: "type_contrat" }) ?? null;
    const nom = useWatch({ control, name: "nom" }) ?? "";
    const prenom = useWatch({ control, name: "prenom" }) ?? "";
    const email = useWatch({ control, name: "email" }) ?? "";
    const doctorant = useWatch({ control, name: "doctorant" }) ?? null;
    const universite_rattachement_id = useWatch({ control, name: "universite_rattachement" }) ?? null;
    const corps_id = useWatch({ control, name: "corps" }) ?? null;
    const grade_id = useWatch({ control, name: "grade" }) ?? null;
    const baps_ids = useWatch({ control, name: "baps_ids" }) ?? [];
    const emploi_types_ids = useWatch({ control, name: "emploi_types_ids" }) ?? [];
    const section_cnrs_id = useWatch({ control, name: "section_cnrs" }) ?? null;
    const cnu_id = useWatch({ control, name: "cnu" }) ?? null;


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
                        <AgentAdvancedFields
                            value={{
                                nom,
                                prenom,
                                email,
                                employeur_id,
                                unite_id,
                                type_contrat_id,
                                doctorant,
                                universite_rattachement_id,
                                corps_id,
                                grade_id,
                                baps_ids,
                                emploi_types_ids,
                                section_cnrs_id,
                                cnu_id,
                            }}
                            onChange={(next) => {
                                localForm.setValue("nom", next.nom, { shouldDirty: true });
                                localForm.setValue("prenom", next.prenom, { shouldDirty: true });
                                localForm.setValue("email", next.email, { shouldDirty: true });
                                localForm.setValue("employeur", next.employeur_id!, { shouldDirty: true });
                                localForm.setValue("unite", next.unite_id!, { shouldDirty: true });
                                localForm.setValue("type_contrat", next.type_contrat_id!, { shouldDirty: true });
                                localForm.setValue("doctorant", next.doctorant!, { shouldDirty: true });
                                localForm.setValue("universite_rattachement", next.universite_rattachement_id!, { shouldDirty: true });
                                localForm.setValue("corps", next.corps_id!, { shouldDirty: true });
                                localForm.setValue("grade", next.grade_id!, { shouldDirty: true });
                                localForm.setValue("baps_ids", next.baps_ids!, { shouldDirty: true });
                                localForm.setValue("emploi_types_ids", next.emploi_types_ids!, { shouldDirty: true });
                                localForm.setValue("section_cnrs", next.section_cnrs_id!, { shouldDirty: true });
                                localForm.setValue("cnu", next.cnu_id!, { shouldDirty: true });
                            }}
                        />


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
    onQuotaUpdate: (number: number | null) => void;
};

function PersonnelCard({ personnel, onDelete, onQuotaUpdate }: PersonnelCardProps) {
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

    const dictionnaireContext = useContext(DictionnaireContext);
    const corps = dictionnaireContext?.corps.find(c => c.id === personnel.utilisateur.corps_id) ?? null;
    const typeContrat = dictionnaireContext?.typeContrat.find(t => t.id === personnel.utilisateur.type_contrat_id) ?? null;

    const [dialogOpen, setDialogOpen] = useState(false);
    const [newQuotite, setNewQuotite] = useState<number | null>(personnel.quotite);

    return (
        <Box sx={{
            p: 2, py: 1, my: 2, border: "1px solid", borderColor: "divider", borderRadius: 5, display: "flex", justifyContent: "space-between", gap: 2,
            background: backgroundGradient,
        }}>

            <Dialog open={dialogOpen} onClose={() => setDialogOpen(false)}>
                <Card>
                    <CardContent>
                        <Typography variant="h6" component="h2">
                            Changement de la quotité de travail
                        </Typography>
                        <TextField
                            sx={{ mt: 2 }}
                            fullWidth
                            label="Quotité de travail (%)"
                            placeholder="50"
                            type="number"
                            value={newQuotite ?? 0}
                            onChange={(event) => {
                                setNewQuotite(event.target.value ? Number(event.target.value) : null);
                            }}
                            slotProps={{ htmlInput: { min: 0, max: 100 } }}
                        />
                    </CardContent>
                    <CardActions sx={{ display: "flex", justifyContent: "end" }}>
                        <Button onClick={() => setDialogOpen(false)} color="primary">
                            Annuler
                        </Button>
                        <Button onClick={() => {
                            onQuotaUpdate(newQuotite);
                            setDialogOpen(false)
                        }} variant="contained" color="primary">
                            Valider
                        </Button>
                    </CardActions>
                </Card>
            </Dialog>


            <Box sx={{
                display: "flex", alignItems: "center", gap: 1
            }}>
                <Account />
                <Typography>{personnel.utilisateur.nom}</Typography>
                <Typography>{personnel.utilisateur.prenom}</Typography>
                <Typography>{personnel.utilisateur.email}</Typography>
                {
                    personnel.quotite !== null && <Chip label={`${personnel.quotite}%`} size="small" />
                }
                {(personnel.utilisateur.type_contrat_id || personnel.utilisateur.corps_id) && <Chip color="info" label={typePersonnelLabel(corps?.acronyme, typeContrat?.nom, personnel.utilisateur.doctorant)} size="small" />}
                {estResponsableScientifique && <Chip color="secondary" label="Responsable scientifique" size="small" />}
                {estResponsableTechnique && <Chip color="secondary" label="Responsable technique" size="small" />}
                {estResponsablePlateforme && <Chip color="secondary" label="Responsable plateforme" size="small" />}
            </Box >
            <Box>
                <IconButton color="error" onClick={() => setDialogOpen(true)}>
                    <Edit />
                </IconButton>
                <IconButton color="error" disabled={estResponsableScientifique || estResponsableTechnique || estResponsablePlateforme} onClick={onDelete}>
                    <Delete />
                </IconButton>
            </Box>

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
            const persMerge = { ...personnel, responsable_plateforme: currentPersonnels[existingPersonnelIndex].responsable_plateforme, responsable_scientifique: currentPersonnels[existingPersonnelIndex].responsable_scientifique, responsable_technique: currentPersonnels[existingPersonnelIndex].responsable_technique };
            updated[existingPersonnelIndex] = persMerge;
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

    const handleUpdateQuotite = (utilisateur_id: number, newQuotite: number | null) => {
        const currentPersonnels = (getValues(PERSONNELS_FIELD_NAME) as PersonnelActuelFormValue[] | undefined) ?? [];
        const updatedPersonnels = currentPersonnels.map((personnel) => {
            if (personnel.utilisateur_id === utilisateur_id) {
                return { ...personnel, quotite: newQuotite };
            }
            return personnel;
        });

        setValue(PERSONNELS_FIELD_NAME, updatedPersonnels, {
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
                    onQuotaUpdate={(number) => handleUpdateQuotite(personnel.utilisateur_id, number)}
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