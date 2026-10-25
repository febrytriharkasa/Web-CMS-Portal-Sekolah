<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Support\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('role', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        return Inertia::render('Admin/Staff/Index', [
            'staff' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Staff/Form', [
            'staffMember' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'type' => 'required|in:guru,tendik,gtk',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = ImageOptimizer::storeAndUrl($request->file('photo'), 'staff', 800);
        }

        Staff::create($data);

        return redirect()->route('admin.staff.index')->with('success', 'Data guru/tendik ditambahkan.');
    }

    public function show(Staff $staff)
    {
        return Inertia::render('Admin/Staff/Show', [
            'staffMember' => $staff,
        ]);
    }

    public function edit(Staff $staff)
    {
        return Inertia::render('Admin/Staff/Form', [
            'staffMember' => $staff,
        ]);
    }

    public function update(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'type' => 'required|in:guru,tendik,gtk',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $this->deletePhoto($staff->photo);
            $data['photo'] = ImageOptimizer::storeAndUrl($request->file('photo'), 'staff', 800);
        }

        $staff->update($data);

        return redirect()->route('admin.staff.index')->with('success', 'Data guru/tendik diperbarui.');
    }

    public function destroy(Staff $staff)
    {
        $this->deletePhoto($staff->photo);
        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Data berhasil dihapus.');
    }

    private function deletePhoto(?string $photo): void
    {
        $path = ltrim(str_replace('/storage/', '', (string) $photo), '/');

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
