import { FormControl, InputLabel, MenuItem, Select, type SelectProps } from "@mui/material";
import { Controller, type Control, type FieldValues, type Path, type RegisterOptions } from "react-hook-form";

type RHFSelectProps<TFieldValues extends FieldValues = FieldValues> = {
    name: Path<TFieldValues>;
    label: string;
    control: Control<TFieldValues>;
    items?: {
        value: string | number;
        label: string;
    }[];
    rules?: RegisterOptions<TFieldValues, Path<TFieldValues>>;
} & SelectProps;

export function RHFSelect<TFieldValues extends FieldValues = FieldValues>({
    name,
    label,
    control,
    rules,
    items,
    ...props
}: RHFSelectProps<TFieldValues>) {
    const isMultiple = props.multiple === true;
    const labelId = `${name}-label`;
    const { sx, ...selectProps } = props;
    return (
        <Controller
            name={name}
            control={control}
            rules={rules}
            defaultValue={(isMultiple ? [] : "") as never}
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