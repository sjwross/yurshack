#!/usr/bin/env python3
"""Build Yur Shack logo variants: SVG marks + high-res print PNGs."""

from __future__ import annotations

from pathlib import Path

import cairosvg
from fontTools.misc.transform import Transform
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
from fontTools.ttLib import TTFont

ROOT = Path(__file__).resolve().parents[1]
OUT_COM = ROOT / "yurshack.com" / "assets"
OUT_UK = ROOT / "yurshack.co.uk" / "assets"
LOGO_DIR_COM = OUT_COM / "logo"

ACCENT = "#1e3d2f"
CREAM = "#e8f0ea"
SAGE = "#8fbf9a"
INK = "#142019"

NS = 'xmlns="http://www.w3.org/2000/svg"'
INTER_BOLD = Path("/usr/share/fonts/truetype/macos/Inter-Bold.ttf")
# Site wordmark uses Fraunces; fall back to a solid serif for export.
SERIF_BOLD = Path("/usr/share/fonts/truetype/liberation/LiberationSerif-Bold.ttf")
if not SERIF_BOLD.exists():
    SERIF_BOLD = Path("/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf")


def text_path_d(text: str, font_path: Path, px_size: float) -> tuple[str, float, float]:
    """Return SVG path `d`, width, and height (ascender-based) for `text`."""
    font = TTFont(str(font_path))
    glyph_set = font.getGlyphSet()
    cmap = font.getBestCmap()
    units = font["head"].unitsPerEm
    scale = px_size / units

    # Horizontal advance
    width_units = 0
    for ch in text:
        width_units += glyph_set[cmap[ord(ch)]].width

    # Position baseline at y=0, x from 0; flip y for SVG
    pen = SVGPathPen(glyph_set)
    x = 0.0
    for ch in text:
        gname = cmap[ord(ch)]
        tpen = TransformPen(pen, Transform(scale, 0, 0, -scale, x * scale, 0))
        glyph_set[gname].draw(tpen)
        x += glyph_set[gname].width

    os2 = font["OS/2"]
    asc = getattr(os2, "sTypoAscender", None) or font["hhea"].ascent
    desc = getattr(os2, "sTypoDescender", None) or font["hhea"].descent
    height = (asc - desc) * scale
    return pen.getCommands(), width_units * scale, height


def www_path(cx: float, cy: float, px_size: float, color: str) -> str:
    d, w, h = text_path_d("www", INTER_BOLD, px_size)
    # Optical center: Inter lowercase sits slightly below cap height; nudge up a touch.
    x = cx - w / 2
    y = cy + px_size * 0.35
    return f'  <path fill="{color}" transform="translate({x:.2f} {y:.2f})" d="{d}"/>'


def word_path(text: str, x: float, baseline_y: float, px_size: float, color: str, font: Path) -> str:
    d, _, _ = text_path_d(text, font, px_size)
    return f'  <path fill="{color}" transform="translate({x:.2f} {baseline_y:.2f})" d="{d}"/>'


def shack_parts(cx: float, cy: float, scale: float = 1.0) -> dict:
    body_w, body_h = 236 * scale, 168 * scale
    roof_overhang = 28 * scale
    roof_rise = 118 * scale
    body_left = cx - body_w / 2
    body_top = cy - body_h / 2 + 18 * scale
    body_right = body_left + body_w
    roof_left = body_left - roof_overhang
    roof_right = body_right + roof_overhang
    peak = (cx, body_top - roof_rise)
    eaves_y = body_top + 6 * scale
    return {
        "body": (body_left, body_top, body_w, body_h),
        "roof_d": (
            f"M{roof_left:.2f} {eaves_y:.2f} "
            f"L{peak[0]:.2f} {peak[1]:.2f} "
            f"L{roof_right:.2f} {eaves_y:.2f} Z"
        ),
        "www_center": (cx, body_top + body_h * 0.56),
        "www_size": 78 * scale,
    }


def svg_icon_mark() -> str:
    g = shack_parts(256, 268)
    bl, bt, bw, bh = g["body"]
    www = www_path(*g["www_center"], g["www_size"], ACCENT)
    return f'''<svg {NS} viewBox="0 0 512 512" role="img" aria-label="Yur Shack">
  <rect width="512" height="512" rx="112" fill="{ACCENT}"/>
  <path fill="{SAGE}" d="{g["roof_d"]}"/>
  <rect x="{bl:.2f}" y="{bt:.2f}" width="{bw:.2f}" height="{bh:.2f}" fill="{CREAM}"/>
{www}
</svg>
'''


def svg_transparent_mark() -> str:
    g = shack_parts(256, 270, scale=1.05)
    bl, bt, bw, bh = g["body"]
    www = www_path(*g["www_center"], g["www_size"], CREAM)
    return f'''<svg {NS} viewBox="0 0 512 512" role="img" aria-label="Yur Shack">
  <path fill="{ACCENT}" d="{g["roof_d"]}"/>
  <rect x="{bl:.2f}" y="{bt:.2f}" width="{bw:.2f}" height="{bh:.2f}" fill="{ACCENT}"/>
{www}
</svg>
'''


def svg_lockup() -> str:
    # Icon sits in a 224px rounded square; wordmark to the right.
    icon_cx, icon_cy = 128, 128
    g = shack_parts(icon_cx, icon_cy, scale=0.42)
    bl, bt, bw, bh = g["body"]
    www = www_path(*g["www_center"], g["www_size"], ACCENT)

    # Wordmark paths (print-safe, no live fonts required)
    name = word_path("Yur Shack", 280, 148, 68, INK, SERIF_BOLD)
    tag = word_path("WEBSITES & HOSTING", 284, 188, 18, ACCENT, INTER_BOLD)

    return f'''<svg {NS} viewBox="0 0 720 256" role="img" aria-label="Yur Shack">
  <rect x="16" y="16" width="224" height="224" rx="48" fill="{ACCENT}"/>
  <path fill="{SAGE}" d="{g["roof_d"]}"/>
  <rect x="{bl:.2f}" y="{bt:.2f}" width="{bw:.2f}" height="{bh:.2f}" fill="{CREAM}"/>
{www}
{name}
{tag}
</svg>
'''


def write(path: Path, content: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(content, encoding="utf-8")
    print(f"wrote {path}")


def export_png(svg_path: Path, png_path: Path, width: int) -> None:
    png_path.parent.mkdir(parents=True, exist_ok=True)
    cairosvg.svg2png(url=str(svg_path), write_to=str(png_path), output_width=width)
    print(f"wrote {png_path} ({width}px)")


def mirror_to_uk(names: list[str]) -> None:
    for name in names:
        src = OUT_COM / name
        dst = OUT_UK / name
        if src.exists():
            dst.parent.mkdir(parents=True, exist_ok=True)
            dst.write_bytes(src.read_bytes())
            print(f"synced {dst}")


def main() -> None:
    mark_svg = OUT_COM / "mark.svg"
    transparent_svg = LOGO_DIR_COM / "mark-transparent.svg"
    lockup_svg = LOGO_DIR_COM / "lockup.svg"

    write(mark_svg, svg_icon_mark())
    write(transparent_svg, svg_transparent_mark())
    write(lockup_svg, svg_lockup())

    export_png(mark_svg, LOGO_DIR_COM / "mark-1024.png", 1024)
    export_png(mark_svg, LOGO_DIR_COM / "mark-3000.png", 3000)
    export_png(transparent_svg, LOGO_DIR_COM / "mark-transparent-3000.png", 3000)
    export_png(lockup_svg, LOGO_DIR_COM / "lockup-2400.png", 2400)
    export_png(lockup_svg, LOGO_DIR_COM / "lockup-3600.png", 3600)

    # Also drop a favicon-sized PNG for clients that want it
    export_png(mark_svg, LOGO_DIR_COM / "mark-512.png", 512)

    mirror_to_uk(
        [
            "mark.svg",
            "logo/mark-transparent.svg",
            "logo/lockup.svg",
            "logo/mark-512.png",
            "logo/mark-1024.png",
            "logo/mark-3000.png",
            "logo/mark-transparent-3000.png",
            "logo/lockup-2400.png",
            "logo/lockup-3600.png",
        ]
    )
    print("done")


if __name__ == "__main__":
    main()
