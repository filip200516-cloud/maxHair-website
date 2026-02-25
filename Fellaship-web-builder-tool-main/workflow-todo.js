/**
 * TODO systém pro Fellaship Web Builder Tool
 * Tento modul poskytuje strukturovaný TODO list pro automatický workflow
 */

export const WORKFLOW_TODOS = {
  // FÁZE 0: Počáteční setup
  INITIAL: [
    {
      id: 'init-1',
      title: 'Pullnout repozitář z GitHubu',
      description: 'Uživatel pullne tool z GitHubu do Cursoru',
      status: 'pending',
      userAction: false
    },
    {
      id: 'init-2',
      title: 'Načíst dokumentaci a zjistit workflow',
      description: 'AI načte CURSOR-AI-GUIDE.md a další dokumentaci',
      status: 'pending',
      userAction: false
    },
    {
      id: 'init-3',
      title: 'Zkontrolovat npm závislosti',
      description: 'Spustit npm install pokud node_modules neexistuje',
      status: 'pending',
      userAction: false
    }
  ],

  // FÁZE 1: Získání přístupů
  GET_ACCESS: [
    {
      id: 'access-1',
      title: 'Požádat uživatele o WordPress přístupy',
      description: 'URL, username, Application Password',
      status: 'pending',
      userAction: true,
      instructions: [
        'Řekni uživateli: "Potřebuji WordPress přístupy:"',
        '- URL webu (např. https://example.com)',
        '- Username (email nebo uživatelské jméno)',
        '- Application Password (viz detailní instrukce níže)'
      ]
    },
    {
      id: 'access-2',
      title: 'Poskytnout DETAILNÍ instrukce pro Application Password',
      description: 'Jak zapnout v Hostingeru a vytvořit',
      status: 'pending',
      userAction: true,
      instructions: [
        '**DŮLEŽITÉ: Application Password musí být nejdřív ZAPNUTO v Hostingeru!**',
        '',
        '### Krok 1: Zapnout Application Passwords v Hostingeru',
        '1. Přihlas se do WordPress Adminu',
        '2. V levém menu klikni na **"Hostinger"** záložku (nebo "hPanel")',
        '3. Klikni na **"Tools"**',
        '4. Scrolluj dolů na sekci **"Application Passwords"**',
        '5. Pokud je **Toggle OFF** → **KLIKNI NA TOGGLE a zapni ho (ON)**',
        '6. Ulož změny',
        '',
        '### Krok 2: Vytvoř Application Password',
        '1. Přejdi na: **Users → Your Profile** (nebo klikni na své jméno v pravém horním rohu)',
        '2. Scrolluj dolů na sekci **"Application Passwords"**',
        '3. Do pole **"New Application Password Name"** zadej: **"Fellaship Web Builder Tool"**',
        '4. Klikni **"Add New Application Password"**',
        '5. **DŮLEŽITÉ:** Zkopíruj heslo hned - zobrazí se jen jednou!',
        '6. Heslo bude ve formátu: `xxxx xxxx xxxx xxxx xxxx xxxx` (s mezerami)',
        '',
        '**Pokud nevidíš sekci Application Passwords:**',
        '- Zkontroluj, zda je zapnuté v Hostinger → Tools → Application Passwords',
        '- Pokud stále nevidíš, použij standardní WordPress heslo (méně bezpečné)'
      ]
    },
    {
      id: 'access-3',
      title: 'Požádat uživatele o SSH přístupy (pokud má)',
      description: 'Host, username, password, port',
      status: 'pending',
      userAction: true,
      instructions: [
        'Řekni uživateli: "Pokud máš SSH přístup, potřebuji:"',
        '- SSH Host (IP adresa)',
        '- SSH Username',
        '- SSH Password',
        '- SSH Port (obvykle 22 nebo jiný, např. 65002)'
      ]
    },
    {
      id: 'access-4',
      title: 'Požádat uživatele o název projektu a GitHub repo',
      description: 'Název firmy/projektu a GitHub repo URL',
      status: 'pending',
      userAction: true,
      instructions: [
        'Řekni uživateli: "Potřebuji:"',
        '- Název projektu/firmy (např. "Acme Corp")',
        '- GitHub repo URL (založ nový repo na GitHubu pokud nemáš)',
        '',
        '**Jak založit GitHub repo:**',
        '1. Jdi na GitHub.com',
        '2. Klikni "New repository"',
        '3. Název: {nazev-projektu}-website',
        '4. Vytvoř repo (může být private)',
        '5. Dej mi odkaz na repo'
      ]
    },
    {
      id: 'access-5',
      title: 'Uložit všechny přístupy pomocí save-access.js',
      description: 'Vytvořit přístupy.md a config.json',
      status: 'pending',
      userAction: false,
      code: `
import { saveAccess } from './save-access.js';

await saveAccess({
  projectName: '...', // od uživatele
  wordpressUrl: '...', // od uživatele
  wordpressUsername: '...', // od uživatele
  wordpressApplicationPassword: '...', // od uživatele
  sshHost: '...', // od uživatele (pokud má)
  sshUsername: '...', // od uživatele (pokud má)
  sshPassword: '...', // od uživatele (pokud má)
  sshPort: 65002, // od uživatele (pokud má)
  githubRepo: '...', // od uživatele
  localPath: 'C:\\\\Users\\\\...\\\\Documents\\\\{nazev-projektu}' // dynamicky
});
      `
    }
  ],

  // FÁZE 2: Setup WordPress - Fáze 1 (Plugin)
  SETUP_PLUGIN: [
    {
      id: 'setup-1',
      title: 'Test připojení k WordPress API',
      description: 'Ověřit, že Application Password funguje',
      status: 'pending',
      userAction: false
    },
    {
      id: 'setup-2',
      title: 'Nahrát plugin přes SSH',
      description: 'Nahrát bricks-api-endpoint.php na server',
      status: 'pending',
      userAction: false,
      code: 'node update-plugin-ssh.js'
    },
    {
      id: 'setup-3',
      title: 'POZASTAVIT a požádat uživatele o aktivaci pluginu',
      description: 'Uživatel musí aktivovat plugin ručně v WordPress adminu',
      status: 'pending',
      userAction: true,
      instructions: [
        '**⏸️ POZASTAVENO - Čekám na aktivaci pluginu**',
        '',
        '**CO DĚLAT:**',
        '1. Jdi do WordPress Admin: {wordpress_url}/wp-admin',
        '2. Přejdi na: **Plugins → Installed Plugins**',
        '3. Najdi **"Bricks API Endpoint"**',
        '4. Klikni **"Activate"**',
        '5. **DŮLEŽITÉ:** Aktualizuj permalinks: **Settings → Permalinks → Save Changes** (i bez změn)',
        '',
        '**Po dokončení napiš:** "Plugin je aktivní" nebo "Aktivoval jsem plugin"',
        '',
        '⏸️ Čekám na potvrzení...'
      ]
    }
  ],

  // FÁZE 3: Setup WordPress - Fáze 2 (Bricks)
  SETUP_BRICKS: [
    {
      id: 'bricks-1',
      title: 'Zkontrolovat, že plugin je aktivní',
      description: 'Ověřit přes API, že plugin běží',
      status: 'pending',
      userAction: false
    },
    {
      id: 'bricks-2',
      title: 'Nainstalovat Bricks Builder téma',
      description: 'Instalace Bricks tématu ze ZIP souboru',
      status: 'pending',
      userAction: false,
      code: 'node sync.js install-bricks'
    },
    {
      id: 'bricks-3',
      title: 'Aktivovat Bricks licenci',
      description: 'Aktivace licence pomocí klíče z config.json',
      status: 'pending',
      userAction: false
    },
    {
      id: 'bricks-4',
      title: 'Aktualizovat Bricks téma',
      description: 'Zkontrolovat a aktualizovat na nejnovější verzi',
      status: 'pending',
      userAction: false,
      code: 'node sync.js update-bricks'
    },
    {
      id: 'bricks-5',
      title: 'Nastavit Bricks Settings',
      description: 'Code Execution a Post Types (Pages)',
      status: 'pending',
      userAction: false
    },
    {
      id: 'bricks-6',
      title: 'Vytvořit Homepage stránku',
      description: 'Vytvořit prázdnou Homepage stránku',
      status: 'pending',
      userAction: false
    },
    {
      id: 'bricks-7',
      title: 'Nastavit WordPress Reading',
      description: 'Nastavit statickou stránku na Homepage',
      status: 'pending',
      userAction: false
    },
    {
      id: 'bricks-8',
      title: 'Vytvořit prázdné Templates',
      description: 'Vytvořit Header a Footer templates',
      status: 'pending',
      userAction: false
    }
  ],

  // FÁZE 4: Hotovo
  DONE: [
    {
      id: 'done-1',
      title: 'Zobrazit shrnutí a další kroky',
      description: 'Informovat uživatele, co může dělat dál',
      status: 'pending',
      userAction: false
    }
  ]
};

/**
 * Získat TODO list pro aktuální fázi
 */
export function getTodosForPhase(phase) {
  return WORKFLOW_TODOS[phase] || [];
}

/**
 * Zobrazit TODO list jako strukturovaný výstup
 */
export function displayTodos(todos, phaseName) {
  console.log(`\n📋 TODO: ${phaseName}\n`);
  console.log('═'.repeat(60));
  
  todos.forEach((todo, index) => {
    const statusIcon = todo.status === 'completed' ? '✅' : 
                      todo.status === 'in_progress' ? '🔄' : 
                      todo.status === 'pending' ? '⏳' : '❌';
    
    console.log(`\n${statusIcon} [${todo.id}] ${todo.title}`);
    console.log(`   ${todo.description}`);
    
    if (todo.userAction) {
      console.log(`   👤 Vyžaduje akci uživatele`);
    }
    
    if (todo.instructions && todo.instructions.length > 0) {
      console.log(`\n   📝 Instrukce:`);
      todo.instructions.forEach(instruction => {
        console.log(`      ${instruction}`);
      });
    }
    
    if (todo.code) {
      console.log(`\n   💻 Kód:`);
      console.log(`      ${todo.code.trim()}`);
    }
  });
  
  console.log('\n' + '═'.repeat(60) + '\n');
}

/**
 * Aktualizovat status TODO
 */
export function updateTodoStatus(todos, todoId, newStatus) {
  const todo = todos.find(t => t.id === todoId);
  if (todo) {
    todo.status = newStatus;
    return true;
  }
  return false;
}

/**
 * Získat další TODO k vykonání
 */
export function getNextTodo(todos) {
  return todos.find(t => t.status === 'pending');
}

