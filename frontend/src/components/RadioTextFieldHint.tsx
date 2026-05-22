import { FormControlLabel, Radio, RadioGroup } from "@mui/material";
import { Controller, useFormContext } from "react-hook-form";
import LayoutLabelHintInput from "./LayoutLabelHintInput";

export default function RadioTextFieldHint({ label, name, helperText }: { label: string; name: string; helperText: string }) {
    const { control } = useFormContext();
    return (
        <LayoutLabelHintInput label={label} helperText={helperText}>
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
        </LayoutLabelHintInput>
    );
}