"""
Upraví pozadí proces-5-rust-muzi.png na barvu pozadí z proces-5-rust.png
(light cream #F8F7F0), aby vizuálně ladilo s ostatními procesními obrázky.
"""
import sys
from pathlib import Path

try:
    from PIL import Image
except ImportError:
    print("Instalace Pillow: pip install Pillow")
    sys.exit(1)

# Cesty
PROJECT = Path(__file__).resolve().parent.parent
SRC = PROJECT / "assets" / "proces-5-rust-muzi.png"
OUT = PROJECT / "assets" / "proces-5-rust-muzi.png"

# Barva pozadí z proces-5-rust.png (light cream, mírně teplá bílá)
TARGET_BG = (248, 247, 240)  # #F8F7F0

# Rozsah pro „staré“ pozadí (světlá krémová / béžová) – nahradíme ji
# R,G,B všechny v tomto rozsahu = považujeme za pozadí
BG_MIN = (200, 195, 180)
BG_MAX = (255, 255, 255)

# Tolerance: pixel je pozadí, pokud všechny kanály jsou v rozsahu
def is_background(pixel):
    if len(pixel) == 4 and pixel[3] < 128:
        return True  # průhlednost
    r, g, b = pixel[0], pixel[1], pixel[2]
    return (
        BG_MIN[0] <= r <= BG_MAX[0]
        and BG_MIN[1] <= g <= BG_MAX[1]
        and BG_MIN[2] <= b <= BG_MAX[2]
    )


def main():
    if not SRC.exists():
        print(f"Soubor nenalezen: {SRC}")
        sys.exit(1)

    img = Image.open(SRC).convert("RGBA")
    data = img.load()
    w, h = img.size
    replaced = 0

    for y in range(h):
        for x in range(w):
            p = data[x, y]
            if is_background(p):
                data[x, y] = (*TARGET_BG, 255)
                replaced += 1

    img.save(OUT, "PNG")
    print(f"Hotovo: {replaced} px pozadí nahrazeno barvou #F8F7F0")
    print(f"Uloženo: {OUT}")


if __name__ == "__main__":
    main()
