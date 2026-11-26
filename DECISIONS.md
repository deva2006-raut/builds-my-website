# DECISIONS

## What I chose

**Laravel + MySQL, server-rendered Blade, no JS framework.**
The brief is a CRUD app with three AI calls. A Blade app with one form per section is the shortest path
that a reviewer can run with `php artisan serve`. No build step, no npm install.

**Gemini 2.0 Flash over raw HTTP instead of an SDK.**
One endpoint, JSON response mode, no extra dependency. `responseMimeType: application/json` removes the
"model wrapped JSON in prose" failure mode; a small `stripFences()` still guards it.

**Two AI passes instead of one.**
Pass 1 extracts facts (`profile` + `conflicts` + `missing`), pass 2 writes copy from those facts only.
A single pass happily invents a phone number while writing the hero. Splitting them means the facts
that reach the copywriter are already the ones extracted from real sources, and conflicts can be shown
to the owner before anything is published.

**Sections as rows, not one HTML blob.**
`regenerate-section` is a core requirement. Row per section means regenerating "services" cannot
silently rewrite the hero, and edits survive regeneration elsewhere.

**Sources as rows with their own status.**
`ok | blocked | failed` plus an error message. Instagram blocks bots constantly; if one fetch killed
generation, almost every real business would fail. The editor shows exactly what was read.

**Prompt-injection defence, not trust.**
Uploaded PDFs and scraped pages are fenced as `<<<DATA … DATA>>>`, the system prompt states data is
never instructions, and `TextSanitizer` drops lines matching "ignore previous instructions",
"system prompt", "reveal your instructions", "api key", etc. Generated copy is escaped by Blade, so the
model cannot inject markup either.

**Conflicts are surfaced, never resolved silently.**
Two phone numbers → `conflicts: [{field, values, sources}]` and a yellow banner. Picking one at random
is how a website ends up advertising the wrong number.

**Graceful AI failure.**
Two retries on 429/5xx, then `AiUnavailable`. The site and its sources are already persisted, so the
owner loses nothing and can press "Regenerate everything" later.

**The provider is a seam, not a hard dependency.**
`AiClient` builds either the Gemini `generateContent` request or, when `AI_OPENAI_BASE_URL` is set, an
OpenAI-style `chat/completions` request. Same `json()` contract either way, so the generator does not
care which model answers. It let me record the demo on a local model when no Gemini key was available,
and it is the first thing a reviewer can swap for another provider.

**Scraped HTML is cleaned before it reaches the model.**
Cloudflare replaces addresses with `[email protected]`; that placeholder is not a fact, so the extractor
drops the link rather than letting the copywriter turn it into an email address.

**No secrets in the repo.**
`GEMINI_API_KEY` comes from `.env`; `.env.example` ships empty values; `.env` is gitignored.

## What I cut

- **Auth / multi-tenant accounts.** Not in the brief; an open editor is fine for a demo. Noted in the README as the first thing to add before real hosting.
- **General web search.** "Search the web" is implemented as fetching the links the owner gave (plus their Google Business page). A real search API needs a second paid key and quota handling; the sources row model is where that would plug in.
- **Image understanding.** Photos are stored and referenced by filename; no vision pass. Logo/text-on-photo extraction was not worth the token cost for this round.
- **Queues for generation.** The three AI calls run in the request (~5–15 s). A `ShouldQueue` job with polling is the right answer for production, but it doubles the UI work for a demo.
- **Custom domain publishing.** Publishing gives `APP_URL/s/{slug}`. Real DNS/TLS is out of scope.
- **Tests.** No test suite was added; the flows to smoke-test are listed in the README.

## What's next

1. Auth + per-owner scoping (one user = many businesses).
2. Queue the generation, show progress, notify by email.
3. Real web search provider for verification of facts (address, hours) against Google Business.
4. Draft autosave + version history per section, so regeneration is undoable.
5. Custom domain / CDN publishing and a sitemap.
6. Vision pass on uploaded photos (menu boards, price lists in image form).
