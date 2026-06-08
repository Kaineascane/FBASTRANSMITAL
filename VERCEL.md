# Deploy on Vercel (Next.js)

The app is now **Next.js + Vercel Postgres**. The old PHP files remain in the repo for reference but are **not** used on Vercel.

## 1. Push to GitHub

Repo: https://github.com/Kaineascane/FBASTRANSMITAL

## 2. Import to Vercel

1. Go to [vercel.com](https://vercel.com) → **Add New Project**
2. Import **FBASTRANSMITAL** from GitHub
3. Framework: **Next.js** (auto-detected)
4. Click **Deploy** (first build may fail until database is added — that's OK)

## 3. Add Vercel Postgres

1. Vercel project → **Storage** → **Create Database** → **Postgres**
2. Connect it to your project (adds `POSTGRES_URL` env vars automatically)
3. **Redeploy**

## 4. Create database tables

1. Vercel project → **Settings** → **Environment Variables**
2. Add `SETUP_SECRET` = any random string (e.g. `my-secret-setup-key`)
3. Redeploy, then open in browser:

```
https://YOUR-PROJECT.vercel.app/api/setup?key=my-secret-setup-key
```

You should see: `{"ok":true,"message":"Database tables created."}`

## 5. Buy / connect your domain

1. Vercel project → **Settings** → **Domains**
2. Add your domain (purchased on Vercel or elsewhere)
3. Follow DNS instructions if domain is external

## 6. Test

| URL | What |
|-----|------|
| `/` | New transmittal form |
| `/search` | Search / reprint |
| `/print/1` | Print slip (after saving one) |

## Local development (no Postgres required)

```bash
npm install
cp .env.example .env.local   # or use the included .env.local
npm run db:setup             # creates data/transmittal.db + tables
npm run dev
```

Open http://localhost:3000 — uses **SQLite** automatically when `POSTGRES_URL` is empty.

Optional: add `POSTGRES_URL` to `.env.local` to use the same Neon/Vercel database locally.

## Legacy PHP

PHP files (`index.php`, `save.php`, etc.) are kept for XAMPP/InfinityFree. For Vercel, only the `app/` Next.js routes are used.
