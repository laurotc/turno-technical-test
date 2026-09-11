export function formatDate(value) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

export function formatCurrency(value, currency = 'USD') {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency,
    }).format(Number(value));
}

export function formatAddress(address) {
    if (!address) {
        return '-';
    }

    return [
        address.street1,
        address.street2,
        [address.city, address.state, address.zip].filter(Boolean).join(', '),
        address.country,
    ]
        .filter(Boolean)
        .join(' - ');
}

export function labelPrintUrl(label) {
    return label?.label_pdf_url || label?.label_url || null;
}
