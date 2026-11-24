<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Site;
use App\Services\AiClient;
use App\Services\AiUnavailable;
use App\Services\SiteGenerator;
use App\Services\SourceExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SiteController extends Controller
{
    public function create()
    {
        return view('sites.create');
    }

    /**
     * Step 1: record what the owner shared, then run the extraction pass.
     * If the AI is down the site is still saved so nothing is lost.
     */
    public function store(Request $request, SourceExtractor $extractor, SiteGenerator $generator)
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:80'],
            'links' => ['nullable', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:5000'],
            'files.*' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,txt,csv'],
        ]);

        $site = Site::create([
            'slug' => Site::uniqueSlug($data['business_name'], $data['city'] ?? null),
            'business_name' => $data['business_name'],
            'city' => $data['city'] ?? null,
            'status' => 'draft',
        ]);

        $links = collect(preg_split('/[\r\n,]+/', (string) ($data['links'] ?? '')))
            ->map(fn ($link) => trim($link))
            ->filter()
            ->values()
            ->all();

        $extractor->collect($site, $links, $request->file('files') ?? [], $data['note'] ?? null);

        try {
            $profile = $generator->buildProfile($site);
            $sections = $generator->buildSections($site, $profile);
            $generator->persist($site, $profile, $sections);

            $message = 'Website created. Review it, edit anything that looks wrong, then publish.';
        } catch (AiUnavailable $e) {
            Log::warning('Generation failed', ['site' => $site->id, 'error' => $e->getMessage()]);

            return redirect()
                ->route('sites.edit', $site)
                ->with('error', 'AI is unavailable right now, so the site was saved without content. Your sources are stored — press "Generate" to retry.');
        }

        $blocked = $site->sources()->whereIn('status', ['blocked', 'failed'])->count();
        if ($blocked > 0) {
            $message .= ' ' . $blocked . ' source(s) could not be read — check the source list.';
        }

        return redirect()->route('sites.edit', $site)->with('status', $message);
    }

    public function edit(Site $site)
    {
        return view('sites.edit', compact('site'));
    }

    /**
     * Retry the full generation (used when the AI was down, or after adding sources).
     */
    public function generate(Site $site, SiteGenerator $generator)
    {
        try {
            $profile = $generator->buildProfile($site);
            $sections = $generator->buildSections($site, $profile);
            $generator->persist($site, $profile, $sections);
        } catch (AiUnavailable $e) {
            return back()->with('error', 'AI is unavailable: ' . $e->getMessage());
        }

        return back()->with('status', 'Website regenerated from all sources.');
    }

    public function updateSection(Request $request, Site $site, Section $section)
    {
        abort_unless($section->site_id === $site->id, 404);

        $data = $request->validate([
            'heading' => ['nullable', 'string', 'max:160'],
            'subheading' => ['nullable', 'string', 'max:240'],
            'body' => ['nullable', 'string', 'max:4000'],
            'items' => ['nullable', 'string', 'max:4000'],
        ]);

        $items = collect(preg_split('/[\r\n]+/', (string) ($data['items'] ?? '')))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();

        $section->update([
            'content' => [
                'heading' => (string) ($data['heading'] ?? ''),
                'subheading' => (string) ($data['subheading'] ?? ''),
                'body' => (string) ($data['body'] ?? ''),
                'items' => $items,
            ],
        ]);

        return back()->with('status', ucfirst($section->key) . ' section saved.');
    }

    public function regenerateSection(Request $request, Site $site, Section $section, SiteGenerator $generator)
    {
        abort_unless($section->site_id === $site->id, 404);

        $data = $request->validate(['instruction' => ['nullable', 'string', 'max:500']]);

        try {
            $generator->regenerateSection($site, $section, $data['instruction'] ?? null);
        } catch (AiUnavailable $e) {
            return back()->with('error', 'Could not regenerate: ' . $e->getMessage());
        }

        return back()->with('status', ucfirst($section->key) . ' section regenerated.');
    }

    public function publish(Site $site)
    {
        if ($site->sections()->count() === 0) {
            return back()->with('error', 'Generate the website before publishing.');
        }

        $site->update([
            'status' => 'published',
            'published_at' => $site->published_at ?? now(),
        ]);

        return back()->with('status', 'Published at ' . $site->publicUrl());
    }

    public function unpublish(Site $site)
    {
        $site->update(['status' => 'draft']);

        return back()->with('status', 'Site is offline again.');
    }

    public function index()
    {
        $sites = Site::latest()->paginate(20);

        return view('sites.index', compact('sites'));
    }
}
