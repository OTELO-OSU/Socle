import { Controller, useFormContext } from "react-hook-form";
import { TextField } from "@mui/material";

export default function NombreTextField({ name, labelTF, ...props }: { name: string; labelTF?: string } & Omit<React.ComponentProps<typeof TextField> & React.ComponentProps<typeof Controller>, "name" | "labelTF" | "render">) {
    const { control } = useFormContext();
    const { rules, ...restProps } = props;
    return (
        <Controller
            name={name}
            control={control}
            rules={rules}
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

                        field.onChange(v === "" ? null : Number(v));
                    }}
                    {...restProps}
                />
            )}
        />
    );
}