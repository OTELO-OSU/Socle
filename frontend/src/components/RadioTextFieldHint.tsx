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
                    const { value, onChange, ...fieldProps } = field;
                    const radioValue = value === true ? "true" : value === false ? "false" : "";
                    return (
                        <>
                            <RadioGroup
                                row
                                {...fieldProps}
                                value={radioValue}
                                onChange={(event) => onChange(event.target.value === "true")}
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