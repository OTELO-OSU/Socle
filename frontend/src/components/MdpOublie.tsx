import Button from '@mui/material/Button';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogContentText from '@mui/material/DialogContentText';
import DialogTitle from '@mui/material/DialogTitle';
import OutlinedInput from '@mui/material/OutlinedInput';
import { motDePasseOubliePhase1 } from '../services/authService';
import { useContext } from 'react';
import { SnackbarContext } from '../context/snackbarContext';

interface MdpOublieProps {
    open: boolean;
    handleClose: () => void;
}

export default function MdpOublie({ open, handleClose }: MdpOublieProps) {

    const snackbar = useContext(SnackbarContext);
    async function envoyerMailResetPassword(email: string) {
        const data = await motDePasseOubliePhase1(email);

        if (data) {
            snackbar?.trigger({
                message: data.message,
                couleur: 'success',
            });
        }
    };

    return (
        <Dialog
            open={open}
            onClose={handleClose}
            slotProps={{
                paper: {
                    component: 'form',
                    onSubmit: async (event: React.SubmitEvent<HTMLFormElement>) => {
                        event.preventDefault();
                        event.stopPropagation();

                        const data = new FormData(event.currentTarget);
                        const email = data.get('email') as string;

                        await envoyerMailResetPassword(email);
                        handleClose();
                    },
                    sx: { backgroundImage: 'none' },
                },
            }}
        >
            <DialogTitle>Réinitialiser le mot de passe</DialogTitle>
            <DialogContent
                sx={{ display: 'flex', flexDirection: 'column', gap: 2, width: '100%' }}
            >
                <DialogContentText>
                    Entrez votre adresse email pour recevoir un lien de réinitialisation de mot de passe.
                </DialogContentText>
                <OutlinedInput
                    autoFocus
                    required
                    margin="dense"
                    id="email"
                    name="email"
                    placeholder="Adresse email"
                    type="email"
                    fullWidth
                />
            </DialogContent>
            <DialogActions sx={{ pb: 3, px: 3 }}>
                <Button onClick={handleClose}>Cancel</Button>
                <Button variant="contained" type="submit">
                    Continue
                </Button>
            </DialogActions>
        </Dialog>
    );
}