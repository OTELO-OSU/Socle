import { Box, Tab, Tabs } from "@mui/material";
import { useState } from "react";
import { allyProps } from "./allyProps";
import { CustomTabPanel } from "../CustomTabPanel";
import { PersonnelActuels } from "./personnels/PersonnelsActuels";
import { PersonnelFuturs } from "./personnels/PersonnelsFuturs";

export function Personnels({ disabled = false }: { disabled?: boolean }) {
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
                </Tabs>
            </Box>
            <Box component="fieldset" disabled={disabled} sx={{ border: 0, p: 0, m: 0, minWidth: 0 }}>
                <CustomTabPanel value={value} index={0}>
                    <PersonnelActuels disabled={disabled} />
                </CustomTabPanel>
                <CustomTabPanel value={value} index={1}>
                    <PersonnelFuturs disabled={disabled} />
                </CustomTabPanel>
            </Box>
        </Box>
    )
}