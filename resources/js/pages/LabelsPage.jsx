import { ChevronLeft, ChevronRight, Eye, PackagePlus, Printer } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { apiClient } from '../api/client';
import { formatDate, labelPrintUrl } from '../utils/formatters';

export function LabelsPage() {
    const [labels, setLabels] = useState([]);
    const [meta, setMeta] = useState(null);
    const [page, setPage] = useState(1);
    const [isLoading, setIsLoading] = useState(true);
    const [message, setMessage] = useState('');

    useEffect(() => {
        let ignore = false;

        setIsLoading(true);
        setMessage('');

        apiClient
            .get('/labels', {
                params: {
                    page,
                    per_page: 10,
                },
            })
            .then((response) => {
                if (ignore) {
                    return;
                }

                setLabels(response.data.data || []);
                setMeta(response.data.meta || null);
            })
            .catch((error) => {
                if (!ignore) {
                    setMessage(error.response?.data?.message || 'Unable to load labels.');
                }
            })
            .finally(() => {
                if (!ignore) {
                    setIsLoading(false);
                }
            });

        return () => {
            ignore = true;
        };
    }, [page]);

    const hasPreviousPage = Boolean(meta?.links?.length) ? Boolean(meta.links[0]?.url) : page > 1;
    const hasNextPage = Boolean(meta?.links?.length)
        ? Boolean(meta.links[meta.links.length - 1]?.url)
        : false;

    return (
        <section className="page-section">
            <div className="section-header">
                <div>
                    <p className="eyebrow">History</p>
                    <h2>Labels</h2>
                </div>
                <Link className="button primary" to="/labels/create">
                    <PackagePlus size={16} aria-hidden="true" />
                    Create label
                </Link>
            </div>

            {message ? <div className="alert">{message}</div> : null}

            {isLoading ? <p className="muted">Loading labels...</p> : null}

            {!isLoading && labels.length === 0 ? (
                <div className="empty-state">
                    <h3>No labels yet</h3>
                    <p className="muted">Created USPS labels will appear here.</p>
                    <Link className="button primary" to="/labels/create">
                        <PackagePlus size={16} aria-hidden="true" />
                        Create first label
                    </Link>
                </div>
            ) : null}

            {!isLoading && labels.length > 0 ? (
                <>
                    <div className="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Recipient</th>
                                    <th>Destination</th>
                                    <th>Service</th>
                                    <th>Tracking</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {labels.map((label) => (
                                    <LabelRow key={label.id} label={label} />
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="pagination-bar">
                        <button
                            type="button"
                            className="button"
                            onClick={() => setPage((current) => Math.max(current - 1, 1))}
                            disabled={!hasPreviousPage}
                        >
                            <ChevronLeft size={16} aria-hidden="true" />
                            Previous
                        </button>
                        <span className="muted">
                            Page {meta?.current_page || page}
                            {meta?.last_page ? ` of ${meta.last_page}` : ''}
                        </span>
                        <button
                            type="button"
                            className="button"
                            onClick={() => setPage((current) => current + 1)}
                            disabled={!hasNextPage}
                        >
                            Next
                            <ChevronRight size={16} aria-hidden="true" />
                        </button>
                    </div>
                </>
            ) : null}
        </section>
    );
}

function LabelRow({ label }) {
    const printUrl = labelPrintUrl(label);

    return (
        <tr>
            <td>
                <strong>{label.recipient?.name || '-'}</strong>
            </td>
            <td>
                {[label.recipient?.city, label.recipient?.state, label.recipient?.zip]
                    .filter(Boolean)
                    .join(', ') || '-'}
            </td>
            <td>
                {[label.carrier, label.service].filter(Boolean).join(' / ') || '-'}
            </td>
            <td>{label.tracking_code || '-'}</td>
            <td>
                <span className="status-pill">{label.status || 'unknown'}</span>
            </td>
            <td>{formatDate(label.created_at)}</td>
            <td>
                <div className="table-actions">
                    <Link className="icon-button" to={`/labels/${label.id}`} title="View label">
                        <Eye size={17} aria-hidden="true" />
                    </Link>
                    {printUrl ? (
                        <a
                            className="icon-button"
                            href={printUrl}
                            target="_blank"
                            rel="noreferrer"
                            title="Print label"
                        >
                            <Printer size={17} aria-hidden="true" />
                        </a>
                    ) : null}
                </div>
            </td>
        </tr>
    );
}
