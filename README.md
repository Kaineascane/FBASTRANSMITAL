# S.I Transmittal System

PHP + MySQL web app for branch transmittal slips (create, search, print).

**Repository:** https://github.com/Kaineascane/FBASTRANSMITAL

---

## Requirements (hosting)

| Requirement | Notes |
|-------------|--------|
| PHP 7.4+ (8.x recommended) | With **mysqli** extension |
| MySQL or MariaDB | Database created in host panel |
| Apache (typical) | `.htaccess` supported |
| HTTPS | Enable free SSL on your domain |

**Not required:** Node.js, XAMPP on the server

---

## Publish with your domain (cPanel / Hostinger / etc.)

### 1. Push this repo to GitHub

On your PC (Git Bash or terminal), in the project folder:

```bash
git add .
git commit -m "Prepare for domain hosting"
git push origin main
```

Share the repo link: **https://github.com/Kaineascane/FBASTRANSMITAL**

### 2. Get hosting + domain

1. Buy hosting + domain (e.g. [Hostinger](https://www.hostinger.com/), Namecheap, InfinityFree for testing).
2. Point your domain to the host (nameservers from the host panel).
3. Enable **SSL (HTTPS)** — Let's Encrypt in cPanel.

### 3. Upload files to `public_html`

**Option A — ZIP upload (easiest)**

1. Download the repo as ZIP from GitHub (**Code → Download ZIP**).
2. Extract locally.
3. Upload **all files and folders** inside the extracted folder to **`public_html`** (not the ZIP wrapper folder name only).
4. After upload, `public_html` should contain `index.php`, `includes/`, `css/`, `assets/`, etc.

**Option B — Git on host (if available)**

```bash
cd public_html
git clone https://github.com/Kaineascane/FBASTRANSMITAL.git .
```

**Option C — FTP (FileZilla)**

- Host: `ftp.yourdomain.com`
- Upload everything to `public_html`

### 4. Create the database

1. cPanel → **MySQL Databases**
2. Create database (e.g. `youruser_transmittal`)
3. Create user + password → add user to database with **All Privileges**
4. Open **phpMyAdmin** → select your database → **Import** → `sql/setup-hosting.sql` → **Go**

> Use `setup-hosting.sql` on shared hosting. Use `sql/setup.sql` only for local XAMPP (includes `CREATE DATABASE`).

### 5. Create `config.php` on the server

In File Manager, copy `config.example.php` → rename to **`config.php`**.

Edit with values from your host panel:

```php
<?php
return [
    'db_host' => 'localhost',              // or sql###.yourhost.com
    'db_user' => 'your_mysql_username',
    'db_pass' => 'your_mysql_password',
    'db_name' => 'your_database_name',
    'debug' => false,                      // MUST be false on live site
];
```

**Do not commit `config.php` to GitHub** — it is in `.gitignore`.

### 6. Test your domain

Open:

```
https://yourdomain.com/
```

You should see the transmittal form. Test save and print.

### 7. After go-live

- Set `debug` => `false` in `config.php`
- Delete or rename `setup-logo.php` on the server
- Uncomment HTTPS redirect in `.htaccess` when SSL works

---

## File layout (after upload)

```
public_html/
├── index.php
├── save.php, print.php, search.php
├── config.php          ← create on server only
├── config.example.php
├── .htaccess
├── includes/
├── css/
├── assets/img/logo.png
└── sql/                ← import once, optional to delete after
```

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| 500 / blank page | Temporarily set `debug` => true; fix DB credentials; set back to false |
| Database connection failed | Check `db_host`, user, password, database name in `config.php` |
| CSS or logo broken | Files must be in same folder as `index.php` (root of `public_html`) |
| Save fails | Re-import `sql/setup-hosting.sql`; check user privileges |

More detail: [HOSTING.md](HOSTING.md)

---

## License / use

Internal office use. Add login before exposing to the public internet if needed.
