import { Box, Tab, Tabs } from "@mui/material";
import { useState } from "react";
import { allyProps } from "./allyProps";
import { CustomTabPanel } from "../CustomTabPanel";
import { PersonnelActuels } from "./personnels/PersonnelsActuels";
import { PersonnelFuturs } from "./personnels/PersonnelsFuturs";

export function Personnels() {
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
                </Tabs>
            </Box>
            <CustomTabPanel value={value} index={0}>
                <PersonnelActuels />
            </CustomTabPanel>
            <CustomTabPanel value={value} index={1}>
                <PersonnelFuturs />
            </CustomTabPanel>
        </>
    )
}