const US_STATES = [
    'AL',
    'AK',
    'AZ',
    'AR',
    'CA',
    'CO',
    'CT',
    'DE',
    'FL',
    'GA',
    'HI',
    'ID',
    'IL',
    'IN',
    'IA',
    'KS',
    'KY',
    'LA',
    'ME',
    'MD',
    'MA',
    'MI',
    'MN',
    'MS',
    'MO',
    'MT',
    'NE',
    'NV',
    'NH',
    'NJ',
    'NM',
    'NY',
    'NC',
    'ND',
    'OH',
    'OK',
    'OR',
    'PA',
    'RI',
    'SC',
    'SD',
    'TN',
    'TX',
    'UT',
    'VT',
    'VA',
    'WA',
    'WV',
    'WI',
    'WY',
];

export function AddressForm({ errors = {}, onChange, prefix, title, value }) {
    const updateField = (field, nextValue) => {
        onChange(prefix, {
            ...value,
            [field]: nextValue,
        });
    };

    return (
        <section className="form-section">
            <h3>{title}</h3>

            <div className="form-grid">
                <FieldErrorInput
                    label="Name"
                    name={`${prefix}.name`}
                    value={value.name}
                    error={errors[`${prefix}.name`]}
                    onChange={(event) => updateField('name', event.target.value)}
                    required
                />

                <FieldErrorInput
                    label="Company"
                    name={`${prefix}.company`}
                    value={value.company}
                    error={errors[`${prefix}.company`]}
                    onChange={(event) => updateField('company', event.target.value)}
                />

                <FieldErrorInput
                    label="Street 1"
                    name={`${prefix}.street1`}
                    value={value.street1}
                    error={errors[`${prefix}.street1`]}
                    onChange={(event) => updateField('street1', event.target.value)}
                    required
                />

                <FieldErrorInput
                    label="Street 2"
                    name={`${prefix}.street2`}
                    value={value.street2}
                    error={errors[`${prefix}.street2`]}
                    onChange={(event) => updateField('street2', event.target.value)}
                />

                <FieldErrorInput
                    label="City"
                    name={`${prefix}.city`}
                    value={value.city}
                    error={errors[`${prefix}.city`]}
                    onChange={(event) => updateField('city', event.target.value)}
                    required
                />

                <div className="field">
                    <label htmlFor={`${prefix}.state`}>State</label>
                    <select
                        id={`${prefix}.state`}
                        name={`${prefix}.state`}
                        value={value.state}
                        onChange={(event) => updateField('state', event.target.value)}
                        required
                    >
                        <option value="">Select state</option>
                        {US_STATES.map((state) => (
                            <option key={state} value={state}>
                                {state}
                            </option>
                        ))}
                    </select>
                    <FieldError errors={errors[`${prefix}.state`]} />
                </div>

                <FieldErrorInput
                    label="ZIP"
                    name={`${prefix}.zip`}
                    value={value.zip}
                    error={errors[`${prefix}.zip`]}
                    onChange={(event) => updateField('zip', event.target.value)}
                    required
                />

                <div className="field">
                    <label htmlFor={`${prefix}.country`}>Country</label>
                    <input id={`${prefix}.country`} name={`${prefix}.country`} type="text" value="US" disabled />
                    <FieldError errors={errors[`${prefix}.country`]} />
                </div>

                <FieldErrorInput
                    label="Phone"
                    name={`${prefix}.phone`}
                    value={value.phone}
                    error={errors[`${prefix}.phone`]}
                    onChange={(event) => updateField('phone', event.target.value)}
                    required
                />

                <FieldErrorInput
                    label="Email"
                    name={`${prefix}.email`}
                    type="email"
                    value={value.email}
                    error={errors[`${prefix}.email`]}
                    onChange={(event) => updateField('email', event.target.value)}
                />
            </div>
        </section>
    );
}

function FieldErrorInput({ error, label, name, type = 'text', ...props }) {
    return (
        <div className="field">
            <label htmlFor={name}>{label}</label>
            <input id={name} name={name} type={type} {...props} />
            <FieldError errors={error} />
        </div>
    );
}

function FieldError({ errors }) {
    if (!errors?.length) {
        return null;
    }

    return <p className="field-error">{errors[0]}</p>;
}
