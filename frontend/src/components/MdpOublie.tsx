import Button from '@mui/material/Button';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogContentText from '@mui/material/DialogContentText';
import DialogTitle from '@mui/material/DialogTitle';
import OutlinedInput from '@mui/material/OutlinedInput';

interface MdpOublieProps {
    open: boolean;
    handleClose: () => void;
}

function envoyerMailResetPassword(email: string) {
    console.log(email);
}

export default function MdpOublie({ open, handleClose }: MdpOublieProps) {
    return (
        <Dialog
            open={open}
            onClose={handleClose}
            slotProps={{
                paper: {
                    component: 'form',
                    onSubmit: (event: React.SubmitEvent<HTMLFormElement>) => {
                        event.preventDefault();

                        const data = new FormData(event.currentTarget);
                        const email = data.get('email') as string;

                        envoyerMailResetPassword(email);
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