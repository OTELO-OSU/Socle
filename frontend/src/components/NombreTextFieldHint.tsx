import LayoutLabelHintInput from "./LayoutLabelHintInput";
import { TextField } from "@mui/material";
import NombreTextField from "./NombreTextField";

export default function NombreTextFieldHint({ label, name, helperText, labelTF, ...props }: { label: string; name: string; helperText: string; labelTF?: string } & Omit<React.ComponentProps<typeof TextField>, "name" | "label" | "helperText">) {
    return (
        <LayoutLabelHintInput label={label} helperText={helperText}>
            <NombreTextField name={name} labelTF={labelTF} {...props} />
        </LayoutLabelHintInput>
    );
}