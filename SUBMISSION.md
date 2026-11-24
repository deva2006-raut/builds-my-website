# Assignment submission — Websites.co.in / InstaWeb Labs

Deadline: **30 November 2026** · Send to **hr@websites.co.in**

## Status

| Item | Status |
| --- | --- |
| Laravel + MySQL app working locally | Done — runs on `php artisan serve`, verified create → edit → regenerate-failure → publish → public page |
| `README.md` | Done |
| `DECISIONS.md` | Done |
| `.env.example` (no secrets) | Done |
| Git repo + first commit | Done locally (branch `main`, 77 tracked files, `.env` ignored) |
| Push to GitHub | **Pending — needs your GitHub login** (`gh auth login`, then `git remote add origin … && git push -u origin main`) |
| 5-min demo video | **Pending — screen + voice, cannot be generated for you** |
| Email to HR | Draft below; send once the two links exist |

## Email draft

Subject: `Full Stack Development Internship — Assignment Submission (Devanshu Raut)`

```
Hi HR Team,

Thank you for the Full Stack Development assignment. Please find my submission below.

GitHub repository: <repo link>
Demo video (5 min): <video link>

The app is "AI Builds My Website" — Laravel + MySQL. A business owner shares a name, city,
social/Google Business links, a PDF price list and photos; the app reads those sources, asks
Gemini to write the site, lets the owner edit or regenerate a single section, and publishes
to a public URL. Facts are only taken from the sources: conflicts between sources are shown
to the owner instead of being auto-picked, and missing fields are left empty.

Expected stipend: ₹<amount> per month
Earliest joining date: <date>

DECISIONS.md in the repo covers the key choices, what I cut, and what I would do next.

Regards,
Devanshu Raut
devanshuraut8767@gmail.com
+91 87671 30074
github.com/deva2006-raut
```

## Demo video script (5 minutes)

1. **0:00–0:30** — Problem in one sentence, then the stack (Laravel 12, MySQL, Gemini 2.0 Flash).
2. **0:30–1:30** — Business #1: paste a real shop's Instagram + Google listing + a PDF price list, submit. Show the sources table filling up, including any `blocked` row.
3. **1:30–2:30** — Show the conflict banner ("two phone numbers found") and the missing-facts panel. Explain that nothing is invented.
4. **2:30–3:30** — Edit the hero, regenerate only the "services" section with an instruction, show the other sections unchanged.
5. **3:30–4:15** — Publish, open the public URL in a second tab.
6. **4:15–4:45** — Business #2 (different city, same app), publish again — shows duplicate-name slug handling.
7. **4:45–5:00** — `DECISIONS.md`: what was cut and what is next.

Skip nothing that fails: if a source is blocked or the AI errors, show the graceful path — that is a
graded edge case.

## Local run (already verified on this machine)

```bash
cd ai-builds-my-website
php artisan serve --host=127.0.0.1 --port=8000
```

Local MySQL-compatible server: MariaDB 11.4.5 on `127.0.0.1:3307`, database `ai_builds_my_website`,
user `root`. Portable PHP 8.4.28 lives in `../tools/php`.

Gemini key is empty in `.env`, so generation currently ends in the graceful "AI is unavailable" path —
put a real key in `GEMINI_API_KEY` before recording the video.
