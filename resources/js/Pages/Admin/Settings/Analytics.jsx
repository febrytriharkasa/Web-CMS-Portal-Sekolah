import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Card';
import Button from '@/Components/Button';
import { useForm } from '@inertiajs/react';

const METRICS = [
    ['users', 'Pengguna aktif'],
    ['sessions', 'Sesi'],
    ['views', 'Tampilan halaman'],
    ['avg_duration', 'Rata-rata durasi sesi'],
    ['engagement_rate', 'Tingkat keterlibatan'],
    ['key_events', 'Peristiwa kunci'],
];

function MetricValue({ metricKey, raw }) {
    const value = Number(raw) || 0;

    if (metricKey === 'avg_duration') {
        const minutes = Math.floor(value / 60);
        return minutes > 0 ? `${minutes} mnt ${value % 60} dtk` : `${value} dtk`;
    }

    if (metricKey === 'engagement_rate') {
        return `${(value * 100).toFixed(1)}%`;
    }

    return value.toLocaleString('id-ID');
}

function Breakdown({ title, rows, emptyText, unit = 'pengguna' }) {
    return (
        <Card className="p-6">
            <h3 className="mb-4 text-base font-bold text-slate-800">{title}</h3>
            {rows.length === 0 ? (
                <p className="text-sm text-slate-500">{emptyText}</p>
            ) : (
                <table className="w-full border-collapse text-left text-sm">
                    <tbody className="divide-y divide-slate-100">
                        {rows.map((row) => (
                            <tr key={row.label}>
                                <td className="max-w-0 truncate py-2 pr-3 text-slate-700">{row.label}</td>
                                <td className="py-2 text-right font-semibold text-slate-800">
                                    {row.value.toLocaleString('id-ID')}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
            <p className="mt-3 text-xs text-slate-400">Urut berdasarkan jumlah {unit}, 28 hari terakhir.</p>
        </Card>
    );
}

function SetupGuide() {
    return (
        <Card className="p-6">
            <h3 className="text-base font-bold text-slate-800">Laporan belum aktif</h3>
            <p className="mt-1 text-sm text-slate-600">
                Agar angka kunjungan muncul di halaman ini, siapkan kredensial baca GA4:
            </p>
            <ol className="mt-4 space-y-2 text-sm text-slate-600">
                <li>
                    1. Di Google Cloud Console, buat Service Account, lalu aktifkan <span className="font-medium">Google Analytics Data API</span>.
                </li>
                <li>
                    2. Buat key JSON untuk service account itu, simpan di{' '}
                    <code className="rounded bg-slate-100 px-1 py-0.5 text-xs">storage/app/private/google-service-account.json</code>
                    {' '}atau tunjuk path lain lewat <code className="rounded bg-slate-100 px-1 py-0.5 text-xs">GOOGLE_ANALYTICS_CREDENTIALS</code> di file <code className="rounded bg-slate-100 px-1 py-0.5 text-xs">.env</code>.
                </li>
                <li>
                    3. Di GA4, buka <span className="font-medium">Admin &gt; Property Access Management</span> dan tambahkan email service account dengan peran Viewer.
                </li>
                <li>
                    4. Isi ID properti pada kolom di atas. Angka ini ada di GA4 <span className="font-medium">Admin &gt; Property Settings</span>, bukan yang berawalan G-.
                </li>
            </ol>
        </Card>
    );
}

export default function AnalyticsSettings({ groupedSettings, report }) {
    const rawSettings = Object.values(groupedSettings).flat().reduce((acc, curr) => {
        acc[curr.key] = curr.value;
        return acc;
    }, {});

    const { data, setData, post, processing, recentlySuccessful, errors } = useForm({
        google_analytics_id: rawSettings.google_analytics_id || '',
        google_analytics_property_id: rawSettings.google_analytics_property_id || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/settings/analytics');
    };

    return (
        <AdminLayout title="Analitik Google">
            <section className="max-w-5xl">
                <h2 className="mb-4 text-lg font-bold text-slate-900">Laporan {report?.days ?? 28} Hari Terakhir</h2>

                {!report && <SetupGuide />}

                {report && !report.ok && (
                    <Card className="border-red-200 p-6">
                        <h3 className="text-base font-bold text-slate-800">Laporan gagal diambil</h3>
                        <p className="mt-1 text-sm text-slate-600">
                            Halaman tetap bisa dipakai, hanya angkanya yang belum tersedia. Cek lagi email service account, peran Viewer di properti GA4, dan ID properti.
                        </p>
                        <p className="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700 break-words">{report.error}</p>
                    </Card>
                )}

                {report?.ok && (
                    <div className="space-y-6">
                        <div className="grid grid-cols-2 gap-4 md:grid-cols-3">
                            {METRICS.map(([key, label]) => (
                                <Card key={key} className="p-4">
                                    <p className="text-2xl font-extrabold text-slate-800">
                                        <MetricValue metricKey={key} raw={report.metrics[key]} />
                                    </p>
                                    <p className="mt-1 text-sm text-slate-500">{label}</p>
                                </Card>
                            ))}
                        </div>

                        <div className="grid gap-6 lg:grid-cols-2">
                            <Breakdown
                                title="Halaman paling sering dibuka"
                                rows={report.pages}
                                unit="tampilan"
                                emptyText="Belum ada halaman yang tercatat dalam periode ini."
                            />
                            <Breakdown
                                title="Asal pengunjung (negara)"
                                rows={report.countries}
                                emptyText="Belum ada data lokasi pengunjung."
                            />
                            <Breakdown
                                title="Sumber trafik"
                                rows={report.channels}
                                unit="sesi"
                                emptyText="Belum ada data sumber trafik."
                            />
                            <Breakdown
                                title="Perangkat pengunjung"
                                rows={report.devices}
                                emptyText="Belum ada data perangkat."
                            />
                        </div>

                        <p className="text-xs text-slate-400">
                            Angka diambil dari GA4 Data API dan disimpan sementara 6 jam. Perubahan terbaru bisa belum terlihat.
                        </p>
                    </div>
                )}
            </section>

            <form onSubmit={handleSubmit} className="max-w-3xl mt-6">
                <Card className="p-6">
                    <div className="mb-6 border-b border-slate-200 pb-4">
                        <h2 className="text-lg font-bold text-slate-900">Google Analytics (GA4)</h2>
                        <p className="mt-1 text-sm text-slate-600">
                            Measurement ID untuk mengirim data kunjungan, ID properti untuk membaca laporannya di halaman ini
                        </p>
                    </div>

                    <div className="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label htmlFor="google_analytics_id" className="mb-1.5 block text-sm font-medium text-slate-700">
                                Measurement ID
                            </label>
                            <input
                                type="text"
                                id="google_analytics_id"
                                value={data.google_analytics_id}
                                onChange={(e) => setData('google_analytics_id', e.target.value.trim())}
                                placeholder="G-XXXXXXXXXX"
                                maxLength={32}
                                className="w-full rounded-lg border-slate-300 shadow-sm focus:border-primary focus:ring-primary"
                            />
                            {errors.google_analytics_id && (
                                <p className="mt-1 text-sm text-red-600">{errors.google_analytics_id}</p>
                            )}
                            <p className="mt-1.5 text-sm text-slate-500">
                                Untuk mengirim data. Diambil dari GA4 Admin &gt; Data Streams &gt; stream web. Kosongkan untuk mematikan pelacakan.
                            </p>
                        </div>

                        <div>
                            <label htmlFor="google_analytics_property_id" className="mb-1.5 block text-sm font-medium text-slate-700">
                                ID Properti (angka)
                            </label>
                            <input
                                type="text"
                                inputMode="numeric"
                                id="google_analytics_property_id"
                                value={data.google_analytics_property_id}
                                onChange={(e) => setData('google_analytics_property_id', e.target.value.trim())}
                                placeholder="123456789"
                                maxLength={20}
                                className="w-full rounded-lg border-slate-300 shadow-sm focus:border-primary focus:ring-primary"
                            />
                            {errors.google_analytics_property_id && (
                                <p className="mt-1 text-sm text-red-600">{errors.google_analytics_property_id}</p>
                            )}
                            <p className="mt-1.5 text-sm text-slate-500">
                                Untuk membaca laporan. Diambil dari GA4 Admin &gt; Property Settings.
                            </p>
                        </div>
                    </div>

                    <p className="mt-5 rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        {data.google_analytics_id
                            ? 'Script pengiriman aktif di server produksi. Di lingkungan lokal dan staging tidak ada data yang dikirim, jadi statistik pengembangan tidak mengotori laporan.'
                            : 'Belum ada Measurement ID, jadi belum ada data kunjungan yang dikirim ke Google Analytics.'}
                    </p>
                </Card>

                <div className="flex gap-3 py-4">
                    <Button type="submit" disabled={processing} icon="save">
                        {processing ? 'Menyimpan...' : 'Simpan Pengaturan'}
                    </Button>
                    {recentlySuccessful && (
                        <p className="flex items-center text-sm text-emerald-600">
                            Pengaturan berhasil disimpan
                        </p>
                    )}
                </div>
            </form>
        </AdminLayout>
    );
}