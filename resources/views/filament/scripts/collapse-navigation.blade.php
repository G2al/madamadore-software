<script>
// Funzione per trovare e aprire il gruppo attivo
function openActiveGroup() {
    const sidebar = document.querySelector('.fi-sidebar');
    if (!sidebar) return null;
    
    const currentPath = window.location.pathname;
    const allLinks = sidebar.querySelectorAll('a');
    
    let activeLink = null;
    let maxMatchLength = 0;
    
    allLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href) {
            let linkPath;
            try {
                linkPath = new URL(href, window.location.origin).pathname;
            } catch {
                linkPath = href;
            }
            
            if (linkPath !== '/admin' && linkPath !== '/admin/' && currentPath === linkPath && linkPath.length > maxMatchLength) {
                activeLink = link;
                maxMatchLength = linkPath.length;
            }
        }
    });
    
    if (activeLink) {
        const parentGroup = activeLink.closest('.fi-sidebar-group');
        if (parentGroup) {
            console.log('✅ Gruppo attivo trovato');
            return parentGroup; // Ritorna il gruppo attivo
        }
    }
    
    return null;
}

// Funzione per chiudere tutti i gruppi TRANNE quello attivo
function collapseAllGroupsExceptActive(activeGroup) {
    const buttons = document.querySelectorAll('.fi-sidebar-group button[aria-expanded="true"]');
    
    let closedCount = 0;
    buttons.forEach(btn => {
        // Se c'è un gruppo attivo, non chiudere il suo bottone
        if (activeGroup && activeGroup.contains(btn)) {
            console.log('⏭️ Salto gruppo attivo');
            return;
        }
        btn.click();
        closedCount++;
    });
    
    if (closedCount > 0) {
        console.log('Chiudendo ' + closedCount + ' gruppi...');
    }
}

// Funzione per assicurarsi che il gruppo attivo sia aperto
function ensureActiveGroupIsOpen(activeGroup) {
    if (!activeGroup) return;
    
    const groupButton = activeGroup.querySelector('button[aria-expanded="false"]');
    if (groupButton) {
        console.log('✅ Aprendo gruppo attivo...');
        groupButton.click();
    } else {
        console.log('⚠️ Gruppo attivo già aperto');
    }
}

// Inizializza navigazione
function initNavigation() {
    console.log('🚀 Inizializzazione navigazione...');
    
    // 1. Trova il gruppo attivo
    const activeGroup = openActiveGroup();
    
    // 2. Chiudi tutti TRANNE il gruppo attivo
    collapseAllGroupsExceptActive(activeGroup);
    
    // 3. Assicurati che il gruppo attivo sia aperto
    setTimeout(() => ensureActiveGroupIsOpen(activeGroup), 100);
}

// Usa MutationObserver per aspettare il rendering completo
const observer = new MutationObserver((mutations, obs) => {
    const sidebar = document.querySelector('.fi-sidebar');
    if (sidebar) {
        console.log('✅ Sidebar rilevata, inizializzo...');
        obs.disconnect();
        setTimeout(initNavigation, 150);
    }
});

observer.observe(document.body, {
    childList: true,
    subtree: true
});

// Fallback
setTimeout(() => {
    observer.disconnect();
    initNavigation();
}, 1000);
</script>