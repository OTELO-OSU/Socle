import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { approuverUtilisateur, fetchUtilisateurEnAttente, refuserUtilisateur, type UtilisateurEnAttente } from "../services/adminService";
import { IconButton, Paper, Table, TableBody, TableCell, TableContainer, TableHead, TableRow } from "@mui/material";
import CheckIcon from '@mui/icons-material/Check';
import ClearIcon from '@mui/icons-material/Clear';
import { useContext } from "react";
import { SnackbarContext } from "../context/snackbarContext";

export default function AdhesionAdmin() {
    const snackbarStore = useContext(SnackbarContext);
    const queryClient = useQueryClient();
    const { data: utilisateurs } = useQuery({
        queryKey: ['utilisateurs', 'en-attente'],
        queryFn: fetchUtilisateurEnAttente
    });

    const approveMutation = useMutation({
        mutationFn: approuverUtilisateur,
        onSuccess: (_, email) => {
            snackbarStore?.trigger({
                message: _.message,
                couleur: "success",
            });
            queryClient.setQueryData<UtilisateurEnAttente[]>(["utilisateurs", "en-attente"], (old = []) =>
                old.filter((u) => u.email !== email)
            );
        },
    });

    const rejectMutation = useMutation({
        mutationFn: refuserUtilisateur,
        onSuccess: (_, email) => {
            snackbarStore?.trigger({
                message: _.message,
                couleur: "success",
            });
            queryClient.setQueryData<UtilisateurEnAttente[]>(["utilisateurs", "en-attente"], (old = []) =>
                old.filter((u) => u.email !== email)
            );
        },
    });

    return (
        <TableContainer component={Paper}>
            <Table>
                <TableHead>
                    <TableRow>
                        <TableCell>Nom</TableCell>
                        <TableCell>Prénom</TableCell>
                        <TableCell>Email</TableCell>
                        <TableCell>Plateforme demandée</TableCell>
                        <TableCell>Unité demandée</TableCell>
                        <TableCell sx={{ textAlign: 'center' }}>OSU demandé</TableCell>
                        <TableCell sx={{ textAlign: 'center' }}>Action</TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {utilisateurs?.map((utilisateur) => (
                        <TableRow key={utilisateur.email}>
                            <TableCell>{utilisateur.nom}</TableCell>
                            <TableCell>{utilisateur.prenom}</TableCell>
                            <TableCell>{utilisateur.email}</TableCell>
                            <TableCell>{utilisateur.plateformeDemande}</TableCell>
                            <TableCell>{utilisateur.uniteDemande}</TableCell>
                            <TableCell sx={{ textAlign: 'center' }}>{utilisateur.osuDemande ? 'Oui' : 'Non'}</TableCell>
                            <TableCell sx={{ textAlign: 'center' }}>
                                <IconButton onClick={() => approveMutation.mutate(utilisateur.email)}><CheckIcon color="success" /></IconButton>
                                <IconButton onClick={() => rejectMutation.mutate(utilisateur.email)}><ClearIcon color="error" /></IconButton>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </TableContainer>
    )
}