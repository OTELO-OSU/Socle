import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom';
import './index.css'
import App from './App'
import { AuthProvider } from './provider/AuthProvider'
import { SnackbarProvider } from './provider/SnackbarProvider';

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <BrowserRouter>
      <AuthProvider>
        <SnackbarProvider>
          <App />
        </SnackbarProvider>
      </AuthProvider>
    </BrowserRouter>
  </StrictMode>,
)
