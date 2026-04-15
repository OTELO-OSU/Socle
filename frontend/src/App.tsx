import { Route, Routes } from 'react-router-dom'
import Home from './pages/Home'
import HeaderAppBar from './components/HeaderAppBar'
import Connexion from './pages/Connexion'
import { Alert, Box, Snackbar } from '@mui/material'
import { useContext } from 'react'
import { SnackbarContext } from './context/snackbarContext'

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
        </Routes>
      </Box >
    </>
  )
}

export default App
