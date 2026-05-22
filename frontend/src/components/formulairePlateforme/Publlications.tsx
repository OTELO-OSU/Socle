import { Box, Button, IconButton, Stack, TextField, Typography } from "@mui/material";
import NombreTextFieldHint from "../NombreTextFieldHint";
import { useFieldArray, useFormContext } from "react-hook-form";
import type { FormValues } from "../../pages/ModificationPlateforme";
import DeleteIcon from "@mui/icons-material/Delete";

function PublicationRow({ numero, onDelete }: { numero: number; onDelete: () => void }) {
    const { register } = useFormContext<FormValues>();
    return (
        <Box sx={{ display: "flex", alignItems: "center", gap: 2, mb: 1 }}>
            <Typography variant="body1">{numero}</Typography>
            <TextField
                label="DOI"
                fullWidth
                {...register(`publications.${numero - 1}.doi`)}
            />
            <IconButton type="button" onClick={onDelete}>
                <DeleteIcon />
            </IconButton>
        </Box>
    );
}

export function Publications() {
    const { control } = useFormContext<FormValues>();
    const { fields, replace, remove } = useFieldArray({
        control,
        name: "publications",
    });

    return (
        <Box sx={{ mt: 2 }}>
            <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold", mt: 2 }}>
                Publications de l'année dans des revues à comité de lecture
            </Typography>
            <NombreTextFieldHint label="Directement par l’équipe opérante : publication portée (1 er auteur) par un membre de la plateforme" name="publication_equipe_operante" helperText="" />
            <NombreTextFieldHint label="En collaboration interne : publication portée (1 er auteur) par quelqu’un d’autre du laboratoire en collaboration avec la plateforme" name="publication_collaboration_interne" helperText="" />
            <NombreTextFieldHint label="En collaboration externe: publication portée (1 er auteur) par quelqu’un d’un autre laboratoire en collaboration avec la plateforme" name="publication_collaboration_externe" helperText="" />
            <NombreTextFieldHint label="Autres types de publications (rapports, abstract congrès...)" name="publication_autre_type" helperText="" />
            <NombreTextFieldHint label="Les thèses rattachées à la plateforme et soutenues cette année" name="publication_these" helperText="" />
            <Stack direction="row" sx={{ mt: 4, mb: 2, justifyContent: "space-between" }}>
                <Typography variant="subtitle1" component="h2" sx={{ fontWeight: "bold" }}>
                    Publications de l'année dans des revues à comité de lecture
                </Typography>
                <Button variant="contained" color="primary" size="small" onClick={() => fields.length < 10 && replace([...fields, { numero: fields.length + 1, doi: "" }])}>
                    Ajouter une publication
                </Button>
            </Stack>
            {fields.map((field, index) => (
                <PublicationRow
                    key={field.id}
                    numero={index + 1}
                    onDelete={() => remove(index)}
                />
            ))}
            {fields.length === 0 && (
                <Typography variant="body2" color="textSecondary">
                    Aucune publication ajoutée.
                </Typography>
            )}
        </Box>
    )
}