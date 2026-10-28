<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesNavMenu;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Mews\Purifier\Facades\Purifier;

class PageController extends Controller
{
    use ManagesNavMenu;

    public function index(Request $request)
    {
        $query = Page::latest();

        if ($request->search) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if (in_array($request->status, ['published', 'draft'], true)) {
            $query->where('is_published', $request->status === 'published');
        }

        $navUrls = $this->navUrls();

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $query->paginate(10)->withQueryString()->through(
                fn (Page $page) => $page->setAttribute('in_nav', in_array('/'.$page->slug, $navUrls, true)),
            ),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Pages/Form', [
            'page' => null,
            'navDropdowns' => $this->navDropdowns(),
            'navPlacement' => ['placement' => 'none', 'label' => '', 'parent_label' => null],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePage($request);
        $nav = $this->navInput($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['image'] = $request->hasFile('image') ? $this->storeImage($request) : null;
        $data['metadata'] = $this->metadata($data);
        $page = Page::create($data);

        $this->syncNavPlacement('/'.$page->slug, $page->title, $nav);

        return redirect()->route('admin.pages.index')->with('success', 'Laman berhasil dibuat.');
    }

    public function show(Page $page)
    {
        return Inertia::render('Admin/Pages/Show', [
            'page' => $page,
            'navPlacement' => $this->navPlacement('/'.$page->slug),
        ]);
    }

    public function edit(Page $page)
    {
        return Inertia::render('Admin/Pages/Form', [
            'page' => $page,
            'navDropdowns' => $this->navDropdowns(),
            'navPlacement' => $this->navPlacement('/'.$page->slug),
        ]);
    }

    public function updateStatus(Request $request, Page $page)
    {
        $validated = $request->validate([
            'is_published' => ['required', 'boolean'],
        ]);

        $page->update(['is_published' => $validated['is_published']]);

        return back()->with('success', 'Status laman diperbarui.');
    }

    public function update(Request $request, Page $page)
    {
        $data = $this->validatePage($request);
        $nav = $this->navInput($request);

        if ($request->hasFile('image')) {
            $this->deleteImage($page->image);
            $data['image'] = $this->storeImage($request);
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($page->image);
            $data['image'] = null;
        }

        $data['metadata'] = $this->metadata($data);

        $page->update($data);

        $this->syncNavPlacement('/'.$page->slug, $page->title, $nav);

        return redirect()->route('admin.pages.index')->with('success', 'Laman berhasil diperbarui.');
    }

    public function destroy(Page $page)
    {
        $this->deleteImage($page->image);
        $this->removeNavEntry('/'.$page->slug);
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Laman berhasil dihapus.');
    }

    private function validatePage(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_published' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'meta' => ['nullable', 'array'],
            'meta.*' => ['nullable'],
            'meta.blocks' => ['nullable', 'array'],
            'meta.blocks.*.type' => ['required_with:meta.blocks', Rule::in(['text', 'table', 'callout', 'blockquote'])],
            'meta.blocks.*.content' => ['nullable', 'string'],
            'meta.blocks.*.source' => ['nullable', 'string', 'max:255'],
            'meta.blocks.*.title' => ['nullable', 'string', 'max:255'],
            'meta.blocks.*.description' => ['nullable', 'string', 'max:500'],
            'meta.blocks.*.columns' => ['nullable', 'array'],
            'meta.blocks.*.columns.*' => ['nullable', 'string', 'max:100'],
            'meta.blocks.*.rows' => ['required_if:meta.blocks.*.type,table', 'array'],
            'meta.blocks.*.rows.*' => ['nullable'],
            'meta.blocks.*.rows.*.*' => ['nullable', 'string', 'max:1000'],
        ], [
            'image.image' => 'Berkas gambar tidak valid.',
            'content.required_if' => 'Konten wajib diisi untuk laman teks statis.',
        ]);

        $validated['content'] = Purifier::clean($validated['content'], 'content');

        return $validated;
    }

    /**
     * Rich-text block bodies arrive as HTML, so they are purified here and blocks
     * that end up empty after cleaning are dropped instead of saved blank.
     */
    private function metadata(array $validated): ?array
    {
        $blocks = $validated['meta']['blocks'] ?? [];

        $blocks = array_map(function ($block) {
            if (! is_array($block)) {
                return null;
            }

            if (isset($block['content']) && is_string($block['content'])) {
                $block['content'] = Purifier::clean($block['content'], 'content');
            }

            if (isset($block['source']) && is_string($block['source'])) {
                $block['source'] = strip_tags($block['source']);
            }

            return $block;
        }, $blocks);

        $blocks = array_values(array_filter($blocks, function ($block) {
            if (! isset($block['type'])) {
                return false;
            }
            if (in_array($block['type'], ['text', 'callout', 'blockquote'], true)) {
                return trim($block['content'] ?? '') !== '';
            }
            if ($block['type'] === 'table') {
                return ! empty($block['rows']);
            }

            return false;
        }));

        return empty($blocks) ? null : ['blocks' => $blocks];
    }

    private function storeImage(Request $request): string
    {
        return ImageOptimizer::storeAndUrl($request->file('image'), 'pages', 1600);
    }

    private function deleteImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        $relativePath = str_replace('/storage/', '', $path);

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }

    private function uniqueSlug(string $title): string
    {
        $base = 'halaman-'. Str::slug($title);
        $slug = $base;
        $index = 1;
        while (Page::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$index++;
        }

        return $slug;
    }
}
