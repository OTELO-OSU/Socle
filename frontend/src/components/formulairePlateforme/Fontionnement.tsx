import { Box, Tab, Tabs } from "@mui/material";
import { useState } from "react";
import { allyProps } from "./allyProps";
import { CustomTabPanel } from "../CustomTabPanel";
import Couts from "./fonctionnement/Couts";

export function Fonctionnement() {
    const [value, setValue] = useState(0);

    const handleChange = (_: React.SyntheticEvent, newValue: number) => {
        setValue(newValue);
    };
    return (
        <>
            <Box sx={{ py: 1 }}>
                <Tabs value={value} onChange={handleChange} aria-label="basic tabs example">
                    <Tab label="Coûts" {...allyProps(0)} />
                    <Tab label="Ressources" {...allyProps(1)} />
                </Tabs>
            </Box>
            <CustomTabPanel value={value} index={0}>
                <Couts />
            </CustomTabPanel>
            <CustomTabPanel value={value} index={1}>
                <h1>Ressources</h1>
            </CustomTabPanel>
        </>
    )
}