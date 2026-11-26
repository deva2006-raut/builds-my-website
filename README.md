# AI Builds My Website

A business owner shares what they have (name, city, social links, Google Business listing, PDF price
list, photos). The app reads it, asks an AI to write a real website, lets the owner edit any section,
regenerate a single section, save everything in MySQL, and publish to a public URL.

Laravel 12 + MySQL + Gemini (`gemini-2.0-flash`) via plain HTTP. No API keys in the repo.

Demo video: `demo/website-builder-demo.mp4` (3:30, silent screen walkthrough).
Submission checklist, HR email draft and demo script: see `SUBMISSION.md`.

## What maps to the brief

| Requirement | Where |
| --- | --- |
| Read files and links | `app/Services/SourceExtractor.php` (PDF via `smalot/pdfparser`, images recorded, HTML → text) |
| Search the web | Links supplied by the owner are fetched server-side; every failure is recorded per source instead of guessed |
| Use AI to create a website | `app/Services/SiteGenerator.php` (facts pass → sections pass) |
| Edit, regenerate a section | `SiteController::updateSection`, `SiteController::regenerateSection` |
| Publish to a public URL | `SiteController::publish` + `GET /s/{slug}` (`PublicSiteController`) |
| Save it all in MySQL | `sites`, `sources`, `sections` migrations |
| Never invent facts | System prompt rule 1–2, `conflicts` / `missing` surfaced in the UI; `TextSanitizer` strips injected instructions |
| AI down, graceful failure | `AiClient` retries 429/5xx, throws `AiUnavailable`; site + sources are still saved and the UI asks to retry |

## Edge cases handled

- **Same business name twice** — `Site::uniqueSlug()` suffixes the city, then a counter (`sharma-sweets-nagpur`, `sharma-sweets-nagpur-2`).
- **Sources disagree** — the facts pass returns `conflicts[]`; nothing is auto-picked, the editor shows a warning banner.
- **Social sites block scraping** — 401/403/429 is stored as `blocked` with a reason; generation continues with the readable sources.
- **Instructions hidden in uploads** — `TextSanitizer` drops lines like "ignore previous instructions"; source text is fenced as `<<<DATA … DATA>>>` and the system prompt says data is never instructions.
- **AI down** — retry, then a clear error; edited sections are never overwritten by a failed call.

## Setup

```bash
composer create-project laravel/laravel ai-builds-my-website
cd ai-builds-my-website

# copy this project's files over the skeleton (same paths)
# app/, database/migrations/2026_11_24_*.php, resources/views/{layouts,sites,public}, routes/web.php

composer require smalot/pdfparser
```

Merge the `gemini` block from `config/services-gemini-snippet.php` into `config/services.php`.

Create the database and run migrations:

```sql
CREATE DATABASE ai_builds_my_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Put your Gemini key in `.env` as `GEMINI_API_KEY=...` (free key from Google AI Studio). `.env` is
gitignored; `.env.example` only has empty placeholders.

No Gemini key at hand? `AiClient` also speaks the OpenAI chat-completions shape, so any local model
works for a demo: run `ollama serve` and set `AI_OPENAI_BASE_URL=http://127.0.0.1:11434/v1` and
`AI_OPENAI_MODEL=qwen2.5:3b` in `.env`. Leave those empty in production and Gemini is used.

## Routes

- `GET /sites/create` — new website form
- `POST /sites` — store sources + generate
- `GET /sites/{site}` — editor
- `PUT /sites/{site}/sections/{section}` — save a section
- `POST /sites/{site}/sections/{section}/regenerate` — regenerate one section
- `POST /sites/{site}/publish` — publish
- `GET /s/{slug}` — public website (404 until published)

## Known limits

- No auth layer: the assignment did not ask for accounts, so the editor is open. Add `auth` middleware before exposing it publicly.
- "Search the web" means fetching the links the owner supplied (plus the Google Business page). There is no general web-search API key in this build.
- Images are stored and listed by filename; gallery copy is written from the owner's text, not from image recognition.
- Generated HTML is escaped in Blade; the model never returns raw HTML that gets rendered.

## Verified on this machine

Installed portable PHP 8.4.28 + Composer, MariaDB 11.4.5 (MySQL-compatible) on `127.0.0.1:3307`, ran the
migrations against MySQL, and exercised the app over HTTP:

- `GET /`, `/sites/create`, `/sites/1` → 200; unknown path → 404
- `POST /sites` created `sharma-sweets-nagpur` with its source row saved, then hit the graceful
  "AI is unavailable" path because no Gemini key is configured (expected)
- conflict banner renders when `sites.conflicts` is populated
- `POST /sites/1/publish` → 302, `GET /s/sharma-sweets-nagpur` → 200 with generated content;
  before publishing the same URL is 404
- `PUT /sites/1/sections/1` saved edited copy and the public page showed the new heading

Not exercised: a real Gemini call (no key), file upload of a real PDF, and a two-business end-to-end run.
Put a real `GEMINI_API_KEY` in `.env` before recording the demo video.
