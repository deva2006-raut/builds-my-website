<?php

namespace App\Http\Controllers;

use App\Models\Site;

class PublicSiteController extends Controller
{
    public function show(string $slug)
    {
        $site = Site::with('sections')->where('slug', $slug)->firstOrFail();

        // Unpublished sites are drafts: show owners a hint, hide from the world.
        abort_unless($site->isPublished(), 404);

        return view('public.show', compact('site'));
    }
}
