import { Box, Button, Dialog, Typography } from "@mui/material";
import Account from '@mui/icons-material/AccountCircle';
import { useFormContext } from "react-hook-form";
import { RHFTextField } from "../../RHFTextField";
import { useState } from "react";
import { AgentAutocompleteField } from "../../AgentAutocompleteField";
import type { Agent } from "../../../services/agentService";

function DialogAjoutPersonnel() {
    const [open, setOpen] = useState(false);
    const [agent, setAgent] = useState<Agent | null>(null);

    const handleAgentChange = (value: Agent | null) => {
        setAgent(value);
    }

    const handleClickOpen = () => {
        setOpen(true);
    };

    const handleClose = () => {
        setOpen(false);
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
                    <RHFTextField name="quotite" control={useFormContext().control} label="Quotité de travail (%)" placeholder="50" />
                    <Box sx={{ display: "flex", justifyContent: "end", gap: 1 }}>
                        <Button onClick={handleClose} color="primary">
                            Annuler
                        </Button>
                        <Button onClick={() => { }} variant="contained" color="primary">
                            Ajouter
                        </Button>
                    </Box>
                </Box>
            </Dialog>
        </>
    )
}

function PersonnelCard() {
    return (
        <Box sx={{
            p: 2, py: 1, my: 2, border: "1px solid", borderColor: "divider", borderRadius: 5, display: "flex", justifyContent: "space-between", gap: 2
        }}>


            <Box sx={{
                display: "flex", alignItems: "center", gap: 1
            }}>
                <Account />
                <Typography>Prénom du personnel</Typography>
                <Typography>Prénom du personnel</Typography>
                <Typography>Prénom du personnel</Typography>
                <Typography>Quotitié du personnel</Typography>
                {/* <Grid size={{ xs: 6, md: 3 }}>
                    <Typography>Prénom du personnel</Typography>
                    <RHFTextField
                        control={control}
                        label="Quotité de travail (%)"
                        name="role"
                        placeholder="50" />
                </Grid> */}
            </Box >
            ferf
        </Box >
    )

}

export function PersonnelActuels() {
    return (
        <Box sx={{ p: 1 }}>
            <Box sx={{ display: "flex", alignItems: "center", justifyContent: "end" }}>
                <DialogAjoutPersonnel />
            </Box>
            <PersonnelCard />
            <PersonnelCard />
            <PersonnelCard />
            <PersonnelCard />
        </Box>
    )
}