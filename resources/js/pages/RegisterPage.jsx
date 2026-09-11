import { useState } from 'react';
import { Link, Navigate, useNavigate } from 'react-router-dom';
import { apiClient } from '../api/client';
import { useAuth } from '../auth/AuthContext';

export function RegisterPage() {
    const navigate = useNavigate();
    const { applyAuthResponse, isAuthenticated } = useAuth();
    const [form, setForm] = useState({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
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
            const response = await apiClient.post('/register', form);
            applyAuthResponse(response.data);
            navigate('/labels', { replace: true });
        } catch (error) {
            setErrors(error.response?.data?.errors || {});
            setMessage(error.response?.data?.message || 'Unable to create account.');
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <main className="auth-shell">
            <section className="auth-panel">
                <p className="eyebrow">New account</p>
                <h1>Register</h1>
                <p className="muted">Create an account to generate and print labels.</p>

                {message ? <div className="alert">{message}</div> : null}

                <form className="form-stack" onSubmit={submitForm}>
                    <div className="field">
                        <label htmlFor="name">Name</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            autoComplete="name"
                            value={form.name}
                            onChange={updateField}
                            required
                        />
                        <FieldError errors={errors.name} />
                    </div>

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
                            autoComplete="new-password"
                            value={form.password}
                            onChange={updateField}
                            required
                        />
                        <FieldError errors={errors.password} />
                    </div>

                    <div className="field">
                        <label htmlFor="password_confirmation">Confirm password</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autoComplete="new-password"
                            value={form.password_confirmation}
                            onChange={updateField}
                            required
                        />
                        <FieldError errors={errors.password_confirmation} />
                    </div>

                    <button type="submit" className="button primary full-width" disabled={isSubmitting}>
                        {isSubmitting ? 'Creating account...' : 'Create account'}
                    </button>
                </form>

                <p className="auth-switch">
                    Already have an account? <Link to="/login">Log in</Link>
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
