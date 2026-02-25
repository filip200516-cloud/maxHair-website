# Přiřazení obrázků k sekci PROCES (transplantace pro muže)

Složky na Google Disku: [Výstupy](https://drive.google.com/drive/folders/1qENhUZyHrZ8bgNJS8-u-ZGszASLlTwy-)

## Doporučené přiřazení kroků → složka

| Krok | Název     | Doporučená složka na Disku      | Co hledat |
|------|-----------|----------------------------------|-----------|
| 1    | Konzultace | **testimonials** nebo **ppc/statika** | Konzultace online, konzultant, hodnocení fotografií |
| 2    | Příprava   | **ppc/statika**                 | Cestování, letadlo, hotel, transfer, příprava na zákrok |
| 3    | Zákrok     | **ppc/statika** nebo **hotové před/po posty** | Klinika, zákrok, Istanbul, lékař, operace |
| 4    | Hojení     | **hotové před/po posty**        | Hojení, péče, první týdny po zákroku |
| 5    | Růst       | **hotové před/po posty**        | Výsledek, před/po, husté vlasy, finální výsledek |

## Jak doplnit přímé odkazy z Google Disku

1. Otevřete složku na Disku a vyberte vhodný obrázek.
2. Klikněte pravým tlačítkem → **Sdílet** → Nastavte „Kdokoli s odkazem“ může zobrazit.
3. Zkopírujte odkaz. Vypadá např.: `https://drive.google.com/file/d/XXXXXX/view`
4. Z odkazu vezměte **ID souboru** (část mezi `/d/` a `/view`).
5. Přímý odkaz na obrázek pro web:  
   `https://drive.google.com/uc?export=view&id=XXXXXX`
6. V souboru `pages/transplantace-vlasu-muzi.json` v sekci PROCES nahraďte u příslušného kroku v atributu `src` u `<img>` placeholder tímto odkazem.

Obrázky v kódu mají u každého kroku atribut `data-drive-folder` podle této tabulky.
