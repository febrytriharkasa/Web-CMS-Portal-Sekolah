import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Card';
import Button from '@/Components/Button';
import Checkbox from '@/Components/Checkbox';
import { useForm } from '@inertiajs/react';

export default function ChatSettings({ groupedSettings }) {
    const rawSettings = Object.values(groupedSettings).flat().reduce((acc, curr) => {
        acc[curr.key] = curr.value;
        return acc;
    }, {});

    const { data, setData, post, processing, recentlySuccessful, errors } = useForm({
        chat_enabled: rawSettings.chat_enabled !== '0',
        chat_assistant_name: rawSettings.chat_assistant_name || '',
        chat_greeting: rawSettings.chat_greeting || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/settings/chat');
    };

    return (
        <AdminLayout title="Pengaturan Chat AI">
            <form onSubmit={handleSubmit}>
                <Card className="p-6">
                    <div className="mb-6 border-b border-slate-200 pb-4">
                        <h2 className="text-lg font-bold text-slate-900">Pengaturan Chat AI</h2>
                        <p className="mt-1 text-sm text-slate-600">
                            Atur nama asisten virtual dan pesan sapaan awal untuk chatbot AI
                        </p>
                    </div>

                    <div className="space-y-5">
                        <div>
                            <label className="flex items-center gap-2">
                                <Checkbox
                                    checked={data.chat_enabled}
                                    onChange={(e) => setData('chat_enabled', e.target.checked)}
                                />
                                <span className="text-sm font-medium text-slate-700">Aktifkan Chat AI</span>
                            </label>
                            <p className="ml-6 mt-1 text-sm text-slate-500">
                                Tampilkan tombol chatbot AI di halaman publik
                            </p>
                        </div>

                        <div>
                            <label htmlFor="chat_assistant_name" className="mb-1.5 block text-sm font-medium text-slate-700">
                                Nama Asisten Virtual
                            </label>
                            <input
                                type="text"
                                id="chat_assistant_name"
                                value={data.chat_assistant_name}
                                onChange={(e) => setData('chat_assistant_name', e.target.value)}
                                placeholder="Contoh: SMA XYZ Virtual Assistant"
                                maxLength={100}
                                className="w-full rounded-lg border-slate-300 shadow-sm focus:border-primary focus:ring-primary"
                            />
                            {errors.chat_assistant_name && (
                                <p className="mt-1 text-sm text-red-600">{errors.chat_assistant_name}</p>
                            )}
                            <p className="mt-1.5 text-sm text-slate-500">
                                Nama ini akan muncul di header panel chat dan dalam sapaan. Kosongkan untuk menggunakan nama default.
                            </p>
                        </div>

                        <div>
                            <label htmlFor="chat_greeting" className="mb-1.5 block text-sm font-medium text-slate-700">
                                Pesan Sapaan Awal
                            </label>
                            <textarea
                                id="chat_greeting"
                                value={data.chat_greeting}
                                onChange={(e) => setData('chat_greeting', e.target.value)}
                                rows={4}
                                maxLength={500}
                                placeholder="Contoh: Assalamu'alaikum! Saya {name}, asisten virtual {site_name}. Ada yang bisa kami bantu?"
                                className="w-full rounded-lg border-slate-300 shadow-sm focus:border-primary focus:ring-primary"
                            />
                            {errors.chat_greeting && (
                                <p className="mt-1 text-sm text-red-600">{errors.chat_greeting}</p>
                            )}
                            <p className="mt-1.5 text-sm text-slate-500">
                                Gunakan <code className="rounded bg-slate-100 px-1 py-0.5 text-xs">{'{name}'}</code> untuk nama asisten 
                                dan <code className="rounded bg-slate-100 px-1 py-0.5 text-xs">{'{site_name}'}</code> untuk nama sekolah. 
                                Kosongkan untuk sapaan default.
                            </p>
                        </div>
                    </div>
                </Card>

                <div className="flex gap-3 pt-4">
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
