import { Box, Button, Chip, Dialog, TextField, Typography } from "@mui/material";
import Account from '@mui/icons-material/AccountCircle';
import { useFormContext } from "react-hook-form";
import { useEffect, useState } from "react";
import { AgentAutocompleteField } from "../../AgentAutocompleteField";
import type { Agent, typeContrat, Corps, Grade } from "../../../services/agentService";
import { typePersonnelLabel } from "../../../services/formatLabelService";

type PersonnelActuelFormValue = {
    agentId: number;
    nom: string;
    prenom: string;
    email: string;
    quotite: number;
    corps?: Corps | null;
    grade?: Grade | null;
    typeContrat?: typeContrat | null;
};

const PERSONNELS_FIELD_NAME = "personnelsActuels";

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
            agentId: agent.id,
            nom: agent.nom,
            prenom: agent.prenom,
            email: agent.email,
            quotite: quotiteNumber,
            corps: agent.corps,
            grade: agent.grade,
            typeContrat: agent.typeContrat,
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
                    <AgentAutocompleteField label="agent" value={agent} onChange={handleAgentChange} />
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

type PersonnelCardProps = {
    personnel: PersonnelActuelFormValue;
    onDelete: () => void;
};

function PersonnelCard({ personnel, onDelete }: PersonnelCardProps) {
    return (
        <Box sx={{
            p: 2, py: 1, my: 2, border: "1px solid", borderColor: "divider", borderRadius: 5, display: "flex", justifyContent: "space-between", gap: 2
        }}>


            <Box sx={{
                display: "flex", alignItems: "center", gap: 1
            }}>
                <Account />
                <Typography>{personnel.nom}</Typography>
                <Typography>{personnel.prenom}</Typography>
                <Typography>{personnel.email}</Typography>
                <Chip label={`${personnel.quotite}%`} size="small" />
                {(personnel.typeContrat || personnel.corps) && <Chip color="info" label={typePersonnelLabel(personnel.corps?.acronyme, personnel.typeContrat?.nom)} size="small" />}
            </Box >
            <Button color="error" onClick={onDelete}>
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
        const existingPersonnelIndex = currentPersonnels.findIndex((current) => current.agentId === personnel.agentId);

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

    const handleDeletePersonnel = (agentId: number) => {
        const currentPersonnels = (getValues(PERSONNELS_FIELD_NAME) as PersonnelActuelFormValue[] | undefined) ?? [];
        const nextPersonnels = currentPersonnels.filter((personnel) => personnel.agentId !== agentId);

        setValue(PERSONNELS_FIELD_NAME, nextPersonnels, {
            shouldDirty: true,
            shouldTouch: true,
            shouldValidate: true,
        });
    };

    return (
        <Box sx={{ p: 1 }}>
            <Box sx={{ display: "flex", alignItems: "center", justifyContent: "end" }}>
                <DialogAjoutPersonnel onAdd={handleAddPersonnel} />
            </Box>
            {personnels.map((personnel) => (
                <PersonnelCard
                    key={personnel.agentId}
                    personnel={personnel}
                    onDelete={() => handleDeletePersonnel(personnel.agentId)}
                />
            ))}
            {personnels.length === 0 && (
                <Typography sx={{ mt: 2 }} color="text.secondary">
                    Aucun personnel actuel ajouté.
                </Typography>
            )}
        </Box>
    )
}