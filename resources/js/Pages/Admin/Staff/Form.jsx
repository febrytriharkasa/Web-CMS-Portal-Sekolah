import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Card';
import Button from '@/Components/Button';
import { useForm } from '@inertiajs/react';
import { useRef, useState } from 'react';

export default function StaffForm({ staffMember }) {
    const isEdit = !!staffMember;
    const [previewUrl, setPreviewUrl] = useState(null);
    const fileInputRef = useRef(null);
    const { data, setData, post, put, processing, errors } = useForm({
        name: staffMember?.name || '',
        role: staffMember?.role || '',
        type: staffMember?.type || 'guru',
        email: staffMember?.email || '',
        phone: staffMember?.phone || '',
        photo: null,
    }, { forceFormData: true });

    const pickPhoto = (event) => {
        const file = event.target.files?.[0];
        if (!file) return;
        setData('photo', file);
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        setPreviewUrl(URL.createObjectURL(file));
    };

    const submit = (event) => {
        event.preventDefault();
        if (isEdit) {
            put(`/admin/staff/${staffMember.id}`);
        } else {
            post('/admin/staff');
        }
    };

    const currentPhoto = previewUrl || staffMember?.photo || null;

    return (
        <AdminLayout title={isEdit ? 'Edit Guru/Tendik' : 'Tambah Guru/Tendik'}>
            <Card className="max-w-2xl p-6">
                <form onSubmit={submit} className="space-y-5">
                    <div>
                        <label className="block text-sm font-semibold text-slate-700">Foto</label>
                        <div className="mt-2 flex items-center gap-4">
                            {currentPhoto ? (
                                <img
                                    src={currentPhoto}
                                    alt="Pratinjau foto profil"
                                    className="h-20 w-20 rounded-full border border-slate-200 object-cover"
                                />
                            ) : (
                                <div className="flex h-20 w-20 items-center justify-center rounded-full bg-primary/10 text-xl font-bold text-primary">
                                    {(data.name || '?').charAt(0).toUpperCase()}
                                </div>
                            )}
                            <div>
                                <input
                                    ref={fileInputRef}
                                    id="photo"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    onChange={pickPhoto}
                                    className="hidden"
                                />
                                <Button type="button" variant="outline" size="sm" onClick={() => fileInputRef.current?.click()}>
                                    {currentPhoto ? 'Ganti Foto' : 'Unggah Foto'}
                                </Button>
                                <p className="mt-1 text-xs text-slate-500">JPG, PNG, atau WebP. Maksimal 2 MB.</p>
                            </div>
                        </div>
                        {errors.photo && <p className="mt-1 text-xs text-red-600">{errors.photo}</p>}
                    </div>

                    <div>
                        <label htmlFor="name" className="block text-sm font-semibold text-slate-700">Nama</label>
                        <input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} className="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary" />
                        {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name}</p>}
                    </div>

                    <div>
                        <label htmlFor="role" className="block text-sm font-semibold text-slate-700">Jabatan</label>
                        <input id="role" value={data.role} onChange={(e) => setData('role', e.target.value)} className="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary" />
                        {errors.role && <p className="mt-1 text-xs text-red-600">{errors.role}</p>}
                    </div>

                    <div>
                        <label htmlFor="type" className="block text-sm font-semibold text-slate-700">Tipe</label>
                        <select id="type" value={data.type} onChange={(e) => setData('type', e.target.value)} className="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary">
                            <option value="guru">Guru</option>
                            <option value="tendik">Tendik</option>
                            <option value="gtk">GTK</option>
                        </select>
                        {errors.type && <p className="mt-1 text-xs text-red-600">{errors.type}</p>}
                    </div>

                    <div>
                        <label htmlFor="email" className="block text-sm font-semibold text-slate-700">Email (opsional)</label>
                        <input id="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} placeholder="email@example.com" className="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary" />
                        {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
                    </div>

                    <div>
                        <label htmlFor="phone" className="block text-sm font-semibold text-slate-700">Telepon (opsional)</label>
                        <input id="phone" value={data.phone} onChange={(e) => setData('phone', e.target.value)} className="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary" />
                        {errors.phone && <p className="mt-1 text-xs text-red-600">{errors.phone}</p>}
                    </div>

                    <div className="flex justify-end gap-3">
                        <Button variant="outline" href="/admin/staff" icon="arrow-left">Batal</Button>
                        <Button type="submit" disabled={processing} icon="save">{processing ? 'Menyimpan...' : 'Simpan'}</Button>
                    </div>
                </form>
            </Card>
        </AdminLayout>
    );
}
