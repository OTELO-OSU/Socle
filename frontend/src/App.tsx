import { Navigate, Route, Routes } from 'react-router-dom'
import Home from './pages/Home'
import HeaderAppBar from './components/HeaderAppBar'
import Connexion from './pages/Connexion'
import { Alert, Box, CircularProgress, Link, Snackbar, Toolbar } from '@mui/material'
import { useContext, useEffect } from 'react'
import { SnackbarContext } from './context/snackbarContext'
import Inscription from './pages/Inscription'
import VerificationMailCallback from './pages/VerificationMailCallback'
import AdminLayout from './layout/AdminLayout'
import PlateformeAdmin from './pages/PlateformeAdmin'
import AdhesionAdmin from './pages/AdhesionAdmin'
import { AuthContext } from './context/authContext'
import { useNavigate } from 'react-router-dom'
import { registerNavigator } from './bridge/navigationBridge'
import ModificationPlateforme from './pages/ModificationPlateforme'
import { ListePlateforme } from './pages/ListePlateforme'
import AgentAdmin from './pages/AgentAdmin'
import UniteAdmin from './pages/UniteAdmin'
import OsuAdmin from './pages/OsuAdmin'
import SaisieAdmin from './pages/SaisieAdmin'
import MonCompte from './pages/MonCompte'
import SoumissionDUAdmin from './pages/SoumissionDUAdmin'
import TagsAdmin from './pages/TagsAdmin'
import HarvesterAdmin from './pages/HarvesterAdmin'
import FedeCallback from './pages/FedeCallback'
import Deconnexion from './pages/Deconnexion'
import ResetMdp from './pages/ResetMdp'
import ApplicationSettingsAdmin from './pages/ApplicationSettingsAdmin'
import { useAppVersion } from './hook/useAppVersion'

function App() {
  const snackbarContext = useContext(SnackbarContext);
  const authContext = useContext(AuthContext);
  const navigate = useNavigate();
  const { data, isLoading, isError } = useAppVersion();

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
        {authContext?.estAdmin && (
          <>
            {isLoading && <CircularProgress />}

            {isError && (
              <Alert severity="warning">
                Impossible de vérifier la disponibilité des mises à jour.
              </Alert>
            )}

            {data?.updateAvailable && (
              <Alert severity="info">
                Une nouvelle version de SIPSI est disponible :
                {' '}{data.latest}
                {' '}(version actuelle : {data.current})
                {' '}<Link href='https://github.com/OTELO-OSU/Socle/blob/production/docs/mise-a-jour.md' target='_blank' rel='noopener noreferrer'>Comment mettre à jour ?</Link>
              </Alert>
            )}
          </>
        )}
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
              <Route path="/callbackCas" element={<FedeCallback />} />
              <Route path="/verification-email" element={<VerificationMailCallback />} />
              <Route path="/compte" element={<MonCompte />} />
              <Route path="/deconnexion" element={<Deconnexion />} />
              <Route path="/reinitialisation-mdp" element={<ResetMdp />} />
              <Route path="/admin" element={<AdminLayout />}>
                <Route index element={<Navigate to="adhesion" replace />} />
                <Route path="adhesion" element={<AdhesionAdmin />} />
                <Route path="plateforme" element={<PlateformeAdmin />} />
                <Route path="agent" element={<AgentAdmin />} />
                <Route path="unite" element={<UniteAdmin />} />
                <Route path="osu" element={<OsuAdmin />} />
                <Route path="saisie" element={<SaisieAdmin />} />
                <Route path="soumission-du" element={<SoumissionDUAdmin />} />
                <Route path="tags" element={<TagsAdmin />} />
                <Route path="harvester" element={<HarvesterAdmin />} />
                <Route path="parametres" element={<ApplicationSettingsAdmin />} />
              </Route>
              <Route path="/plateforme" element={<ListePlateforme />} />
              <Route path="/plateforme/edition" element={<ModificationPlateforme />} />
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
      </Box>
    </>
  )
}

export default App
