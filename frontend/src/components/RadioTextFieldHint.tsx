import { FormControlLabel, Radio, RadioGroup } from "@mui/material";
import CoutTextFieldHint from "./CoutTextFieldHint";
import { Controller, useFormContext } from "react-hook-form";

export default function RadioTextFieldHint({ label, name, helperText }: { label: string; name: string; helperText: string }) {
    const { control } = useFormContext();
    return (
        <CoutTextFieldHint label={label} name={name} helperText={helperText}>
            <Controller
                name={name}
                control={control}
                render={({ field }) => {
                    const { value, ...fieldProps } = field;
                    return (
                        <>
                            <RadioGroup
                                row
                                {...fieldProps}
                                value={value ?? null}
                            >
                                <FormControlLabel value="true" control={<Radio />} label="Oui" />
                                <FormControlLabel value="false" control={<Radio />} label="Non" />
                            </RadioGroup>
                        </>
                    )
                }}
            />
        </CoutTextFieldHint>
    );
}