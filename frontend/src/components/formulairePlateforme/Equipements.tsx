import { Box, Tab, Tabs } from "@mui/material";
import { useState } from "react";
import { allyProps } from "./allyProps";
import { CustomTabPanel } from "../CustomTabPanel";
import { EquipementActuels } from "./equipements/EquipementsActuels";
import { EquipementFuturs } from "./equipements/EquipementsFuturs";

export function Equipements() {
    const [value, setValue] = useState(0);

    const handleChange = (event: React.SyntheticEvent, newValue: number) => {
        setValue(newValue);
    };
    return (
        <>
            <Box sx={{ py: 1 }}>
                <Tabs value={value} onChange={handleChange} aria-label="basic tabs example">
                    <Tab label="Actuels" {...allyProps(0)} />
                    <Tab label="Futurs" {...allyProps(1)} />
                    <Tab label="Maintenance" {...allyProps(2)} />
                </Tabs>
            </Box>
            <CustomTabPanel value={value} index={0}>
                <EquipementActuels />
            </CustomTabPanel>
            <CustomTabPanel value={value} index={1}>
                <EquipementFuturs />
            </CustomTabPanel>
            <CustomTabPanel value={value} index={2}>
                <h1>Maintenance</h1>
            </CustomTabPanel>
        </>
    )
}