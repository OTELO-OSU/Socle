import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom';
import './index.css'
import App from './App'
import { AuthProvider } from './provider/AuthProvider'
import { SnackbarProvider } from './provider/SnackbarProvider';
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { DictionnaireProvider } from './provider/DictionnaireProvider';

const queryClient = new QueryClient();

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <BrowserRouter>
      <QueryClientProvider client={queryClient}>
        <DictionnaireProvider>
          <AuthProvider>
            <SnackbarProvider>
              <App />
            </SnackbarProvider>
          </AuthProvider>
        </DictionnaireProvider>
      </QueryClientProvider>
    </BrowserRouter>
  </StrictMode>,
)
