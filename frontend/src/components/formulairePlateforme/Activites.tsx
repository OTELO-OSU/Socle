import { Box, Tab, Tabs } from "@mui/material";
import { useState } from "react";
import { allyProps } from "./allyProps";
import { CustomTabPanel } from "../CustomTabPanel";
import Accesibilite from "./activites/Accesibilite";
import Utilisation from "./activites/Utilisation";
import EmpreinteCarbone from "./activites/EmpreinteCarbone";

export function Activites() {
    const [value, setValue] = useState(0);

    const handleChange = (_: React.SyntheticEvent, newValue: number) => {
        setValue(newValue);
    };
    return (
        <>
            <Box sx={{ py: 1 }}>
                <Tabs value={value} onChange={handleChange} aria-label="basic tabs example">
                    <Tab label="Accesibilité" {...allyProps(0)} />
                    <Tab label="Utilisation" {...allyProps(1)} />
                    <Tab label="Empreinte Carbone" {...allyProps(2)} />
                </Tabs>
            </Box>
            <CustomTabPanel value={value} index={0}>
                <Accesibilite />
            </CustomTabPanel>
            <CustomTabPanel value={value} index={1}>
                <Utilisation />
            </CustomTabPanel>
            <CustomTabPanel value={value} index={2}>
                <EmpreinteCarbone />
            </CustomTabPanel>
        </>
    )
}