import { Navigate, Route, Routes } from 'react-router-dom'
import Home from './pages/Home'
import HeaderAppBar from './components/HeaderAppBar'
import Connexion from './pages/Connexion'
import { Alert, Box, CircularProgress, Snackbar, Toolbar } from '@mui/material'
import { useContext, useEffect } from 'react'
import { SnackbarContext } from './context/snackbarContext'
import OrcidCallback from './pages/OrcidCallback'
import Inscription from './pages/Inscription'
import VerificationMailCallback from './pages/VerificationMailCallback'
import AdminLayout from './layout/AdminLayout'
import PlateformeAdmin from './pages/PlateformeAdmin'
import AdhesionAdmin from './pages/AdhesionAdmin'
import { AuthContext } from './context/authContext'
import { useNavigate } from 'react-router-dom'
import { registerNavigator } from './bridge/navigationBridge'

function App() {
  const snackbarContext = useContext(SnackbarContext);
  const authContext = useContext(AuthContext);
  const navigate = useNavigate();

  useEffect(() => {
    registerNavigator((path: string) => {
      navigate(path);
    });

    return () => {
      registerNavigator(null);
    };
  }, [navigate]);

  return (

    <>
      <HeaderAppBar />
      <Box
        sx={{
          height: '100vh',
          display: 'flex',
          flexDirection: 'column',
        }}
      >
        <Toolbar />
        <Box component="main" sx={{ flex: 1, minHeight: 0 }}>
          {authContext?.isAuthLoading ? (
            <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100%' }}>
              <CircularProgress />
            </Box>
          ) : (
            <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/connexion" element={<Connexion />} />
              <Route path="/inscription" element={<Inscription />} />
              <Route path="/orcid" element={<OrcidCallback />} />
              <Route path="/verification-email" element={<VerificationMailCallback />} />
              <Route path="/admin" element={<AdminLayout />}>
                <Route index element={<Navigate to="adhesion" replace />} />
                <Route path="adhesion" element={<AdhesionAdmin />} />
                <Route path="plateforme" element={<PlateformeAdmin />} />
                <Route path="settings" element={<div>Admin Settings</div>} />
              </Route>
            </Routes>
          )}
        </Box>
        <Snackbar
          open={snackbarContext!.open}
          autoHideDuration={3000}
          anchorOrigin={{ vertical: 'top', horizontal: 'center' }}
          onClose={snackbarContext!.close}
        >
          <Alert severity={snackbarContext!.couleur}
            sx={{ width: '100%' }}
          >
            {snackbarContext?.message}
          </Alert>
        </Snackbar>
      </Box >
    </>
  )
}

export default App
