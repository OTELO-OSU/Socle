import { TextField, type TextFieldProps } from "@mui/material";
import { Controller, type Control, type FieldValues, type RegisterOptions } from "react-hook-form";

type RHFTextFieldProps = {
    name: string;
    label: string;
    control: Control<FieldValues>;
    rules?: RegisterOptions;
} & TextFieldProps;

export function RHFTextField({ name, label, control, rules, ...props }: RHFTextFieldProps) {
    return (
        <Controller
            name={name}
            control={control}
            rules={rules}
            defaultValue=""
            render={({ field, fieldState }) => (
                <TextField
                    fullWidth
                    {...field}
                    {...props}
                    id={name}
                    error={!!fieldState.error}
                    helperText={fieldState.error?.message}
                    label={label}
                />
            )}
        />
    );
}