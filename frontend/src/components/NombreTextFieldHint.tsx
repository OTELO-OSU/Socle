import { Controller, useFormContext } from "react-hook-form";
import LayoutLabelHintInput from "./LayoutLabelHintInput";
import { TextField } from "@mui/material";

export default function NombreTextFieldHint({ label, name, helperText, labelTF, ...props }: { label: string; name: string; helperText: string; labelTF?: string } & Omit<React.ComponentProps<typeof TextField>, "name" | "label" | "helperText">) {
    const { control, } = useFormContext();
    return (
        <LayoutLabelHintInput label={label} helperText={helperText}>
            <Controller
                name={name}
                control={control}
                render={({ field }) => (
                    <TextField
                        {...field}
                        size="small"
                        fullWidth
                        label={labelTF || "Nombre"}
                        type="text"
                        inputMode="decimal"
                        value={field.value ?? ""}

                        onChange={(e) => {
                            let v = e.target.value;

                            // nettoyage UI immédiat
                            v = v.replace(/[^0-9.]/g, "");
                            v = v.replace(/(\..*)\./g, "$1");

                            field.onChange(v === "" ? null : v);
                        }}
                        {...props}
                    />
                )}
            />
        </LayoutLabelHintInput>
    );
}