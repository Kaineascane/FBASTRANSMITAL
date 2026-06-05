# Online Hosting — S.I Transmittal System

Deploy to your **domain** so the office can use `https://yourdomain.com/` from anywhere.

**Repository:** https://github.com/Kaineascane/FBASTRANSMITAL

---

## Quick checklist

- [ ] Hosting with PHP + MySQL
- [ ] Domain pointed to host + SSL enabled
- [ ] All project files in `public_html` (see README.md)
- [ ] Database created + `sql/setup-hosting.sql` imported
- [ ] `config.php` created on server (from `config.example.php`)
- [ ] `debug` => **false** on live site
- [ ] Remove `setup-logo.php` after logo is in place

---

## Recommended hosts

| Type | Examples |
|------|----------|
| Free (testing) | [InfinityFree](https://www.infinityfree.com/), [000webhost](https://www.000webhost.com/) |
| Paid (office) | [Hostinger](https://www.hostinger.com/), Namecheap, Cloudways |

---

## Database import

**Shared hosting:** use `sql/setup-hosting.sql` (tables only).

**Local XAMPP only:** use `sql/setup.sql` (includes `CREATE DATABASE`).

---

## Custom domain + HTTPS

1. Host panel → attach domain to `public_html`
2. Enable **Let's Encrypt** SSL
3. In `.htaccess`, uncomment the HTTPS redirect lines when SSL is active
4. Set `app_url` in `config.php` to your live domain (see below)

---

## Hostinger domain + InfinityFree hosting (your setup)

You can keep the app on **InfinityFree** and use a **free domain from Hostinger**. The domain is connected in DNS and in `config.php` — not by changing PHP file paths.

### 1. Add the domain on InfinityFree

1. Log in to [InfinityFree](https://www.infinityfree.com/) → your account (e.g. `if0_42101552`).
2. Open the site → **Add Domain** / **Parked Domain**.
3. Enter your Hostinger domain exactly (e.g. `fbastransmittal.com`).

### 2. Point Hostinger DNS to InfinityFree

In **Hostinger** → **Domains** → your domain → **DNS**:

| Type | Name | Value |
|------|------|--------|
| **A** | `@` | `185.27.134.33` (your InfinityFree website IP from the account panel) |
| **A** or **CNAME** | `www` | Same IP, or CNAME to your root domain |

Save and wait up to 24–48 hours for DNS to propagate (often much faster).

### 3. Enable SSL on InfinityFree

InfinityFree control panel → **SSL Certificates** → issue free SSL for your custom domain.

### 4. Configure the app (`config.php` on the server)

Copy from `config.example.php` and set your real domain:

```php
'app_url' => 'https://your-actual-domain.com',
'force_https' => true,
'allow_infinityfree_fallback' => true,   // true while testing; false after DNS works
```

Upload the updated `includes/domain.php` and `includes/database.php` with your project files.

- While `allow_infinityfree_fallback` is **true**, both `https://yourdomain.com` and `https://fbastransmittal.infinityfree.io` work.
- After your Hostinger domain opens the site correctly, set `allow_infinityfree_fallback` to **false** so visitors are always sent to your branded domain.

### 5. Test

1. `https://yourdomain.com/` — transmittal form loads.
2. `https://fbastransmittal.infinityfree.io/` — still works until you disable fallback.
3. Uncomment HTTPS rules in `.htaccess` if redirects are not already handled by PHP.

---

## Security (live)

- Never commit `config.php` to Git
- `debug` => **false** in production
- Delete `setup-logo.php` after setup
- Consider adding login for public-facing domains

---

## GitHub → live site flow

```
GitHub repo  →  Download ZIP or git clone  →  public_html  →  config.php + MySQL  →  https://yourdomain.com/
```

Full steps: [README.md](README.md)
