import { createContext, useContext, useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import * as authApi from '../services/api';
import { CartProvider } from './CartContext';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [customer, setCustomer] = useState(null);
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();

    useEffect(() => {
        checkAuth();
    }, []);

    const checkAuth = async () => {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            setLoading(false);
            return;
        }

        try {
            const customer_data = await authApi.getCurrentUser();
            if (customer_data && customer_data.id) {
                setCustomer(customer_data);
            } else {
                console.error('Invalid customer data received');
                localStorage.removeItem('auth_token');
            }
        } catch (error) {
            // Only remove token if it's a 401 (unauthorized)
            if (error.response?.status === 401) {
                console.error('Auth check failed: Token invalid or expired', error.response?.data);
                localStorage.removeItem('auth_token');
                setCustomer(null);
            } else {
                console.error('Auth check failed:', error);
                // Don't remove token for network errors or other issues
            }
        } finally {
            setLoading(false);
        }
    };

    const login = async (email, password) => {
        try {
            const data = await authApi.login({ email, password });
            setCustomer(data.customer);
            return data;
        } catch (error) {
            throw error;
        }
    };

    const register = async (formData) => {
        try {
            const data = await authApi.register(formData);
            setCustomer(data.customer);
            return data;
        } catch (error) {
            throw error;
        }
    };

    const logout = async () => {
        try {
            await authApi.logout();
        } catch (error) {
            console.error('Logout failed:', error);
            // Continue with logout even if API call fails
        } finally {
            setCustomer(null);
            localStorage.removeItem('auth_token');
            if (navigate) {
                navigate('/');
            } else {
                window.location.href = '/';
            }
        }
    };

    const value = {
        customer,
        loading,
        authenticated: !!customer,
        login,
        register,
        logout,
        checkAuth,
    };

    return (
        <AuthContext.Provider value={value}>
            <CartProvider>{children}</CartProvider>
        </AuthContext.Provider>
    );
}

export function useAuth() {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
}

