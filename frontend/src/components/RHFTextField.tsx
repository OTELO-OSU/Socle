import { TextField, type TextFieldProps } from "@mui/material";
import { Controller, type Control, type FieldValues, type Path, type RegisterOptions } from "react-hook-form";

type RHFTextFieldProps<TFieldValues extends FieldValues = FieldValues> = {
    name: Path<TFieldValues>;
    label: string;
    control: Control<TFieldValues>;
    rules?: RegisterOptions<TFieldValues, Path<TFieldValues>>;
} & TextFieldProps;

export function RHFTextField<TFieldValues extends FieldValues = FieldValues>({
    name,
    label,
    control,
    rules,
    ...props
}: RHFTextFieldProps<TFieldValues>) {
    return (
        <Controller
            name={name}
            control={control}
            rules={rules}
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