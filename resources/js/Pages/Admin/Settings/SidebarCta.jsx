import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Card';
import Button from '@/Components/Button';
import Checkbox from '@/Components/Checkbox';
import LinkUrlPicker from '@/Components/LinkUrlPicker';
import { useForm } from '@inertiajs/react';

export default function SidebarCtaSettings({ groupedSettings, linkOptions }) {
    const rawSettings = Object.values(groupedSettings).flat().reduce((acc, curr) => {
        acc[curr.key] = curr.value;
        return acc;
    }, {});

    const { data, setData, post, processing, recentlySuccessful, errors } = useForm({
        sidebar_cta_enabled: rawSettings.sidebar_cta_enabled !== '0',
        sidebar_cta_title: rawSettings.sidebar_cta_title || '',
        sidebar_cta_heading: rawSettings.sidebar_cta_heading || '',
        sidebar_cta_description: rawSettings.sidebar_cta_description || '',
        sidebar_cta_button_text: rawSettings.sidebar_cta_button_text || '',
        sidebar_cta_button_url: rawSettings.sidebar_cta_button_url || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/settings/sidebar-cta');
    };

    return (
        <AdminLayout title="Kartu PPDB Sidebar Artikel">
            <form onSubmit={handleSubmit} className="space-y-6 max-w-3xl">
                {recentlySuccessful && (
                    <div className="p-4 bg-emerald-50 text-emerald-700 rounded-lg text-sm">
                        Pengaturan berhasil disimpan.
                    </div>
                )}

                {Object.keys(errors).length > 0 && (
                    <div role="alert" className="p-4 bg-red-50 text-red-700 rounded-lg text-sm">
                        {Object.values(errors)[0]}
                    </div>
                )}

                <Card className="p-6">
                    <h2 className="text-base font-bold text-slate-800 mb-4">Kartu PPDB Sidebar Artikel</h2>
                    <p className="text-sm text-slate-600 mb-4">
                        Kartu ini tampil di sidebar setiap halaman berita dan artikel. Ubah sekali di sini,
                        seluruh halaman ikut berubah. Isi kartu tetap tersimpan walau dimatikan, jadi tinggal
                        dinyalakan lagi saat PPDB dibuka.
                    </p>

                    <label className="flex cursor-pointer items-start gap-3">
                        <Checkbox
                            checked={data.sidebar_cta_enabled}
                            onChange={(e) => setData('sidebar_cta_enabled', e.target.checked)}
                            className="mt-0.5"
                        />
                        <span>
                            <span className="block text-sm font-medium text-slate-700">Tampilkan kartu di sidebar artikel</span>
                            <span className="mt-0.5 block text-xs text-slate-500">Matikan saat PPDB tutup.</span>
                        </span>
                    </label>
                </Card>

                <Card className="p-6">
                    <h2 className="text-base font-bold text-slate-800 mb-4">Isi Kartu</h2>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">Label Kecil di Atas Judul</label>
                            <input
                                type="text"
                                value={data.sidebar_cta_title}
                                onChange={(e) => setData('sidebar_cta_title', e.target.value)}
                                placeholder="PPDB 2026 / 2027"
                                className="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">Judul Kartu</label>
                            <input
                                type="text"
                                value={data.sidebar_cta_heading}
                                onChange={(e) => setData('sidebar_cta_heading', e.target.value)}
                                placeholder="Bergabung Bersama Generasi Unggul SMA XYZ"
                                className="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">Deskripsi</label>
                            <textarea
                                rows={3}
                                value={data.sidebar_cta_description}
                                onChange={(e) => setData('sidebar_cta_description', e.target.value)}
                                placeholder="Pendaftaran Gelombang 1 telah dibuka. Dapatkan jalur beasiswa tahfidz dan prestasi akademik."
                                className="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">Teks Tombol</label>
                            <input
                                type="text"
                                value={data.sidebar_cta_button_text}
                                onChange={(e) => setData('sidebar_cta_button_text', e.target.value)}
                                placeholder="Daftar Sekarang"
                                className="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">Link Tombol</label>
                            <div className="mt-1">
                                <LinkUrlPicker
                                    options={linkOptions}
                                    value={data.sidebar_cta_button_url}
                                    onChange={(url) => setData('sidebar_cta_button_url', url)}
                                />
                            </div>
                            <p className="mt-1 text-xs text-slate-500">
                                Pilih halaman yang tersedia, atau pakai "URL lain (isi manual)" untuk link luar seperti portal PPDB.
                            </p>
                        </div>
                    </div>
                </Card>

                <div className="flex gap-3">
                    <Button type="submit" disabled={processing} icon="save">
                        {processing ? 'Menyimpan...' : 'Simpan Pengaturan'}
                    </Button>
                </div>
            </form>
        </AdminLayout>
    );
}
