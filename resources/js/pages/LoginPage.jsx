import { Link } from 'react-router-dom';

export function LoginPage() {
    return (
        <main className="auth-shell">
            <section className="auth-panel">
                <p className="eyebrow">Welcome back</p>
                <h1>Login</h1>
                <p className="muted">The login form will be added in the next step.</p>
                <Link to="/register">Create an account</Link>
            </section>
        </main>
    );
}
