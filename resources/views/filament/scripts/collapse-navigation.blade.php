<script>
// Multipli tentativi per assicurarci che funzioni
function collapseAllGroups() {
    const buttons = document.querySelectorAll('.fi-sidebar-group button[aria-expanded="true"]');
    if (buttons.length > 0) {
        console.log('Chiudendo ' + buttons.length + ' gruppi...');
        buttons.forEach(btn => btn.click());
        return true;
    }
    return false;
}

// Prova immediatamente
setTimeout(collapseAllGroups, 50);
// Riprova dopo 100ms
setTimeout(collapseAllGroups, 100);
// Riprova dopo 300ms
setTimeout(collapseAllGroups, 300);
// Riprova dopo 500ms
setTimeout(collapseAllGroups, 500);

// E quando la pagina è completamente caricata
window.addEventListener('load', function() {
    setTimeout(collapseAllGroups, 100);
});
</script>