import { ArrowLeft, Printer } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { apiClient } from '../api/client';
import { formatAddress, formatCurrency, formatDate, labelPrintUrl } from '../utils/formatters';

export function LabelDetailPage() {
    const { id } = useParams();
    const [label, setLabel] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [message, setMessage] = useState('');

    useEffect(() => {
        let ignore = false;

        setIsLoading(true);
        setMessage('');

        apiClient
            .get(`/labels/${id}`)
            .then((response) => {
                if (!ignore) {
                    setLabel(response.data.data);
                }
            })
            .catch((error) => {
                if (!ignore) {
                    setMessage(error.response?.data?.message || 'Unable to load label.');
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
    }, [id]);

    const printUrl = labelPrintUrl(label);

    return (
        <section className="page-section">
            <div className="section-header">
                <div>
                    <p className="eyebrow">Label detail</p>
                    <h2>{label?.tracking_code || `Label #${id}`}</h2>
                </div>
                <div className="section-actions">
                    <Link className="button" to="/labels">
                        <ArrowLeft size={16} aria-hidden="true" />
                        Back
                    </Link>
                    {printUrl ? (
                        <a className="button primary" href={printUrl} target="_blank" rel="noreferrer">
                            <Printer size={16} aria-hidden="true" />
                            Print
                        </a>
                    ) : null}
                </div>
            </div>

            {message ? <div className="alert">{message}</div> : null}
            {isLoading ? <p className="muted">Loading label...</p> : null}

            {!isLoading && label ? (
                <div className="detail-grid">
                    <DetailGroup title="Destination">
                        <DetailRow label="Recipient" value={label.to_address?.name} />
                        <DetailRow label="Address" value={formatAddress(label.to_address)} />
                        <DetailRow label="Phone" value={label.to_address?.phone} />
                    </DetailGroup>

                    <DetailGroup title="Origin">
                        <DetailRow label="Sender" value={label.from_address?.name} />
                        <DetailRow label="Address" value={formatAddress(label.from_address)} />
                        <DetailRow label="Phone" value={label.from_address?.phone} />
                    </DetailGroup>

                    <DetailGroup title="Package">
                        <DetailRow label="Dimensions" value={formatDimensions(label.parcel)} />
                        <DetailRow label="Weight" value={label.parcel?.weight ? `${label.parcel.weight} oz` : '-'} />
                    </DetailGroup>

                    <DetailGroup title="Shipment">
                        <DetailRow label="Carrier" value={label.carrier} />
                        <DetailRow label="Service" value={label.service} />
                        <DetailRow label="Rate" value={formatCurrency(label.rate?.rate, label.rate?.currency)} />
                        <DetailRow label="Status" value={label.status} />
                        <DetailRow label="Created" value={formatDate(label.created_at)} />
                    </DetailGroup>
                </div>
            ) : null}
        </section>
    );
}

function DetailGroup({ children, title }) {
    return (
        <section className="detail-group">
            <h3>{title}</h3>
            <div className="detail-rows">{children}</div>
        </section>
    );
}

function DetailRow({ label, value }) {
    return (
        <div className="detail-row">
            <dt>{label}</dt>
            <dd>{value || '-'}</dd>
        </div>
    );
}

function formatDimensions(parcel) {
    if (!parcel) {
        return '-';
    }

    return `${parcel.length || '-'} x ${parcel.width || '-'} x ${parcel.height || '-'} in`;
}
