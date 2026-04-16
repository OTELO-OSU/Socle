import { Box, List, ListItem, ListItemText, Divider, ListItemButton } from '@mui/material';
import { NavLink, Outlet } from 'react-router-dom';

const drawerWidth = 240;

export default function AdminLayout() {
    return (
        <Box sx={{ display: 'flex', height: '100%' }}>
            {/* Drawer */}
            <Box
                sx={{
                    height: "100%",
                    width: drawerWidth,
                }}
            >
                <List>
                    <ListItem disablePadding>
                        <ListItemButton sx={{
                            '&.active': {
                                bgcolor: 'action.selected',
                                borderLeft: '3px solid',
                                borderColor: 'primary.main',
                            },
                        }} component={NavLink} to="/admin">
                            <ListItemText primary="Plateforme" />
                        </ListItemButton>
                    </ListItem>

                    <ListItem disablePadding>
                        <ListItemButton sx={{
                            '&.active': {
                                bgcolor: 'action.selected',
                                borderLeft: '3px solid',
                                borderColor: 'primary.main',
                            },
                        }} component={NavLink} to="/admin/users">
                            <ListItemText primary="Users" />
                        </ListItemButton>
                    </ListItem>

                    <ListItem disablePadding>
                        <ListItemButton sx={{
                            '&.active': {
                                bgcolor: 'action.selected',
                                borderLeft: '3px solid',
                                borderColor: 'primary.main',
                            },
                        }} component={NavLink} to="/admin/settings">
                            <ListItemText primary="Settings" />
                        </ListItemButton>
                    </ListItem>
                </List>
            </Box>

            <Divider orientation="vertical" flexItem />

            {/* Main content */}
            <Box
                component="main"
                sx={{
                    flexGrow: 1,
                    p: 3,
                }}
            >
                <Outlet />
            </Box>

        </Box>
    );
}