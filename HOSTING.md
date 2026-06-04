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
