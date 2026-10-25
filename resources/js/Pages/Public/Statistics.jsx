import PublicLayout from '@/Layouts/PublicLayout';
import Seo from '@/Components/Seo';
import Card from '@/Components/Card';
import Badge from '@/Components/Badge';
import Breadcrumbs from '@/Components/Breadcrumbs';
import { usePage } from '@inertiajs/react';
import { Reveal, StaggerContainer } from '@/Components/Reveal';

export default function Statistics({ statSettings, counts }) {
    const { settings = {} } = usePage().props;
    const stats = [
        { label: 'Total Siswa', value: statSettings.total_students || '-', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
        { label: 'Guru', value: counts.teachers || statSettings.total_teachers || '-', icon: 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z' },
        { label: 'Tenaga Kependidikan', value: counts.staff || statSettings.total_staff || '-', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
        { label: 'Tahun Berdiri', value: statSettings.year_founded || '-', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
        { label: 'Total Berita', value: counts.posts, icon: 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-5 0V17' },
        { label: 'Total Prestasi', value: counts.achievements, icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z' },
        { label: 'Agenda', value: counts.agendas, icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
        { label: 'Dokumentasi', value: counts.galleries, icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' },
    ];

    return (
        <PublicLayout>
            <Seo
                title="Statistik Sekolah"
                description={`Data statistik ${settings.site_name || 'sekolah'}: jumlah siswa, guru, tenaga kependidikan, berita, prestasi, agenda, dan dokumentasi.`}
            />
            <Breadcrumbs items={[{ label: 'Beranda', href: '/' }, { label: 'Statistik' }]} />
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <Reveal>
                <h1 className="text-3xl md:text-4xl font-extrabold text-slate-900">Statistik Sekolah</h1>
                <p className="mt-2 text-xs font-bold uppercase tracking-[0.14em] text-secondary">
                    Data statistik dan informasi kuantitatif {settings.site_name || 'SMA XYZ'}.
                </p>
                </Reveal>

                <StaggerContainer className="grid md:grid-cols-4 gap-6 mt-6">
                    {stats.map((stat, idx) => (
                        <div key={idx} className="stagger-item">
                        <Card className="p-6 text-center">
                            <div className="w-12 h-12 mx-auto bg-primary/10 text-primary rounded-lg flex items-center justify-center mb-3">
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={stat.icon} />
                                </svg>
                            </div>
                            <p className="text-3xl font-bold text-primary mb-1">{stat.value}</p>
                            <p className="text-sm text-slate-600">{stat.label}</p>
                        </Card>
                        </div>
                    ))}
                </StaggerContainer>
            </div>
        </PublicLayout>
    );
}
