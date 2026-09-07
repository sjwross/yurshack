# Yur Shack main websites

Source for **yurshack.com** and **yurshack.co.uk**.

## Layout

```
yurshack.com/     # canonical PHP site (deploy target)
yurshack.co.uk/   # kept in sync while domains share one server tree
deploy.sh         # rsync/tar deploy to projtoolbox.com
```

Both domains start as **identical copies**. Edit `yurshack.com/` then sync to `.co.uk`, or let them diverge later with separate remote dirs.

Today both hostnames still serve one shared tree on the server (`public_html/yurshack/`) via cPanel aliases. `deploy.sh` publishes **`yurshack.com/`** to that path.

## What the site includes

- Services & standard pricing from the Master Website Service Agreement
- Contact form → `support@yurshack.com`
- Order form → email + PostgreSQL `orders` table
- Support admin at `/admin/` to search and update orders

See `yurshack.com/README.md` and `yurshack.com/.env.example` for server setup (`sql/schema.sql`, SMTP, admin password).

## Deploy

```bash
./deploy.sh              # default SSH host: projtoolbox.com
./deploy.sh other-host   # optional override
```

Requires SSH access for the hosting account. Do **not** commit `.env` or server `.htaccess` secrets from `public_html/`.
