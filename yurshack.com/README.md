# Yur Shack website (yurshack.com / yurshack.co.uk)

PHP site for websites & hosting offerings, with contact/order forms and a support admin.

## Local folders

- `yurshack.com/` — canonical site (deployed)
- `yurshack.co.uk/` — kept in sync while domains share one server tree

## Features

- Public pages: home, services & pricing, order form, contact, terms overview
- Contact + order emails to `support@yurshack.com` (PHPMailer when available, else `mail()`)
- Orders stored in PostgreSQL (`sql/schema.sql`)
- Support admin at `/admin/` — password login, search/filter orders, update status

Pricing matches the Master Website Service Agreement standard rates.

## Server setup

1. Deploy with `./deploy.sh` (publishes `yurshack.com/` → `public_html/yurshack/`).
2. Create a PostgreSQL database/user and run `sql/schema.sql`.
3. Copy `.env.example` to `.env` in the site directory (or parent) and set:
   - `POSTGRES_DSN`, `POSTGRES_USER`, `POSTGRES_PASS`
   - `SMTP_*` (optional but recommended)
   - `ADMIN_PASSWORD` (required for `/admin/`)
   - `MAIL_TO=support@yurshack.com`
4. Ensure PHP has `pdo_pgsql` and that Composer `vendor/autoload.php` is reachable for PHPMailer (typically `~/public_html/vendor`).

## Deploy

```bash
./deploy.sh              # default SSH host: projtoolbox.com
./deploy.sh other-host   # optional override
```

Do not commit `.env` or server secrets. Keep both domain folders in sync while they share one remote path.
