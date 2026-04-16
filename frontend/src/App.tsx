import { Route, Routes } from 'react-router-dom'
import Home from './pages/Home'
import HeaderAppBar from './components/HeaderAppBar'
import Connexion from './pages/Connexion'
import { Alert, Box, Snackbar, Toolbar } from '@mui/material'
import { useContext } from 'react'
import { SnackbarContext } from './context/snackbarContext'
import OrcidCallback from './pages/OrcidCallback'
import Inscription from './pages/Inscription'
import VerificationMailCallback from './pages/VerificationMailCallback'
import AdminLayout from './layout/AdminLayout'
import { Dashboard } from '@mui/icons-material'

function App() {
  const snackbarContext = useContext(SnackbarContext);
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
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/connexion" element={<Connexion />} />
            <Route path="/inscription" element={<Inscription />} />
            <Route path="/orcid" element={<OrcidCallback />} />
            <Route path="/verification-email" element={<VerificationMailCallback />} />
            <Route path="/admin" element={<AdminLayout />} >
              <Route path="dashboard" element={<Dashboard />} />
              {/* <Route path="users" element={<Users />} />
              <Route path="settings" element={<Settings />} /> */}
            </Route>
          </Routes>
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
