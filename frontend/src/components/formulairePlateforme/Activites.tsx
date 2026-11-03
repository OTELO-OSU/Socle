import { Box, Tab, Tabs } from "@mui/material";
import { useState } from "react";
import { allyProps } from "./allyProps";
import { CustomTabPanel } from "../CustomTabPanel";
import Accesibilite from "./activites/Accesibilite";
import Utilisation from "./activites/Utilisation";
import EmpreinteCarbone from "./activites/EmpreinteCarbone";

export function Activites({ disabled = false }: { disabled?: boolean }) {
    const [value, setValue] = useState(0);

    const handleChange = (_: React.SyntheticEvent, newValue: number) => {
        setValue(newValue);
    };
    return (
        <Box>
            <Box sx={{ py: 1 }}>
                <Tabs value={value} onChange={handleChange} aria-label="basic tabs example">
                    <Tab label="Accesibilité" {...allyProps(0)} />
                    <Tab label="Utilisation" {...allyProps(1)} />
                    <Tab label="Empreinte Carbone" {...allyProps(2)} />
                </Tabs>
            </Box>
            <Box component="fieldset" disabled={disabled} sx={{ border: 0, p: 0, m: 0, minWidth: 0 }}>
                <CustomTabPanel value={value} index={0}>
                    <Accesibilite disabled={disabled} />
                </CustomTabPanel>
                <CustomTabPanel value={value} index={1}>
                    <Utilisation disabled={disabled} />
                </CustomTabPanel>
                <CustomTabPanel value={value} index={2}>
                    <EmpreinteCarbone disabled={disabled} />
                </CustomTabPanel>
            </Box>
        </Box>
    )
}