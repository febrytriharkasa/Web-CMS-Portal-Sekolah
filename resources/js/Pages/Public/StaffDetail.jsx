import PublicLayout from '@/Layouts/PublicLayout';
import Seo from '@/Components/Seo';
import Breadcrumbs from '@/Components/Breadcrumbs';
import Card from '@/Components/Card';
import { Link } from '@inertiajs/react';
import { Reveal } from '@/Components/Reveal';

export default function StaffDetail({ title, staffMember }) {
    return (
        <PublicLayout>
            <Seo title={staffMember.name} description={staffMember.role} />
            <Breadcrumbs items={[{ label: 'Beranda', href: '/' }, { label: 'Guru & Tenaga Kependidikan', href: '/guru-tendik' }, { label: staffMember.name }]} />
            <section className="bg-slate-50 py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Reveal>
                        <div className="flex flex-col items-center">
                            {staffMember.photo ? (
                                <img src={staffMember.photo} alt={staffMember.name} className="mb-6 h-40 w-40 rounded-full object-cover shadow-lg" />
                            ) : (
                                <div className="mb-6 flex h-40 w-40 items-center justify-center rounded-full bg-primary/10 text-3xl font-bold text-primary">
                                    {(staffMember.name || '?').charAt(0).toUpperCase()}
                                </div>
                            )}
                            <h1 className="mb-2 text-2xl font-extrabold text-slate-900 sm:text-3xl">{staffMember.name}</h1>
                            <p className="mb-4 text-sm text-slate-600">{staffMember.role}</p>
                            <div className="flex gap-4 text-sm text-slate-500">
                                {staffMember.email && <span>Email: {staffMember.email}</span>}
                                {staffMember.phone && <span>Telepon: {staffMember.phone}</span>}
                            </div>
                        </div>
                    </Reveal>
                </div>
            </section>
        </PublicLayout>
    );
}
