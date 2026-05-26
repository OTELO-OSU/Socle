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
            render={({ field, fieldState }) => {
                const hasValue = field.value !== undefined && field.value !== null && field.value !== "";

                return (
                    <TextField
                        fullWidth
                        {...field}
                        {...props}
                        value={field.value ?? ""}
                        id={name}
                        error={!!fieldState.error}
                        helperText={fieldState.error?.message}
                        label={label}
                        slotProps={{
                            ...props.slotProps,
                            inputLabel: { shrink: hasValue },
                        }}
                    />
                );
            }}
        />
    );
}