<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Support\ImageOptimizer;
use App\Support\PublicCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SiteSettingController extends Controller
{
    public function hero()
    {
        $settings = SiteSetting::allCached()->groupBy('group_name');

        return Inertia::render('Admin/Settings/Hero', [
            'groupedSettings' => $settings,
            'linkOptions' => $this->footerLinkOptions(),
        ]);
    }

    public function updateHero(Request $request)
    {
        $validated = $request->validate([
            'hero_slide_1_tag' => 'nullable|string|max:100',
            'hero_slide_1_title' => 'nullable|string|max:150',
            'hero_slide_1_subtitle' => 'nullable|string|max:500',
            'hero_slide_1_btn_text' => 'nullable|string|max:50',
            'hero_slide_1_btn_url' => ['nullable', 'string', 'max:500', $this->urlRule()],
            'hero_slide_1_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_hero_slide_1_image' => 'boolean',
            'hero_slide_2_tag' => 'nullable|string|max:100',
            'hero_slide_2_title' => 'nullable|string|max:150',
            'hero_slide_2_subtitle' => 'nullable|string|max:500',
            'hero_slide_2_btn_text' => 'nullable|string|max:50',
            'hero_slide_2_btn_url' => ['nullable', 'string', 'max:500', $this->urlRule()],
            'hero_slide_2_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_hero_slide_2_image' => 'boolean',
        ]);
        $data = collect($validated)->except([
            'hero_slide_1_file',
            'hero_slide_2_file',
            'remove_hero_slide_1_image',
            'remove_hero_slide_2_image',
        ])->all();

        foreach ([1, 2] as $i) {
            $inputName = "hero_slide_{$i}_file";
            $imageKey = "hero_slide_{$i}_image";

            if ($request->hasFile($inputName)) {
                $existing = SiteSetting::where('key', $imageKey)->first();
                if ($existing) {
                    $this->deleteSettingFile($existing->value);
                }
                $data[$imageKey] = ImageOptimizer::storeAndUrl($request->file($inputName), 'settings', 1920);
            } elseif ($request->boolean("remove_hero_slide_{$i}_image")) {
                $imageKey = "hero_slide_{$i}_image";
                $fallbackKey = 'hero_background_image';
                $existingImage = SiteSetting::where('key', $imageKey)->first();
                $existingFallback = SiteSetting::where('key', $fallbackKey)->first();
                if ($existingImage) {
                    $this->deleteSettingFile($existingImage->value);
                }
                if ($existingFallback) {
                    $this->deleteSettingFile($existingFallback->value);
                }
                $data[$imageKey] = '';
                $data[$fallbackKey] = '';
            }
        }

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '', 'updated_at' => now()]
            );
        }

        return redirect()->back();
    }

    private function deleteSettingFile(?string $url): void
    {
        if (! $url) {
            return;
        }

        $relativePath = str_replace('/storage/', '', $url);

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }

    public function contact()
    {
        $settings = SiteSetting::allCached()->groupBy('group_name');

        return Inertia::render('Admin/Settings/Contact', [
            'groupedSettings' => $settings,
        ]);
    }

    public function updateContact(Request $request)
    {
        $data = $request->validate([
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'contact_address' => 'nullable|string|max:500',
            'ppdb_link' => ['nullable', 'string', 'max:500', $this->urlRule()],
        ]);

        $this->saveSettings($data);

        return redirect()->back();
    }

    public function statistics()
    {
        $settings = SiteSetting::allCached()->groupBy('group_name');

        return Inertia::render('Admin/Settings/Statistics', [
            'groupedSettings' => $settings,
        ]);
    }

    public function updateStatistics(Request $request)
    {
        $data = $request->validate([
            'total_students' => 'nullable|string|max:50',
            'total_teachers' => 'nullable|string|max:50',
            'total_staff' => 'nullable|string|max:50',
            'year_founded' => 'nullable|string|max:20',
        ]);

        $this->saveSettings($data);

        return redirect()->back();
    }

    public function quickLinks()
    {
        $settings = SiteSetting::allCached()->groupBy('group_name');

        return Inertia::render('Admin/Settings/QuickLinks', [
            'groupedSettings' => $settings,
        ]);
    }

    public function updateQuickLinks(Request $request)
    {
        $rules = [];
        foreach ([1, 2, 3] as $i) {
            $rules["quick_link_{$i}_title"] = 'nullable|string|max:100';
            $rules["quick_link_{$i}_desc"] = 'nullable|string|max:255';
            $rules["quick_link_{$i}_url"] = ['nullable', 'string', 'max:500', $this->urlRule()];
        }

        $this->saveSettings($request->validate($rules));

        return redirect()->back();
    }

    public function socialMedia()
    {
        $settings = SiteSetting::allCached()->groupBy('group_name');

        return Inertia::render('Admin/Settings/SocialMedia', [
            'groupedSettings' => $settings,
        ]);
    }

    public function updateSocialMedia(Request $request)
    {
        $rules = [];
        foreach (['facebook', 'instagram', 'twitter', 'youtube', 'linkedin', 'whatsapp', 'tiktok'] as $network) {
            $rules["social_{$network}"] = 'nullable|url:http,https|max:500';
        }

        $this->saveSettings($request->validate($rules));

        return redirect()->back();
    }

    private function saveSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '', 'updated_at' => now()]
            );
        }
    }

    private function urlRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === '') {
                return;
            }

            if ((str_starts_with($value, '/') && ! str_starts_with($value, '//')) || str_starts_with($value, '#')) {
                return;
            }

            if (! filter_var($value, FILTER_VALIDATE_URL) || ! in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true)) {
                $fail('Kolom :attribute harus berupa URL HTTP(S), path internal, atau anchor yang valid.');
            }
        };
    }

    public function footer()
    {
        $settings = SiteSetting::allCached()->groupBy('group_name');

        return Inertia::render('Admin/Settings/Footer', [
            'groupedSettings' => $settings,
            'linkOptions' => $this->footerLinkOptions(),
        ]);
    }

    /**
     * Ready-to-pick internal pages so admins never copy URLs by hand.
     */
    public function footerLinkOptions(bool $includeDrafts = false): array
    {
        $cacheKey = $includeDrafts
            ? PublicCache::FOOTER_LINKS_WITH_DRAFTS
            : PublicCache::FOOTER_LINKS;

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(PublicCache::SHELL_TTL_MINUTES),
            function () use ($includeDrafts): array {
                $options = [
                    ['label' => 'Beranda', 'url' => '/'],
                    ['label' => 'Berita', 'url' => '/berita'],
                    ['label' => 'Statistik', 'url' => '/statistik'],
                ];

                $ppdbUrl = SiteSetting::allCached()->firstWhere('key', 'ppdb_link')?->value;
                if ($ppdbUrl) {
                    $options[] = ['label' => 'Daftar PPDB', 'url' => $ppdbUrl];
                }

                $pages = Page::query()
                    ->when(! $includeDrafts, fn ($query) => $query->where('is_published', true))
                    ->orderBy('title')
                    ->get(['title', 'slug', 'is_published']);

                foreach ($pages as $page) {
                    $options[] = [
                        'label' => 'Laman: '.$page->title.($page->is_published ? '' : ' (draf)'),
                        'url' => '/'.$page->slug,
                    ];
                }

                foreach (Category::orderBy('name')->get(['name', 'slug']) as $category) {
                    $options[] = ['label' => 'Kategori: '.$category->name, 'url' => '/'.$category->slug];
                }

                return $options;
            },
        );
    }

    public function updateFooter(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:100',
            'site_portal_text' => 'nullable|string|max:100',
            'site_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'site_website_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'footer_about' => 'nullable|string|max:500',
            'footer_groups' => 'nullable|array',
            'footer_groups.*.title' => 'required|string|max:100',
            'footer_groups.*.links' => 'required|array|min:1',
            'footer_groups.*.links.*.label' => 'required|string|max:100',
            'footer_groups.*.links.*.url' => ['required', 'string', 'max:500', $this->urlRule()],
        ]);

        $this->saveLogoSetting($request, 'site_logo_file', 'site_logo', 'remove_site_logo');
        $this->saveLogoSetting($request, 'site_website_logo_file', 'site_website_logo', 'remove_site_website_logo');

        foreach (['site_name', 'site_portal_text', 'footer_about'] as $key) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $validated[$key] ?? '', 'updated_at' => now()]
            );
        }

        SiteSetting::updateOrCreate(
            ['key' => 'footer_groups'],
            ['value' => json_encode(array_values($validated['footer_groups'] ?? [])), 'updated_at' => now()]
        );

        return redirect()->back();
    }

    private function saveLogoSetting(Request $request, string $fileInput, string $key, string $removeInput): void
    {
        if ($request->hasFile($fileInput)) {
            $existing = SiteSetting::where('key', $key)->first();
            if ($existing) {
                $this->deleteSettingFile($existing->value);
            }

            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => ImageOptimizer::storeAndUrl($request->file($fileInput), 'settings', 512), 'updated_at' => now()]
            );

            return;
        }

        if ($request->boolean($removeInput)) {
            $existing = SiteSetting::where('key', $key)->first();
            if ($existing) {
                $this->deleteSettingFile($existing->value);
                $existing->delete();
            }
        }
    }

    public function navigation()
    {
        $settings = SiteSetting::allCached()->groupBy('group_name');

        return Inertia::render('Admin/Settings/Navigation', [
            'groupedSettings' => $settings,
            'linkOptions' => $this->footerLinkOptions(includeDrafts: true),
        ]);
    }

    public function updateNavigation(Request $request)
    {
        $validated = $request->validate([
            'nav_items' => 'nullable|array',
            'nav_items.*.type' => 'required|in:single,dropdown',
            // Dropdowns may be parked half-finished: label, url, and children can stay empty.
            'nav_items.*.label' => 'nullable|string|max:255|required_if:nav_items.*.type,single',
            'nav_items.*.url' => ['required_if:nav_items.*.type,single', 'nullable', 'string', 'max:500', $this->urlRule()],
            'nav_items.*.children' => 'nullable|array',
            'nav_items.*.children.*.label' => 'required|string|max:255',
            'nav_items.*.children.*.url' => ['required', 'string', 'max:500', $this->urlRule()],
        ]);

        SiteSetting::saveNavItems($validated['nav_items'] ?? []);

        return redirect()->back();
    }

    public function topbar()
    {
        $settings = SiteSetting::allCached()->groupBy('group_name');

        return Inertia::render('Admin/Settings/Topbar', [
            'groupedSettings' => $settings,
            'linkOptions' => $this->footerLinkOptions(),
        ]);
    }

    public function sidebarCta()
    {
        $settings = SiteSetting::allCached()->groupBy('group_name');

        return Inertia::render('Admin/Settings/SidebarCta', [
            'groupedSettings' => $settings,
            'linkOptions' => $this->footerLinkOptions(),
        ]);
    }

    public function updateSidebarCta(Request $request)
    {
        $validated = $request->validate([
            'sidebar_cta_enabled' => 'boolean',
            'sidebar_cta_title' => 'nullable|string|max:100',
            'sidebar_cta_heading' => 'nullable|string|max:150',
            'sidebar_cta_description' => 'nullable|string|max:500',
            'sidebar_cta_button_text' => 'nullable|string|max:50',
            'sidebar_cta_button_url' => ['nullable', 'string', 'max:500', $this->urlRule()],
        ]);

        foreach ($validated as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : $value, 'updated_at' => now()]
            );
        }

        return redirect()->back();
    }

    public function updateTopbar(Request $request)
    {
        $validated = $request->validate([
            'topbar_links' => 'nullable|array',
            'topbar_links.*.label' => 'required|string|max:100',
            'topbar_links.*.url' => ['required', 'string', 'max:500', $this->urlRule()],
        ]);

        SiteSetting::updateOrCreate(
            ['key' => 'topbar_links'],
            ['value' => json_encode(array_values($validated['topbar_links'] ?? [])), 'updated_at' => now()]
        );

        return redirect()->back();
    }
}
