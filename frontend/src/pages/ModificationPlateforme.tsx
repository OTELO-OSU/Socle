import { Box, Button, Card, Container, FormControl, FormLabel, MenuItem, Select, Tab, Tabs, TextField, Typography, type SelectProps, type TextFieldProps } from "@mui/material";
import { useState } from "react";
import { useForm, Controller, type FieldError, type RegisterOptions, type FieldValues, type Control, FormProvider } from "react-hook-form";
import { Presentation } from "../components/formulairePlateforme/Presentation";


type RHFTextFieldProps = {
    name: string;
    label: string;
    control: Control<FieldValues>;
    rules?: RegisterOptions;
} & TextFieldProps;

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

export function RHFTextField({ name, label, control, rules, ...props }: RHFTextFieldProps) {
    return (
        <FormControl fullWidth sx={{ mt: 2 }}>
            <Controller
                name={name}
                control={control}
                rules={rules}
                defaultValue=""
                render={({ field, fieldState }) => (
                    <>
                        <FormLabel error={!!fieldState.error} htmlFor={name}>
                            {label}
                        </FormLabel>
                        <TextField
                            {...field}
                            {...props}
                            id={name}
                            error={!!fieldState.error}
                            helperText={fieldState.error?.message}
                        />
                    </>
                )}
            />
        </FormControl>
    );
}

export function RHFSelect({ name, label, control, rules, items, ...props }: RHFSelectProps) {
    const isMultiple = props.multiple === true;
    return (
        <FormControl fullWidth sx={{ mt: 2 }}>
            <Controller
                name={name}
                control={control}
                rules={rules}
                defaultValue={isMultiple ? [] : ""}
                render={({ field, fieldState }) => (
                    <>
                        <FormLabel error={!!fieldState.error} htmlFor={name}>
                            {label}
                        </FormLabel>
                        <Select
                            {...field}
                            {...props}
                            error={!!fieldState.error}
                        >
                            {items?.map((item) => (
                                <MenuItem key={item.value} value={item.value}>
                                    {item.label}
                                </MenuItem>
                            ))}
                        </Select>
                    </>
                )}
            />
        </FormControl>
    );
}


function a11yProps(index: number) {
    return {
        id: `simple-tab-${index}`,
        'aria-controls': `simple-tabpanel-${index}`,
    };
}

interface TabPanelProps {
    children?: React.ReactNode;
    index: number;
    value: number;
}

function CustomTabPanel(props: TabPanelProps) {
    const { children, value, index, ...other } = props;

    return (
        <div
            role="tabpanel"
            hidden={value !== index}
            id={`simple-tabpanel-${index}`}
            aria-labelledby={`simple-tab-${index}`}
            {...other}
        >
            {value === index && <>{children}</>}
        </div>
    );
}

export default function ModificationPlateforme() {
    const methods = useForm<FieldValues>();
    const { handleSubmit, formState: { errors } } = methods;

    function onSubmit(data: FieldValues) {
        console.log(data);
    }

    const [value, setValue] = useState(0);

    const handleChange = (event: React.SyntheticEvent, newValue: number) => {
        setValue(newValue);
    };

    return (
        <Container sx={{ py: 4 }}>
            <FormProvider {...methods}>
                <form noValidate onSubmit={handleSubmit(onSubmit)}>
                    <Card sx={{ display: "flex", flexDirection: "column", p: 4 }}>
                        <Typography variant="h4" component="h1">
                            Modifier une Plateforme
                        </Typography>
                        <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
                            <Tabs value={value} onChange={handleChange} aria-label="basic tabs example">
                                <Tab label="Présentation" {...a11yProps(0)} />
                                <Tab label="Item Two" {...a11yProps(1)} />
                                <Tab label="Item Three" {...a11yProps(2)} />
                            </Tabs>
                        </Box>
                        <CustomTabPanel value={value} index={0}>
                            <Presentation />
                        </CustomTabPanel>


                    </Card>
                    <Button variant="contained" color="primary" sx={{ mt: 2 }} type="submit">
                        Enregistrer les modifications
                    </Button>
                </form>
                <ul>
                    {Object.entries(errors).map(([field, error]) => {
                        const err = error as FieldError;
                        return <li key={field}>{err.message}</li>
                    })}
                </ul>
            </FormProvider>
        </Container >
    )
}