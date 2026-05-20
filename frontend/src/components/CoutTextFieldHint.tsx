import { FormControl, Grid, Typography } from "@mui/material";
import { useFormContext } from "react-hook-form";
import { RHFTextField } from "./RHFTextField";

export default function CoutTextFieldHint({ label, name, helperText, children }: { label: string; name: string; helperText: string, children?: React.ReactNode }) {
    const { control } = useFormContext();
    return (
        <FormControl fullWidth sx={{ mt: 2 }}>
            <Grid container spacing={2}>
                <Grid size={{ xs: 9 }} sx={{ backgroundColor: "whitesmoke", alignItems: 'center', display: 'flex', px: 1 }}>
                    <Typography variant="body1" component="p">
                        {label}
                    </Typography>
                </Grid>
                <Grid size={{ xs: 3 }}>
                    {children || (
                        <RHFTextField
                            name={name}
                            label="Coût"
                            control={control}
                            size="small"
                            slotProps={{
                                input: {
                                    endAdornment: "k€",
                                }
                            }}
                        />
                    )}
                </Grid>
            </Grid>
            <Grid container spacing={2}>
                <Grid size={{ xs: 9 }}>
                    <Typography variant="caption" component="p">
                        {helperText}
                    </Typography>
                </Grid>
            </Grid>
        </FormControl>
    );
}