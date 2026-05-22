# Online Hosting — S.I Transmittal System

Ilagay ang system online para ma-access ng office kahit saan (phone, laptop, ibang branch).

---

## Kailangan ng host

| Feature | Required |
|---------|----------|
| PHP | 7.4 o mas bago (8.x recommended) |
| MySQL / MariaDB | Yes |
| mysqli extension | Yes |
| SSL (HTTPS) | Recommended (libre sa karamihan ng host) |

**Hindi kailangan:** Node.js, VPS (puwede shared hosting)

---

## Recommended hosts (Philippines-friendly)

### Libre (testing / maliit na team)
- [InfinityFree](https://www.infinityfree.com/) — PHP + MySQL, may subdomain
- [000webhost](https://www.000webhost.com/)

### Bayad (mas stable, office use)
- [Hostinger](https://www.hostinger.com/) — mura, madaling cPanel
- [Namecheap](https://www.namecheap.com/) shared hosting
- [Cloudways](https://www.cloudways.com/) — kung gusto mas mabilis

---

## Step-by-step deployment (cPanel style)

### 1. Upload files

1. Login sa **cPanel** o **File Manager** ng host mo.
2. Buksan folder **`public_html`** (o `htdocs` / `www`).
3. Upload **lahat** ng files ng project (ZIP then Extract):
   - `index.php`, `save.php`, `print.php`, `search.php`
   - folders: `css`, `includes`, `assets`, `sql`
   - `config.example.php`, `.htaccess`

**Huwag i-upload:** `start-server.bat`, `scripts/` (optional)

### 2. Gawin ang `config.php`

1. Sa File Manager, **copy** `config.example.php` → rename to **`config.php`**
2. Edit `config.php` gamit ang MySQL details mula sa host:

```php
return [
    'db_host' => 'localhost',           // o sql123.yourhost.com (basahin sa panel)
    'db_user' => 'username_from_host',
    'db_pass' => 'password_from_host',
    'db_name' => 'database_name_from_host',
    'debug' => false,                   // IMPORTANT: false sa live site
];
```

> Sa **InfinityFree**, ang `db_host` ay hindi laging `localhost` — gamitin ang hostname na ibinigay sa MySQL panel (hal. `sql301.infinityfree.com`).

### 3. Create database

1. Sa cPanel → **MySQL Databases**
2. Gumawa ng bagong database (hal. `transmittal_db`)
3. Gumawa ng user + password, i-link sa database (**All Privileges**)
4. Buksan **phpMyAdmin**
5. Piliin ang database → tab **Import** → upload `sql/setup.sql` → **Go**

### 4. Test

Buksan sa browser:

```
https://yourdomain.com/
```

o kung nasa subfolder:

```
https://yourdomain.com/transmittal-system/
```

Dapat lumabas ang form. Subukan mag-save at print.

### 5. Logo

Siguraduhing nandoon ang:

```
assets/img/logo.png
```

### 6. Security (live site)

- `debug` => **false** sa `config.php`
- Pagkatapos mag-setup, **burahin** o i-rename `setup-logo.php` kung na-upload
- Sa susunod: magdagdag ng **login** (optional upgrade)

---

## Upload via FTP (FileZilla)

| Field | Value |
|-------|--------|
| Host | ftp.yourdomain.com |
| Username | cPanel FTP user |
| Password | FTP password |
| Port | 21 |

Upload sa `public_html` — same files as above.

---

## Custom domain (optional)

1. Sa host panel → **Domains** → point domain
2. Enable **Free SSL** (Let's Encrypt) sa cPanel
3. Uncomment HTTPS lines sa `.htaccess` kung gusto auto-redirect to HTTPS

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Blank / 500 error | Check `debug` => true temporarily; tingnan error; ibalik false |
| Database connection failed | Mali ang `db_host`, user, pass, o db name sa `config.php` |
| CSS/logo broken | Wrong folder — files dapat nasa same level as `index.php` |
| Save hindi gumagana | Import ulit `sql/setup.sql`; check MySQL user privileges |

---

## Local vs Online

| | Local (XAMPP) | Online |
|--|---------------|--------|
| URL | http://localhost/transmittal-system/ | https://yourdomain.com/ |
| Config | `config.php` root/password blank | Host MySQL credentials |
| Access | PC mo lang | Buong office (internet) |

---

## Files checklist before upload

- [x] index.php, save.php, print.php, search.php
- [x] includes/ (all PHP)
- [x] css/ (style.css, print.css)
- [x] assets/img/logo.png
- [x] sql/setup.sql (import sa phpMyAdmin, hindi kailangan i-upload permanently)
- [x] config.php (gawin sa server, huwag i-share publicly)
- [x] .htaccess

---

Kung may specific host ka na (Hostinger, InfinityFree, etc.), sabihin mo at puwede tayong gumawa ng exact steps para sa panel nila.
