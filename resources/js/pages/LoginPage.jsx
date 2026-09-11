import { useState } from 'react';
import { Link, Navigate, useLocation, useNavigate } from 'react-router-dom';
import { apiClient } from '../api/client';
import { useAuth } from '../auth/AuthContext';

export function LoginPage() {
    const navigate = useNavigate();
    const location = useLocation();
    const { applyAuthResponse, isAuthenticated } = useAuth();
    const [form, setForm] = useState({
        email: '',
        password: '',
    });
    const [errors, setErrors] = useState({});
    const [message, setMessage] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    if (isAuthenticated) {
        return <Navigate to="/labels" replace />;
    }

    const updateField = (event) => {
        setForm((current) => ({
            ...current,
            [event.target.name]: event.target.value,
        }));
    };

    const submitForm = async (event) => {
        event.preventDefault();
        setErrors({});
        setMessage('');
        setIsSubmitting(true);

        try {
            const response = await apiClient.post('/login', form);
            applyAuthResponse(response.data);
            navigate(location.state?.from?.pathname || '/labels', { replace: true });
        } catch (error) {
            setErrors(error.response?.data?.errors || {});
            setMessage(error.response?.data?.message || 'Unable to log in.');
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <main className="auth-shell">
            <section className="auth-panel">
                <p className="eyebrow">Welcome back</p>
                <h1>Login</h1>
                <p className="muted">Sign in to manage USPS shipping labels.</p>

                {message ? <div className="alert">{message}</div> : null}

                <form className="form-stack" onSubmit={submitForm}>
                    <div className="field">
                        <label htmlFor="email">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autoComplete="email"
                            value={form.email}
                            onChange={updateField}
                            required
                        />
                        <FieldError errors={errors.email} />
                    </div>

                    <div className="field">
                        <label htmlFor="password">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autoComplete="current-password"
                            value={form.password}
                            onChange={updateField}
                            required
                        />
                        <FieldError errors={errors.password} />
                    </div>

                    <button type="submit" className="button primary full-width" disabled={isSubmitting}>
                        {isSubmitting ? 'Logging in...' : 'Log in'}
                    </button>
                </form>

                <p className="auth-switch">
                    New here? <Link to="/register">Create an account</Link>
                </p>
            </section>
        </main>
    );
}

function FieldError({ errors }) {
    if (!errors?.length) {
        return null;
    }

    return <p className="field-error">{errors[0]}</p>;
}
