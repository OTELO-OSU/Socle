import { Box, Tab, Tabs } from "@mui/material";
import { useState } from "react";
import { allyProps } from "./allyProps";
import { CustomTabPanel } from "../CustomTabPanel";
import { EquipementActuels } from "./equipements/EquipementsActuels";
import { EquipementFuturs } from "./equipements/EquipementsFuturs";
import { EquipementActuelsMaintenance } from "./equipements/Maintenance";

export function Equipements({ disabled = false }: { disabled?: boolean }) {
    const [value, setValue] = useState(0);

    const handleChange = (_: React.SyntheticEvent, newValue: number) => {
        setValue(newValue);
    };
    return (
        <Box>
            <Box sx={{ py: 1 }}>
                <Tabs value={value} onChange={handleChange} aria-label="basic tabs example">
                    <Tab label="Actuels" {...allyProps(0)} />
                    <Tab label="Futurs" {...allyProps(1)} />
                    <Tab label="Maintenance" {...allyProps(2)} />
                </Tabs>
            </Box>
            <Box component="fieldset" disabled={disabled} sx={{ border: 0, p: 0, m: 0, minWidth: 0 }}>
                <CustomTabPanel value={value} index={0}>
                    <EquipementActuels />
                </CustomTabPanel>
                <CustomTabPanel value={value} index={1}>
                    <EquipementFuturs />
                </CustomTabPanel>
                <CustomTabPanel value={value} index={2}>
                    <EquipementActuelsMaintenance />
                </CustomTabPanel>
            </Box>
        </Box>
    )
}