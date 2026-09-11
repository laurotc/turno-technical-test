import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { apiClient, clearStoredToken, getStoredToken, setAuthToken, storeToken } from '../api/client';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const navigate = useNavigate();
    const [user, setUser] = useState(null);
    const [isLoading, setIsLoading] = useState(true);

    const logout = useCallback(async () => {
        try {
            await apiClient.post('/logout');
        } catch (error) {
            if (error.response?.status !== 401) {
                throw error;
            }
        } finally {
            clearStoredToken();
            setUser(null);
            navigate('/login', { replace: true });
        }
    }, [navigate]);

    const applyAuthResponse = useCallback((payload) => {
        storeToken(payload.token);
        setUser(payload.user);
    }, []);

    useEffect(() => {
        const token = getStoredToken();

        if (!token) {
            setIsLoading(false);
            return;
        }

        setAuthToken(token);

        apiClient
            .get('/user')
            .then((response) => {
                setUser(response.data.user);
            })
            .catch((error) => {
                if (error.response?.status === 401) {
                    clearStoredToken();
                    setUser(null);
                }
            })
            .finally(() => {
                setIsLoading(false);
            });
    }, []);

    useEffect(() => {
        const interceptor = apiClient.interceptors.response.use(
            (response) => response,
            (error) => {
                if (error.response?.status === 401) {
                    clearStoredToken();
                    setUser(null);
                    navigate('/login', { replace: true });
                }

                return Promise.reject(error);
            },
        );

        return () => {
            apiClient.interceptors.response.eject(interceptor);
        };
    }, [navigate]);

    const value = useMemo(
        () => ({
            user,
            isAuthenticated: Boolean(user),
            isLoading,
            applyAuthResponse,
            logout,
        }),
        [applyAuthResponse, isLoading, logout, user],
    );

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error('useAuth must be used within AuthProvider.');
    }

    return context;
}
