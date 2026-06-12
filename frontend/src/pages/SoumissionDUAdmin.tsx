import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { fetchUtilisateurEnAttenteDuDU, gererUtilisateurEnAttenteDU, type UtilisateurEnAttente } from "../services/adminService";
import { IconButton, Table, TableBody, TableCell, TableHead, TableRow } from "@mui/material";
import CheckIcon from '@mui/icons-material/Check';
import ClearIcon from '@mui/icons-material/Clear';
import { useContext } from "react";
import { SnackbarContext } from "../context/snackbarContext";
import { AdminPageShell, AdminSurface } from "../components/admin/AdminPageShell";

export default function SoumissionDUAdmin() {
    const snackbarStore = useContext(SnackbarContext);
    const queryClient = useQueryClient();
    const { data: utilisateurs } = useQuery({
        queryKey: ['utilisateurs', 'en-attente', 'du'],
        queryFn: fetchUtilisateurEnAttenteDuDU
    });

    const approveMutation = useMutation({
        mutationFn: (id: number) => gererUtilisateurEnAttenteDU(id, true),
        onSuccess: ({ message }, id) => {
            snackbarStore?.trigger({
                message: message,
                couleur: "success",
            });
            queryClient.setQueryData<UtilisateurEnAttente[]>(["utilisateurs", "en-attente", "du"], (old = []) =>
                old.filter((u) => u.id !== id)
            );
        },
    });

    const rejectMutation = useMutation({
        mutationFn: (id: number) => gererUtilisateurEnAttenteDU(id, false),
        onSuccess: ({ message }, id) => {
            snackbarStore?.trigger({
                message: message,
                couleur: "success",
            });
            queryClient.setQueryData<UtilisateurEnAttente[]>(["utilisateurs", "en-attente", "du"], (old = []) =>
                old.filter((u) => u.id !== id)
            );
        },
    });

    return (
        <AdminPageShell
            title="Soumissions DU"
            subtitle="Gère les demandes en attente de validation par direction d'unité."
        >
            <AdminSurface>
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableCell>Nom</TableCell>
                            <TableCell>Prénom</TableCell>
                            <TableCell>Email</TableCell>
                            <TableCell sx={{ textAlign: 'center' }}>Action</TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {utilisateurs?.map((utilisateur) => (
                            <TableRow key={utilisateur.email}>
                                <TableCell>{utilisateur.nom}</TableCell>
                                <TableCell>{utilisateur.prenom}</TableCell>
                                <TableCell>{utilisateur.email}</TableCell>
                                <TableCell sx={{ textAlign: 'center' }}>
                                    <IconButton onClick={() => approveMutation.mutate(utilisateur.id)}><CheckIcon color="success" /></IconButton>
                                    <IconButton onClick={() => rejectMutation.mutate(utilisateur.id)}><ClearIcon color="error" /></IconButton>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </AdminSurface>
        </AdminPageShell>
    )
}