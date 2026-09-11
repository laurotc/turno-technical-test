import { useParams } from 'react-router-dom';

export function LabelDetailPage() {
    const { id } = useParams();

    return (
        <section className="page-section">
            <div className="section-header">
                <div>
                    <p className="eyebrow">Label detail</p>
                    <h2>Label #{id}</h2>
                </div>
            </div>
            <p className="muted">The label detail view will be added in step 2.</p>
        </section>
    );
}
