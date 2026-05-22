import CoutTextFieldHint from "./CoutTextFieldHint";
import { useFormContext } from "react-hook-form";
import { RHFTextField } from "./RHFTextField";

export default function NombreTextFieldHint({ label, name, helperText }: { label: string; name: string; helperText: string }) {
    const { control } = useFormContext();
    return (
        <CoutTextFieldHint label={label} name={name} helperText={helperText}>
            <RHFTextField
                name={name}
                control={control}
                size="small"
                label={"Nombre"}
            />
        </CoutTextFieldHint >
    );
}