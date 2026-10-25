<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatSettingController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::allCached()->groupBy('group_name');

        return Inertia::render('Admin/Settings/Chat', [
            'groupedSettings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'chat_enabled' => 'boolean',
            'chat_assistant_name' => 'nullable|string|max:100',
            'chat_greeting' => 'nullable|string|max:500',
        ]);

        foreach ($validated as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : ($value ?? ''),
                    'type' => $key === 'chat_enabled' ? 'boolean' : 'text',
                    'group_name' => 'features',
                    'updated_at' => now(),
                ]
            );
        }

        return redirect()->back();
    }
}
