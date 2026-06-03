import { Box, List, ListItem, ListItemText, Divider, ListItemButton, ListItemIcon } from '@mui/material';
import { NavLink, Outlet } from 'react-router-dom';
import HowToRegIcon from '@mui/icons-material/HowToReg';
import HomeWork from '@mui/icons-material/HomeWork';
import { AccountCircle, CalendarMonth, Group, Hub } from '@mui/icons-material';

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
                        }} component={NavLink} to="/admin/adhesion">
                            <ListItemIcon>
                                <HowToRegIcon />
                            </ListItemIcon>
                            <ListItemText primary="Demande d'adhesion" />
                        </ListItemButton>
                    </ListItem>

                    <ListItem disablePadding>
                        <ListItemButton sx={{
                            '&.active': {
                                bgcolor: 'action.selected',
                                borderLeft: '3px solid',
                                borderColor: 'primary.main',
                            },
                        }} component={NavLink} to="/admin/osu" end>
                            <ListItemIcon>
                                <Hub />
                            </ListItemIcon>
                            <ListItemText primary="OSU" />
                        </ListItemButton>
                    </ListItem>

                    <ListItem disablePadding>
                        <ListItemButton sx={{
                            '&.active': {
                                bgcolor: 'action.selected',
                                borderLeft: '3px solid',
                                borderColor: 'primary.main',
                            },
                        }} component={NavLink} to="/admin/plateforme" end>
                            <ListItemIcon>
                                <HomeWork />
                            </ListItemIcon>
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
                        }} component={NavLink} to="/admin/unite" end>
                            <ListItemIcon>
                                <Group />
                            </ListItemIcon>
                            <ListItemText primary="Unité" />
                        </ListItemButton>
                    </ListItem>

                    <ListItem disablePadding>
                        <ListItemButton sx={{
                            '&.active': {
                                bgcolor: 'action.selected',
                                borderLeft: '3px solid',
                                borderColor: 'primary.main',
                            },
                        }} component={NavLink} to="/admin/agent">
                            <ListItemIcon>
                                <AccountCircle />
                            </ListItemIcon>
                            <ListItemText primary="Agent" />
                        </ListItemButton>
                    </ListItem>

                    <ListItem disablePadding>
                        <ListItemButton sx={{
                            '&.active': {
                                bgcolor: 'action.selected',
                                borderLeft: '3px solid',
                                borderColor: 'primary.main',
                            },
                        }} component={NavLink} to="/admin/saisie">
                            <ListItemIcon>
                                <CalendarMonth />
                            </ListItemIcon>
                            <ListItemText primary="Année de saisie" />
                        </ListItemButton>
                    </ListItem>
                </List>
            </Box>

            <Divider orientation="vertical" flexItem />

            {/* Main content */}
            <Box
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