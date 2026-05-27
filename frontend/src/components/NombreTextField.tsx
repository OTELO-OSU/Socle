import { TextField, type TextFieldProps } from "@mui/material";
import {
    Controller,
    type Control,
    type FieldValues,
    type Path,
    type RegisterOptions,
    useFormContext,
} from "react-hook-form";

type NombreTextFieldProps<TFieldValues extends FieldValues = FieldValues> = {
    name: Path<TFieldValues>;
    control?: Control<TFieldValues>;
    label?: string;
    labelTF?: string;
    rules?: RegisterOptions<TFieldValues, Path<TFieldValues>>;
} & Omit<TextFieldProps, "name" | "label">;

export default function NombreTextField<TFieldValues extends FieldValues = FieldValues>({
    name,
    control,
    label,
    labelTF,
    rules,
    ...restProps
}: NombreTextFieldProps<TFieldValues>) {
    const { control: contextControl } = useFormContext<TFieldValues>();
    return (
        <Controller
            name={name}
            control={control ?? contextControl}
            rules={rules}
            render={({ field, fieldState }) => (
                <TextField
                    {...field}
                    size="small"
                    fullWidth
                    label={label ?? labelTF ?? "Nombre"}
                    type="text"
                    inputMode="decimal"
                    value={field.value ?? ""}
                    error={!!fieldState.error}
                    helperText={fieldState.error?.message}

                    onChange={(e) => {
                        let v = e.target.value;

                        // nettoyage UI immédiat
                        v = v.replace(/[^0-9.]/g, "");
                        v = v.replace(/(\..*)\./g, "$1");

                        field.onChange(v === "" ? null : Number(v));
                    }}
                    {...restProps}
                />
            )}
        />
    );
}