import { useEffect, useState } from "react";
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
            render={({ field, fieldState }) => {
                const [displayValue, setDisplayValue] = useState("");

                useEffect(() => {
                    setDisplayValue(field.value?.toString() ?? "");
                }, [field.value]);

                return (
                    <TextField
                        size="small"
                        fullWidth
                        label={label ?? labelTF ?? "Nombre"}
                        type="text"
                        inputMode="decimal"
                        value={displayValue}
                        error={!!fieldState.error}
                        helperText={fieldState.error?.message}
                        onBlur={field.onBlur}
                        inputRef={field.ref}
                        name={field.name}
                        onChange={(e) => {
                            let v = e.target.value;

                            // Chiffres + un seul point
                            v = v.replace(/[^0-9.]/g, "");
                            v = v.replace(/(\..*)\./g, "$1");

                            setDisplayValue(v);

                            if (v === "") {
                                field.onChange(null);
                                return;
                            }

                            // On ne met à jour RHF que si c'est un nombre "terminé"
                            if (!v.endsWith(".")) {
                                const n = Number(v);
                                if (!Number.isNaN(n)) {
                                    field.onChange(n);
                                }
                            }
                        }}
                        {...restProps}
                    />
                );
            }}
        />
    );
}