/**
* Vérifie si l'utilisateur tape dans un champ éditable.
*/
function isTypingTarget(target) {
return target?.isContentEditable
|| ['INPUT', 'TEXTAREA', 'SELECT'].includes(target?.tagName);
}
/**
* Active la navigation "Backspace" pour revenir à la page précédente.
* Ignore les champs de saisie pour éviter de supprimer du texte.
*/
export function initBackNavigation() {
document.addEventListener('keydown', (event) => {
if (event.key !== 'Backspace') return;
if (isTypingTarget(event.target)) return;
event.preventDefault();
// Utilise history.back() si possible
if (window.history.length > 1) {
window.history.back();
} else {
// Fallback : revenir à /lolissr/manga si aucune page précédente
window.location.href = '/lolissr/manga';
}
});
}