import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom';
import './index.css'
import App from './App'
import { AuthProvider } from './provider/AuthProvider'
import { SnackbarProvider } from './provider/SnackbarProvider';
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { DictionnaireProvider } from './provider/DictionnaireProvider';
import { createTheme, ThemeProvider } from '@mui/material';

const queryClient = new QueryClient();

const theme = createTheme({
  palette: {
    primary: {
      main: '#ba272c',
    },
    secondary: {
      main: '#49b6ff',
    },
  },
  typography: {
    fontFamily: 'Roboto, sans-serif',
  },
});

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <BrowserRouter>
      <QueryClientProvider client={queryClient}>
        <AuthProvider>
          <DictionnaireProvider>
            <ThemeProvider theme={theme}>
              <SnackbarProvider>
                <App />
              </SnackbarProvider>
            </ThemeProvider>
          </DictionnaireProvider>
        </AuthProvider>
      </QueryClientProvider>
    </BrowserRouter>
  </StrictMode>,
)
