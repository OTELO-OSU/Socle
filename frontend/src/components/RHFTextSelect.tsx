import { FormControl, InputLabel, MenuItem, Select, type SelectProps } from "@mui/material";
import { Controller, type Control, type FieldValues, type RegisterOptions } from "react-hook-form";

type RHFSelectProps = {
    name: string;
    label: string;
    control: Control<FieldValues>;
    items?: {
        value: string | number;
        label: string;
    }[];
    rules?: RegisterOptions;
} & SelectProps;

export function RHFSelect({ name, label, control, rules, items, ...props }: RHFSelectProps) {
    const isMultiple = props.multiple === true;
    const labelId = `${name}-label`;
    const { sx, ...selectProps } = props;
    return (
        <Controller
            name={name}
            control={control}
            rules={rules}
            defaultValue={isMultiple ? [] : ""}
            render={({ field, fieldState }) => (
                <FormControl fullWidth error={!!fieldState.error} sx={sx}>
                    <InputLabel id={labelId}>{label}</InputLabel>
                    <Select
                        labelId={labelId}
                        id={name}
                        {...field}
                        {...selectProps}
                        error={!!fieldState.error}
                        label={label}
                    >
                        {items?.map((item) => (
                            <MenuItem key={item.value} value={item.value}>
                                {item.label}
                            </MenuItem>
                        ))}
                    </Select>
                </FormControl>
            )}
        />
    );
}