import { Link } from 'react-router-dom';

export function RegisterPage() {
    return (
        <main className="auth-shell">
            <section className="auth-panel">
                <p className="eyebrow">New account</p>
                <h1>Register</h1>
                <p className="muted">The registration form will be added in the next step.</p>
                <Link to="/login">Already have an account?</Link>
            </section>
        </main>
    );
}
