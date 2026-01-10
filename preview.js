// MaxHair.cz - Local Preview System
// Parsuje JSON soubory z Bricks a renderuje je jako HTML

class MaxHairPreview {
    constructor() {
        this.currentPage = 'homepage';
        this.routes = {
            '/': 'homepage',
            '/vop': 'vop',
            '/gdpr': 'gdpr',
            '/cookies': 'cookies',
            '/dekujeme': 'dekujeme',
            '/kontakt': 'kontakt',
            '/o-nas': 'o-nas',
            '/reference': 'reference',
            '/faq': 'faq',
            '/metoda-dhi': 'metoda-dhi',
            '/metoda-sapphire-fue': 'metoda-sapphire-fue',
            '/transplantace-vlasu-muzi': 'transplantace-vlasu-muzi',
            '/transplantace-vlasu-zeny': 'transplantace-vlasu-zeny',
            '/transplantace-vousu': 'transplantace-vousu',
            '/transplantace-oboci': 'transplantace-oboci',
            '/prp-terapie': 'prp-terapie'
        };
        this.init();
    }

    async init() {
        // Setup routing
        window.addEventListener('popstate', () => this.handleRoute());
        
        // Click handler pro odkazy
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link || !link.href) return;
            
            try {
                const url = new URL(link.href);
                
                // Hash odkazy (scroll na sekci)
                if (url.hash && url.pathname === window.location.pathname) {
                    e.preventDefault();
                    this.scrollToSection(url.hash);
                    return;
                }
                
                // Hash odkazy na homepage
                if (url.hash && (url.pathname === '/' || url.pathname === window.location.pathname)) {
                    e.preventDefault();
                    // Pokud nejsme na homepage, načti ji
                    if (window.location.pathname !== '/') {
                        window.history.pushState({}, '', '/');
                        this.handleRoute().then(() => {
                            setTimeout(() => this.scrollToSection(url.hash), 200);
                        });
                    } else {
                        // Scroll na sekci po načtení
                        setTimeout(() => this.scrollToSection(url.hash), 100);
                    }
                    return;
                }
                
                // Ostatní odkazy na stejné origin
                if (url.origin === window.location.origin) {
                    e.preventDefault();
                    window.history.pushState({}, '', url.pathname + (url.hash || ''));
                    this.handleRoute().then(() => {
                        // Pokud je hash, scroll na sekci
                        if (url.hash) {
                            setTimeout(() => this.scrollToSection(url.hash), 200);
                        }
                    });
                }
            } catch (e) {
                // Pokud není validní URL, nech to projít
                console.warn('Neplatný URL:', link.href);
            }
        });

        // Initial load
        await this.handleRoute();
        
        // Pokud je hash v URL, scroll na sekci
        if (window.location.hash) {
            setTimeout(() => this.scrollToSection(window.location.hash), 300);
        }
    }
    
    scrollToSection(hash) {
        if (!hash) return;
        
        const targetId = hash.substring(1); // Odstraň #
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
            const headerHeight = 110; // Výška fixed headeru
            const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        } else {
            console.warn(`Sekce s ID "${targetId}" nebyla nalezena`);
        }
    }

    async handleRoute() {
        const path = window.location.pathname;
        const page = this.routes[path] || 'homepage';
        this.currentPage = page;
        await this.loadPage(page);
    }

    async loadPage(pageName) {
        const app = document.getElementById('app');
        app.innerHTML = '<div id="loading">Načítání...</div>';

        try {
            if (pageName === 'homepage') {
                await this.loadHomepage();
            } else {
                await this.loadPageFile(`pages/${pageName}.json`);
            }
        } catch (error) {
            console.error('Chyba při načítání stránky:', error);
            app.innerHTML = `
                <div class="error">
                    <h2>Chyba při načítání stránky</h2>
                    <p><strong>Zpráva:</strong> ${error.message}</p>
                    <p><strong>Stack:</strong> ${error.stack}</p>
                    <p>Zkontroluj konzoli prohlížeče (F12) pro více informací.</p>
                </div>`;
        }
    }

    async loadHomepage() {
        const app = document.getElementById('app');
        app.innerHTML = '';

        // Load header (sekvenčně, aby se načetl správně)
        await this.loadComponent('header-maxhair.json');
        
        // Malé zpoždění pro správné načtení
        await this.delay(50);

        // Load homepage hero
        await this.loadComponent('homepage-maxhair.json');
        await this.delay(50);

        // Load all sections
        const sections = [
            '02-problem.json',
            '03-vyhody.json',
            '04-sluzby.json',
            '05-metody.json',
            '06-proces.json',
            '07-zahrnuto.json',
            '08-cenik.json',
            '09-tym.json',
            '10-reference.json',
            '11-faq.json',
            '12-kontakt.json',
            '13-sticky-cta.json'
        ];

        for (const section of sections) {
            await this.loadComponent(`sections/${section}`);
            await this.delay(30); // Malé zpoždění mezi sekcemi
        }

        // Load footer
        await this.loadComponent('footer-maxhair.json');
        
        // Pokud je hash v URL, scroll na sekci po načtení
        if (window.location.hash) {
            setTimeout(() => this.scrollToSection(window.location.hash), 100);
        }
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async loadPageFile(filePath) {
        const app = document.getElementById('app');
        app.innerHTML = '';

        // Load header
        await this.loadComponent('header-maxhair.json');
        await this.delay(50);

        // Load page content (může obsahovat více sekcí)
        await this.loadComponent(filePath);
        await this.delay(50);

        // Load sticky CTA
        await this.loadComponent('sections/13-sticky-cta.json');
        await this.delay(30);

        // Load footer
        await this.loadComponent('footer-maxhair.json');
        
        // Pokud je hash v URL, scroll na sekci po načtení
        if (window.location.hash) {
            setTimeout(() => this.scrollToSection(window.location.hash), 100);
        }
    }

    async loadComponent(filePath) {
        try {
            console.log(`Načítám: ${filePath}`);
            const response = await fetch(filePath);
            if (!response.ok) {
                console.error(`Soubor ${filePath} nenalezen (${response.status})`);
                return;
            }

            const json = await response.json();
            if (!json) {
                console.error(`Prázdný JSON v ${filePath}`);
                return;
            }
            console.log(`JSON načten z ${filePath}, obsahuje ${json.content?.length || 0} položek`);
            this.renderComponent(json);
        } catch (error) {
            console.error(`Chyba při načítání ${filePath}:`, error);
        }
    }

    renderComponent(json) {
        if (!json.content || !Array.isArray(json.content)) {
            console.warn('JSON nemá content array:', json);
            return;
        }

        // Najdi všechny code bloky
        const codeBlocks = json.content.filter(item => item.name === 'code' && item.settings && item.settings.code);
        
        if (codeBlocks.length === 0) {
            console.warn('Žádné code bloky nenalezeny v:', json);
            return;
        }

        console.log(`Načítám ${codeBlocks.length} code bloků`);
        codeBlocks.forEach((codeBlock, index) => {
            const code = codeBlock.settings.code;
            if (!code || code.trim() === '') {
                console.warn(`Prázdný code blok na indexu ${index}`);
                return;
            }
            this.executeCode(code);
        });
    }

    executeCode(code) {
        if (!code || typeof code !== 'string') {
            console.error('Neplatný code:', code);
            return;
        }

        // Vytvoř kontejner pro tento kód
        const container = document.createElement('div');
        container.className = 'bricks-component';

        // Parsuj HTML, CSS a JS z code stringu
        // HTML může být před <style> nebo mezi komentáři
        let htmlContent = '';
        let cssContent = '';
        let jsContent = '';

        // Najdi HTML (vše mezi komentáři a před <style> nebo <script>)
        const htmlBeforeStyle = code.split('<style>')[0];
        const htmlBeforeScript = htmlBeforeStyle.split('<script>')[0];
        // Odstraň pouze HTML komentáře (<!-- ... -->), ale zachovej zbytek obsahu
        htmlContent = htmlBeforeScript
            .replace(/<!--[\s\S]*?-->/g, '') // Odstraň komentáře
            .trim();
        
        // Pokud je HTML prázdné, zkus najít jakýkoliv HTML tag
        if (!htmlContent || htmlContent.length < 10) {
            // Zkus najít první HTML tag
            const htmlTagMatch = code.match(/<[a-z][\s\S]*?>/i);
            if (htmlTagMatch) {
                // Najdi celý HTML obsah před style/script
                const fullHtml = code.substring(0, code.indexOf('<style>') !== -1 ? code.indexOf('<style>') : code.indexOf('<script>') !== -1 ? code.indexOf('<script>') : code.length);
                htmlContent = fullHtml.replace(/<!--[\s\S]*?-->/g, '').trim();
            }
        }

        // Najdi CSS (podporuje i více <style> tagů)
        const cssMatches = code.match(/<style>([\s\S]*?)<\/style>/g);
        if (cssMatches) {
            cssContent = cssMatches.map(match => {
                const content = match.match(/<style>([\s\S]*?)<\/style>/);
                return content ? content[1].trim() : '';
            }).filter(c => c !== '').join('\n');
        }

        // Najdi JS (podporuje i více <script> tagů)
        const jsMatches = code.match(/<script>([\s\S]*?)<\/script>/g);
        if (jsMatches) {
            jsContent = jsMatches.map(match => {
                const content = match.match(/<script>([\s\S]*?)<\/script>/);
                return content ? content[1].trim() : '';
            }).filter(c => c !== '').join('\n');
        }

        // Přidej CSS do head (pouze jednou pro každý unikátní CSS)
        if (cssContent) {
            const styleId = 'style-' + this.hashCode(cssContent);
            if (!document.getElementById(styleId)) {
                const style = document.createElement('style');
                style.id = styleId;
                style.textContent = cssContent;
                document.head.appendChild(style);
            }
        }

        // Přidej HTML
        if (htmlContent && htmlContent.trim() !== '') {
            try {
                container.innerHTML = htmlContent;
                console.log('HTML přidáno:', htmlContent.substring(0, 100) + '...');
            } catch (e) {
                console.error('Chyba při přidávání HTML:', e, htmlContent.substring(0, 200));
            }
        } else {
            console.warn('Prázdný HTML obsah');
        }

        // Přidej JS (každý script zvlášť)
        if (jsContent) {
            const script = document.createElement('script');
            script.textContent = `
                (function() {
                    try {
                        ${jsContent}
                    } catch(e) {
                        console.error('Chyba v komponentě:', e);
                    }
                })();
            `;
            container.appendChild(script);
        }

        // Přidej do DOM
        const app = document.getElementById('app');
        if (app) {
            // Odstraň loading zprávu, pokud existuje
            const loading = app.querySelector('#loading');
            if (loading) {
                loading.remove();
            }
            app.appendChild(container);
        } else {
            console.error('Element #app nenalezen!');
        }
    }

    hashCode(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32bit integer
        }
        return Math.abs(hash).toString(36);
    }
}

// Spusť preview systém
document.addEventListener('DOMContentLoaded', () => {
    new MaxHairPreview();
});

