# Vercel + S.I Transmittal

## Important: this app is PHP + MySQL

Vercel runs **Node.js / static / serverless** — it does **not** run PHP with MySQL the way XAMPP or InfinityFree do.

You **cannot** upload `index.php`, `save.php`, and `config.php` to Vercel and expect the transmittal system to work without rewriting the backend.

---

## Option A — Buy domain on Vercel, host PHP elsewhere (recommended, no rewrite)

1. Buy your domain in [Vercel Domains](https://vercel.com/domains).
2. Keep the app on **PHP hosting** (Hostinger, InfinityFree, Railway, etc.).
3. In Vercel → your domain → **DNS**:
   - **A** record `@` → your PHP host IP (from host panel)
   - **CNAME** `www` → your host’s www target (or same A record)
4. On the PHP host, upload this project to `public_html` / `htdocs`.
5. Set `app_url` in `config.php` to `https://yourdomain.com`.

**GitHub repo (deploy PHP from):** https://github.com/Kaineascane/FBASTRANSMITAL

---

## Option B — Full Vercel hosting (requires rewrite)

To run entirely on Vercel you would need to:

- Rebuild the app in **Next.js** (or similar)
- Replace PHP with **API routes**
- Use **Vercel Postgres**, **Neon**, or **Supabase** instead of MySQL

That is a separate development task — not a zip upload.

---

## Option C — Vercel project for docs only

You can connect this GitHub repo to Vercel, but without a Next.js app Vercel will not serve the transmittal form. Use Option A instead.

---

## Quick comparison

| | PHP host + Vercel domain | Vercel-only |
|--|--------------------------|-------------|
| Works with current code | Yes | No — rewrite needed |
| Cost | Domain + free/cheap PHP host | Vercel + database |
| Setup time | ~30 minutes | Days (rewrite) |

---

## Files to deploy (PHP host)

Upload everything except `config.php` (create on server from `config.example.php`).

Import `sql/setup-hosting.sql` in phpMyAdmin, then set database credentials in `config.php`.
