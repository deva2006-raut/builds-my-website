<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

/**
 * Turns "what the business owner shares" into plain text we can feed the model.
 *
 * Every source is stored with its own status, so one blocked social page never
 * takes the whole generation down.
 */
class SourceExtractor
{
    private const MAX_CHARS_PER_SOURCE = 12_000;

    public function __construct(private readonly TextSanitizer $sanitizer)
    {
    }

    /**
     * @param  array<int, string>  $links
     * @param  array<int, UploadedFile>  $files
     */
    public function collect(Site $site, array $links, array $files, ?string $note): void
    {
        foreach ($links as $link) {
            $link = trim($link);
            if ($link === '') {
                continue;
            }
            $this->fromUrl($site, $link);
        }

        foreach ($files as $file) {
            $this->fromFile($site, $file);
        }

        if (is_string($note) && trim($note) !== '') {
            $site->sources()->create([
                'type' => 'note',
                'origin' => 'owner note',
                'status' => 'ok',
                'text' => $this->sanitizer->clean(trim($note)),
            ]);
        }
    }

    public function fromUrl(Site $site, string $url): void
    {
        $type = $this->guessType($url);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; WebsiteBuilderBot/1.0)',
                'Accept' => 'text/html,application/json;q=0.9,*/*;q=0.8',
            ])->timeout(20)->get($url);

            if (in_array($response->status(), [401, 403, 429], true)) {
                $site->sources()->create([
                    'type' => $type,
                    'origin' => $url,
                    'status' => 'blocked',
                    'error' => 'Source refused automated access (HTTP ' . $response->status() . ').',
                ]);

                return;
            }

            if (! $response->successful()) {
                $site->sources()->create([
                    'type' => $type,
                    'origin' => $url,
                    'status' => 'failed',
                    'error' => 'HTTP ' . $response->status(),
                ]);

                return;
            }

            $site->sources()->create([
                'type' => $type,
                'origin' => $url,
                'status' => 'ok',
                'text' => $this->sanitizer->clean($this->htmlToText($response->body())),
            ]);
        } catch (\Throwable $e) {
            $site->sources()->create([
                'type' => $type,
                'origin' => $url,
                'status' => 'failed',
                'error' => substr($e->getMessage(), 0, 250),
            ]);
        }
    }

    public function fromFile(Site $site, UploadedFile $file): void
    {
        $path = null;

        try {
            $path = $file->store('uploads/' . $site->id);
            $absolute = Storage::path($path);
            $mime = $file->getMimeType() ?: '';
            $text = '';

            if ($mime === 'application/pdf' || str_ends_with(strtolower($file->getClientOriginalName()), '.pdf')) {
                $text = (new Parser())->parseFile($absolute)->getText();
            } elseif (str_starts_with($mime, 'image/')) {
                // Phone photos carry no text we can trust; record them so the
                // gallery section can still use them by filename.
                $text = 'Photo supplied by the owner: ' . $file->getClientOriginalName();
            } else {
                $text = (string) @file_get_contents($absolute);
            }

            $site->sources()->create([
                'type' => str_starts_with($mime, 'image/') ? 'image' : 'pdf',
                'origin' => $file->getClientOriginalName(),
                'status' => 'ok',
                'text' => $this->sanitizer->clean($text),
            ]);
        } catch (\Throwable $e) {
            $site->sources()->create([
                'type' => 'pdf',
                'origin' => $file->getClientOriginalName(),
                'status' => 'failed',
                'error' => substr($e->getMessage(), 0, 250),
            ]);
        }
    }

    private function guessType(string $url): string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return match (true) {
            str_contains($host, 'google.') => 'google_business',
            str_contains($host, 'instagram.'), str_contains($host, 'facebook.'),
            str_contains($host, 'linkedin.'), str_contains($host, 'x.com'),
            str_contains($host, 'twitter.') => 'social',
            default => 'url',
        };
    }

    private function htmlToText(string $html): string
    {
        $html = preg_replace('/<(script|style|noscript)\b[^>]*>.*?<\/\1>/is', ' ', $html) ?? '';
        $html = preg_replace('/<!--.*?-->/s', ' ', $html) ?? '';
        // Cloudflare hides addresses as <a href="/cdn-cgi/l/email-protection">[email protected]</a>.
        // The placeholder is not a real fact, so drop the whole link instead of feeding it to the model.
        $html = preg_replace('/<a\b[^>]*email-protection[^>]*>.*?<\/a>/is', ' ', $html) ?? '';
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);
        $text = preg_replace('/\[email\s*protected\]/i', '', $text) ?? $text;
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return mb_substr(trim($text), 0, self::MAX_CHARS_PER_SOURCE);
    }
}
