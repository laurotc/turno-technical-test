import { Navigate, Route, Routes } from 'react-router-dom';
import { ProtectedRoute } from './auth/ProtectedRoute';
import { AppLayout } from './layouts/AppLayout';
import { CreateLabelPage } from './pages/CreateLabelPage';
import { LabelDetailPage } from './pages/LabelDetailPage';
import { LabelsPage } from './pages/LabelsPage';
import { LoginPage } from './pages/LoginPage';
import { RegisterPage } from './pages/RegisterPage';

export function RootApp() {
    return (
        <Routes>
            <Route path="/" element={<Navigate to="/labels" replace />} />
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            <Route
                path="/labels"
                element={
                    <ProtectedRoute>
                        <AppLayout />
                    </ProtectedRoute>
                }
            >
                <Route index element={<LabelsPage />} />
                <Route path="create" element={<CreateLabelPage />} />
                <Route path=":id" element={<LabelDetailPage />} />
            </Route>
            <Route path="*" element={<Navigate to="/labels" replace />} />
        </Routes>
    );
}
