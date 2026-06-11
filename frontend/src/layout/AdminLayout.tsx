import { Box, List, ListItem, ListItemText, Divider, ListItemButton, ListItemIcon } from '@mui/material';
import { NavLink, Outlet } from 'react-router-dom';
import HowToRegIcon from '@mui/icons-material/HowToReg';
import HomeWork from '@mui/icons-material/HomeWork';
import { AccountCircle, CalendarMonth, Group, Hub, LocalOffer } from '@mui/icons-material';
import { useContext } from 'react';
import { AuthContext } from '../context/authContext';

const drawerWidth = 240;

export default function AdminLayout() {
    const authContext = useContext(AuthContext);
    return (
        <Box sx={{ display: 'flex', height: '100%', background: 'linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%)' }}>
            {/* Drawer */}
            <Box
                sx={{
                    height: "100%",
                    width: drawerWidth,
                    px: 1.5,
                    py: 2,
                }}
            >
                <List sx={{ border: '1px solid', borderColor: 'divider', borderRadius: 3, backgroundColor: 'background.paper', p: 1 }}>
                    {authContext?.estAdmin && (
                        <ListItem disablePadding>
                            <ListItemButton sx={{
                                borderRadius: 2,
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
                    )}

                    <ListItem disablePadding>
                        <ListItemButton sx={{
                            borderRadius: 2,
                            '&.active': {
                                bgcolor: 'action.selected',
                                borderLeft: '3px solid',
                                borderColor: 'primary.main',
                            },
                        }} component={NavLink} to="/admin/soumission-du">
                            <ListItemIcon>
                                <HowToRegIcon />
                            </ListItemIcon>
                            <ListItemText primary="Soumission DU" />
                        </ListItemButton>
                    </ListItem>

                    {authContext?.estAdmin && (
                        <>
                            <ListItem disablePadding>
                                <ListItemButton sx={{
                                    borderRadius: 2,
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
                        </>
                    )}

                    {authContext?.estAdmin && (
                        <>
                            <ListItem disablePadding>
                                <ListItemButton sx={{
                                    borderRadius: 2,
                                    '&.active': {
                                        bgcolor: 'action.selected',
                                        borderLeft: '3px solid',
                                        borderColor: 'primary.main',
                                    },
                                }} component={NavLink} to="/admin/tags" end>
                                    <ListItemIcon>
                                        <LocalOffer />
                                    </ListItemIcon>
                                    <ListItemText primary="Tags" />
                                </ListItemButton>
                            </ListItem>
                        </>
                    )}

                    {(authContext?.estAdmin || authContext?.estDu) && (
                        <ListItem disablePadding>
                            <ListItemButton sx={{
                                borderRadius: 2,
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
                    )}

                    {(authContext?.estAdmin || authContext?.estDu) && (
                        <>
                            <ListItem disablePadding>
                                <ListItemButton sx={{
                                    borderRadius: 2,
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
                        </>
                    )}

                    {authContext?.estAdmin && (
                        <>
                            <ListItem disablePadding>
                                <ListItemButton sx={{
                                    borderRadius: 2,
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
                                    borderRadius: 2,
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
                        </>
                    )}
                </List>
            </Box>

            <Divider orientation="vertical" flexItem />

            {/* Main content */}
            <Box
                sx={{
                    flexGrow: 1,
                    p: { xs: 2, md: 3 },
                }}
            >
                <Outlet />
            </Box>

        </Box >
    );
}