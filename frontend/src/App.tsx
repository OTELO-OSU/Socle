import { Route, Routes } from 'react-router-dom'
import Home from './pages/Home'
import HeaderAppBar from './components/HeaderAppBar'
import Connexion from './pages/Connexion'
import { Alert, Box, Snackbar } from '@mui/material'
import { useContext } from 'react'
import { SnackbarContext } from './context/snackbarContext'
import OrcidCallback from './pages/OrcidCallback'
import Inscription from './pages/Inscription'
import VerificationMailCallback from './pages/VerificationMailCallback'

function App() {
  const snackbarContext = useContext(SnackbarContext);
  return (

    <>
      <HeaderAppBar />
      <Box>
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
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/connexion" element={<Connexion />} />
          <Route path="/inscription" element={<Inscription />} />
          <Route path="/orcid" element={<OrcidCallback />} />
          <Route path="/verification-email" element={<VerificationMailCallback />} />
        </Routes>
      </Box >
    </>
  )
}

export default App
