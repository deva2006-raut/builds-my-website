<?php

namespace App\Services;

/**
 * Uploaded PDFs, scraped pages and pasted notes are untrusted input.
 * They may contain sentences aimed at the model ("ignore previous instructions").
 * We drop those lines, cap the length, and always hand the text to the model
 * wrapped as quoted data (see SiteGenerator::sourceBlock).
 */
class TextSanitizer
{
    private const SUSPICIOUS = [
        'ignore (all )?(previous|prior|above) instructions',
        'disregard (the )?(system|previous)',
        'you are now',
        'system prompt',
        'reveal (your|the) (prompt|instructions)',
        'api[_ ]?key',
        'exfiltrate',
        'send .{0,30}(data|credentials) to',
    ];

    public function clean(string $text): string
    {
        $text = str_replace("\0", '', $text);
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];

        $kept = [];
        foreach ($lines as $line) {
            if ($this->isInstruction($line)) {
                continue;
            }
            $kept[] = $line;
        }

        $clean = trim(implode("\n", $kept));

        // Keep the prompt small and predictable.
        return mb_substr($clean, 0, 12_000);
    }

    public function isInstruction(string $line): bool
    {
        $line = mb_strtolower($line);

        foreach (self::SUSPICIOUS as $pattern) {
            if (preg_match('/' . $pattern . '/u', $line)) {
                return true;
            }
        }

        return false;
    }
}
