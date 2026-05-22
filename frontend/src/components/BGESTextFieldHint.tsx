import CoutTextFieldHint from "./CoutTextFieldHint";
import { useFormContext } from "react-hook-form";
import { RHFTextField } from "./RHFTextField";

export default function BGESTextFieldHint({ label, name, helperText }: { label: string; name: string; helperText: string }) {
    const { control } = useFormContext();
    return (
        <CoutTextFieldHint label={label} name={name} helperText={helperText}>
            <RHFTextField
                name={name}
                control={control}
                size="small"
                label={"eCO2"}
                slotProps={{
                    input: {
                        endAdornment: "kg",
                    }
                }}
            />
        </CoutTextFieldHint >
    );
}