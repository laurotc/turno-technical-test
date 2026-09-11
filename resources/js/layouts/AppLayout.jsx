import { LogOut, PackagePlus } from 'lucide-react';
import { NavLink, Outlet } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';

export function AppLayout() {
    const { logout, user } = useAuth();

    return (
        <div className="app-frame">
            <header className="app-header">
                <div>
                    <p className="eyebrow">Turno Technical Test</p>
                    <h1>Shipping Labels</h1>
                </div>

                <nav className="app-nav" aria-label="Primary navigation">
                    <NavLink to="/labels" end>
                        Labels
                    </NavLink>
                    <NavLink to="/labels/create">
                        <PackagePlus size={16} aria-hidden="true" />
                        Create
                    </NavLink>
                </nav>

                <div className="user-actions">
                    <span>{user?.name}</span>
                    <button type="button" className="icon-button" onClick={logout} title="Log out">
                        <LogOut size={18} aria-hidden="true" />
                    </button>
                </div>
            </header>

            <main className="content-shell">
                <Outlet />
            </main>
        </div>
    );
}
