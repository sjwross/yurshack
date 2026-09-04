# Yur Shack main websites

Source for **yurshack.com** and **yurshack.co.uk**.

## Layout

```
yurshack.com/     # site files for yurshack.com
yurshack.co.uk/   # site files for yurshack.co.uk
deploy.sh         # rsync/tar deploy to projtoolbox.com
```

Both domains start as **identical copies**. Edit either folder when they should stay in sync (copy changes across), or let them diverge independently.

Today both hostnames still serve one shared tree on the server (`public_html/yurshack/`) via cPanel aliases + host-based `.htaccess` on `projtoolbox.com`. `deploy.sh` therefore publishes **`yurshack.com/`** to that path while the sites match.

When the domains need different content:

1. Keep editing each folder separately.
2. Point deploy (or add a second target) at separate remote dirs, e.g. `public_html/yurshack-com/` and `public_html/yurshack-co-uk/`, and update host routing on the server.

Do **not** commit server `.htaccess` from `public_html/` — it may contain secrets (API keys). Keep routing/secrets only on the host.

## Deploy

```bash
./deploy.sh              # default SSH host: projtoolbox.com
./deploy.sh other-host   # optional override
```

Requires SSH access configured for the hosting account (see `~/.ssh/config` host `projtoolbox.com`).
