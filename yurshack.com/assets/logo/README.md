# Yur Shack logo

Simple shack mark with **www** in the body — brand greens from the site CSS.

| File | Use |
|------|-----|
| `../mark.svg` | Favicon + header (rounded square) |
| `mark-transparent.svg` | Print on light stock (no square) |
| `lockup.svg` | Mark + “Yur Shack” wordmark |
| `mark-512.png` / `mark-1024.png` | Web / social |
| `mark-3000.png` | Print — icon on green square (~10″ @ 300dpi) |
| `mark-transparent-3000.png` | Print — shack only |
| `lockup-2400.png` / `lockup-3600.png` | Print lockup for cards / letterhead |

SVG is the master (infinite resolution). Prefer SVG for print when the printer accepts it; otherwise use the `*-3000.png` / `lockup-3600.png` rasters.

Rebuild from repo root:

```bash
python3 scripts/build_logo.py
```
