import { Box, Button, Card, Container, Tab, Tabs, Typography } from "@mui/material";
import { useState } from "react";
import { useForm, type FieldError, type FieldValues, FormProvider } from "react-hook-form";
import { Presentation } from "../components/formulairePlateforme/Presentation";
import { CustomTabPanel } from "../components/CustomTabPanel";
import { allyProps } from "../components/formulairePlateforme/allyProps";
import { Personnels } from "../components/formulairePlateforme/Personnels";

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
                                <Tab label="Présentation" {...allyProps(0)} />
                                <Tab label="Personnels" {...allyProps(1)} />
                                <Tab label="Item Three" {...allyProps(2)} />
                            </Tabs>
                        </Box>
                        <CustomTabPanel value={value} index={0}>
                            <Presentation />
                        </CustomTabPanel>
                        <CustomTabPanel value={value} index={1}>
                            <Personnels />
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