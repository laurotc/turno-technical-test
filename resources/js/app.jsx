import '../css/app.css';
import './bootstrap';

import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { AuthProvider } from './auth/AuthContext';
import { RootApp } from './RootApp';

createRoot(document.getElementById('root')).render(
    <StrictMode>
        <BrowserRouter>
            <AuthProvider>
                <RootApp />
            </AuthProvider>
        </BrowserRouter>
    </StrictMode>,
);
