import os
import PyPDF2
from pathlib import Path

def extract_text_from_pdf(pdf_path):
    """Extrahuje text z PDF souboru"""
    try:
        with open(pdf_path, 'rb') as file:
            pdf_reader = PyPDF2.PdfReader(file)
            text = ""
            for page in pdf_reader.pages:
                text += page.extract_text() + "\n"
            return text
    except Exception as e:
        return f"CHYBA: {str(e)}"

def analyze_pdfs():
    """Analyzuje všechny PDF soubory v podsložkách"""
    base_path = Path(r"D:\WEB TO PDF\pdf_output")
    folders = ['abclinic.com', 'hairagain.cz', 'newhair.cz', 'premier-clinic.cz', 'transplantacevlasuturecko.cz']
    
    # Klíčové soubory k analýze
    key_files = {
        'index.pdf': 'Hlavní stránka',
        'cenik.pdf': 'Ceník',
        'domu.pdf': 'Domů',
        'sluzby_transplantace_vlasu_muzi.pdf': 'Služby - muži',
        'sluzby_transplantace_vlasu_zeny.pdf': 'Služby - ženy',
        'faq.pdf': 'FAQ',
        'reference.pdf': 'Reference',
        'o_nas.pdf': 'O nás',
        'kontakt.pdf': 'Kontakt',
        'transplantace-vlasu-cena.pdf': 'Cena transplantace',
        'transplantace-vlasu-metodou-dhi.pdf': 'DHI metoda',
        'zakroky_transplantace-vlasu-dhi.pdf': 'Zákrok DHI',
    }
    
    results = {}
    
    for folder in folders:
        folder_path = base_path / folder
        if not folder_path.exists():
            continue
            
        results[folder] = {}
        print(f"\n{'='*60}")
        print(f"ANALÝZA: {folder}")
        print(f"{'='*60}")
        
        # Projdi klíčové soubory
        for filename, description in key_files.items():
            pdf_path = folder_path / filename
            if pdf_path.exists():
                print(f"\n[PDF] {filename} - {description}")
                text = extract_text_from_pdf(pdf_path)
                if text and not text.startswith("CHYBA"):
                    # Ulož prvních 500 znaků pro přehled
                    preview = text[:500].replace('\n', ' ')
                    print(f"   Preview: {preview}...")
                    results[folder][filename] = {
                        'description': description,
                        'text_length': len(text),
                        'preview': preview
                    }
                else:
                    print(f"   [WARNING] Nelze extrahovat text")
        
        # Spočítej všechny PDF soubory
        all_pdfs = list(folder_path.glob("*.pdf"))
        print(f"\n[STATS] Celkem PDF souboru: {len(all_pdfs)}")
        results[folder]['total_pdfs'] = len(all_pdfs)
    
    return results

if __name__ == "__main__":
    results = analyze_pdfs()
    
    # Ulož souhrn
    summary_path = Path(r"D:\maxhair\WEB\zadani\pdf-analyza-souhrn.txt")
    with open(summary_path, 'w', encoding='utf-8') as f:
        f.write("SOUHRN ANALÝZY PDF SOUBORŮ\n")
        f.write("="*60 + "\n\n")
        for folder, data in results.items():
            f.write(f"{folder}:\n")
            f.write(f"  Celkem PDF: {data.get('total_pdfs', 0)}\n")
            f.write(f"  Analyzované klíčové soubory: {len([k for k in data.keys() if k != 'total_pdfs'])}\n\n")
    
    print(f"\n[OK] Analyza dokoncena. Souhrn ulozen do: {summary_path}")

