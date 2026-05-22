import { FormControl, Grid, Typography } from "@mui/material";

export default function LayoutLabelHintInput({ label, helperText, children }: { label: string; helperText: string, children?: React.ReactNode }) {
    return (
        <FormControl fullWidth sx={{ mt: 2 }}>
            <Grid container spacing={2}>
                <Grid size={{ xs: 9 }} sx={{ backgroundColor: "whitesmoke", alignItems: 'center', display: 'flex', px: 1 }}>
                    <Typography variant="body1" component="p">
                        {label}
                    </Typography>
                </Grid>
                <Grid size={{ xs: 3 }}>
                    {children}
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