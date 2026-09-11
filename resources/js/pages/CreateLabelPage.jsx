import { ArrowLeft, LoaderCircle, PackageCheck } from 'lucide-react';
import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { apiClient } from '../api/client';
import { AddressForm } from '../components/AddressForm';

const emptyAddress = {
    name: '',
    company: '',
    street1: '',
    street2: '',
    city: '',
    state: '',
    zip: '',
    country: 'US',
    phone: '',
    email: '',
};

const emptyParcel = {
    length: '',
    width: '',
    height: '',
    weight: '',
};

export function CreateLabelPage() {
    const navigate = useNavigate();
    const [form, setForm] = useState({
        from_address: { ...emptyAddress },
        to_address: { ...emptyAddress },
        parcel: { ...emptyParcel },
    });
    const [errors, setErrors] = useState({});
    const [message, setMessage] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const updateAddress = (key, value) => {
        setForm((current) => ({
            ...current,
            [key]: {
                ...value,
                country: 'US',
            },
        }));
    };

    const updateParcel = (field, value) => {
        setForm((current) => ({
            ...current,
            parcel: {
                ...current.parcel,
                [field]: value,
            },
        }));
    };

    const submitForm = async (event) => {
        event.preventDefault();
        setErrors({});
        setMessage('');
        setIsSubmitting(true);

        try {
            const response = await apiClient.post('/labels', {
                ...form,
                from_address: normalizeAddress(form.from_address),
                to_address: normalizeAddress(form.to_address),
            });

            navigate(`/labels/${response.data.data.id}`);
        } catch (error) {
            setErrors(error.response?.data?.errors || {});
            setMessage(error.response?.data?.error || error.response?.data?.message || 'Unable to create label.');
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <section className="page-section">
            <div className="section-header">
                <div>
                    <p className="eyebrow">New shipment</p>
                    <h2>Create label</h2>
                </div>
                <Link className="button" to="/labels">
                    <ArrowLeft size={16} aria-hidden="true" />
                    Back
                </Link>
            </div>

            {message ? <div className="alert form-alert">{message}</div> : null}

            <form className="form-stack" onSubmit={submitForm}>
                <AddressForm
                    title="From address"
                    prefix="from_address"
                    value={form.from_address}
                    errors={errors}
                    onChange={updateAddress}
                />

                <AddressForm
                    title="Destination address"
                    prefix="to_address"
                    value={form.to_address}
                    errors={errors}
                    onChange={updateAddress}
                />

                <section className="form-section">
                    <h3>Package</h3>
                    <div className="form-grid parcel-grid">
                        <ParcelField
                            label="Length"
                            name="parcel.length"
                            value={form.parcel.length}
                            errors={errors['parcel.length']}
                            onChange={(event) => updateParcel('length', event.target.value)}
                        />
                        <ParcelField
                            label="Width"
                            name="parcel.width"
                            value={form.parcel.width}
                            errors={errors['parcel.width']}
                            onChange={(event) => updateParcel('width', event.target.value)}
                        />
                        <ParcelField
                            label="Height"
                            name="parcel.height"
                            value={form.parcel.height}
                            errors={errors['parcel.height']}
                            onChange={(event) => updateParcel('height', event.target.value)}
                        />
                        <ParcelField
                            label="Weight"
                            name="parcel.weight"
                            suffix="oz"
                            value={form.parcel.weight}
                            errors={errors['parcel.weight']}
                            onChange={(event) => updateParcel('weight', event.target.value)}
                        />
                    </div>
                </section>

                <div className="form-footer">
                    <p className="muted">The backend buys the first USPS test rate returned by EasyPost.</p>
                    <button type="submit" className="button primary" disabled={isSubmitting}>
                        {isSubmitting ? (
                            <>
                                <LoaderCircle size={16} aria-hidden="true" />
                                Creating...
                            </>
                        ) : (
                            <>
                                <PackageCheck size={16} aria-hidden="true" />
                                Create label
                            </>
                        )}
                    </button>
                </div>
            </form>
        </section>
    );
}

function ParcelField({ errors, label, name, suffix = 'in', ...props }) {
    return (
        <div className="field">
            <label htmlFor={name}>{label}</label>
            <div className="input-with-suffix">
                <input id={name} name={name} type="number" min="0" step="0.01" required {...props} />
                <span>{suffix}</span>
            </div>
            <FieldError errors={errors} />
        </div>
    );
}

function FieldError({ errors }) {
    if (!errors?.length) {
        return null;
    }

    return <p className="field-error">{errors[0]}</p>;
}

function normalizeAddress(address) {
    return Object.fromEntries(
        Object.entries({
            ...address,
            country: 'US',
        }).filter(([, value]) => value !== ''),
    );
}
