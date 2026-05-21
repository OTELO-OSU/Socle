import { Box, Tab, Tabs } from "@mui/material";
import { useState } from "react";
import { allyProps } from "./allyProps";
import { CustomTabPanel } from "../CustomTabPanel";

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
                <h1>Accesibilité</h1>
            </CustomTabPanel>
            <CustomTabPanel value={value} index={1}>
                <h1>Utilisation</h1>
            </CustomTabPanel>
            <CustomTabPanel value={value} index={2}>
                <h1>Empreinte Carbone</h1>
            </CustomTabPanel>
        </>
    )
}