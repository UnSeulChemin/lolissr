import { prefetchSeriesPage, prefetchSeriesImage } from './prefetch-series.js';
/*
|------------------------------------------------------------------
| État global de la navigation clavier
|------------------------------------------------------------------
*/
let seriesKeyboardNavigationInitialized = false;
let seriesActiveCardIndex = -1;
/*
|------------------------------------------------------------------
| Sélecteurs et utilitaires
|------------------------------------------------------------------
*/
function getSeriesGrid() {
return document.querySelector('.collection-grid');
}
function getSeriesCardLinks() {
const grid = getSeriesGrid();
return grid ? Array.from(grid.querySelectorAll('.collection-card-link')) : [];
}
function getSeriesGridColumnCount() {
const grid = getSeriesGrid();
if (!grid) return 1;
const styles = window.getComputedStyle(grid);
const columns = styles.gridTemplateColumns.split(' ').filter(Boolean);
return columns.length || 1;
}
/**
* Vérifie si on tape dans un champ (input/textarea/select/contenteditable)
*/
function isTypingContext(target) {
if (!target) return false;
return Boolean(target.closest('input, textarea, select, [contenteditable="true"]'));
}
/*
|------------------------------------------------------------------
| Gestion de l'état actif des cartes
|------------------------------------------------------------------
*/
function clearSeriesActiveState() {
seriesActiveCardIndex = -1;
getSeriesCardLinks().forEach(card => {
card.classList.remove('is-active');
card.blur();
});
}
function syncSeriesActiveState() {
const cards = getSeriesCardLinks();
if (cards.length === 0) {
seriesActiveCardIndex = -1;
return;
}
// Clamp l'index pour rester dans la grille
if (seriesActiveCardIndex >= cards.length) seriesActiveCardIndex = cards.length - 1;
if (seriesActiveCardIndex < 0) seriesActiveCardIndex = 0;
cards.forEach((card, index) => card.classList.toggle('is-active', index === seriesActiveCardIndex));
const activeCard = cards[seriesActiveCardIndex];
if (!activeCard) return;
// Scroll vers la carte active
activeCard.focus({ preventScroll: true });
const rect = activeCard.getBoundingClientRect();
const viewportPadding = 120;
if (rect.bottom > window.innerHeight - viewportPadding || rect.top < viewportPadding) {
activeCard.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
}
// Prefetch page et image
prefetchSeriesPage(activeCard.href);
const image = activeCard.querySelector('.card-image-portrait');
if (image) prefetchSeriesImage(image.src);
}
/*
|------------------------------------------------------------------
| Navigation de l'index actif
|------------------------------------------------------------------
*/
function openActiveSeriesCard() {
const cards = getSeriesCardLinks();
if (seriesActiveCardIndex < 0 || !cards[seriesActiveCardIndex]) return;
window.location.href = cards[seriesActiveCardIndex].href;
}
function moveSeriesActiveIndexToNext(cards) {
seriesActiveCardIndex = seriesActiveCardIndex < cards.length - 1 ? seriesActiveCardIndex + 1 : 0;
}
function moveSeriesActiveIndexToPrevious(cards) {
seriesActiveCardIndex = seriesActiveCardIndex > 0 ? seriesActiveCardIndex - 1 : cards.length - 1;
}
function moveSeriesActiveIndexDown(cards) {
const columnCount = getSeriesGridColumnCount();
if (seriesActiveCardIndex === -1) { seriesActiveCardIndex = 0; return; }
seriesActiveCardIndex = Math.min(seriesActiveCardIndex + columnCount, cards.length - 1);
}
function moveSeriesActiveIndexUp() {
const columnCount = getSeriesGridColumnCount();
if (seriesActiveCardIndex === -1) { seriesActiveCardIndex = 0; return; }
seriesActiveCardIndex = Math.max(seriesActiveCardIndex - columnCount, 0);
}
function handleSeriesBackNavigation() {
const backButton = document.querySelector('.collection-back-button');
if (backButton) {
window.location.href = backButton.href;
} else {
window.history.back();
}
}
/*
|------------------------------------------------------------------
| Initialisation navigation clavier
|------------------------------------------------------------------
*/
export function initSeriesKeyboardNavigation() {
if (seriesKeyboardNavigationInitialized) return;
seriesKeyboardNavigationInitialized = true;
// Click sur une carte -> synchronise l'état actif
document.addEventListener('click', (event) => {
const clickedCard = event.target.closest('.collection-card-link');
const grid = getSeriesGrid();
if (!clickedCard || !grid || !grid.contains(clickedCard)) return;
const cards = getSeriesCardLinks();
const clickedCardIndex = cards.indexOf(clickedCard);
if (clickedCardIndex === -1) return;
seriesActiveCardIndex = clickedCardIndex;
syncSeriesActiveState();
});
// Navigation clavier
document.addEventListener('keydown', (event) => {
if (isTypingContext(event.target)) return;
const grid = getSeriesGrid();
const cards = getSeriesCardLinks();
if (!grid || cards.length === 0) return;
switch (event.key) {
case 'Tab':
event.preventDefault();
if (seriesActiveCardIndex === -1) seriesActiveCardIndex = 0;
else if (event.shiftKey) moveSeriesActiveIndexToPrevious(cards);
else moveSeriesActiveIndexToNext(cards);
syncSeriesActiveState();
break;
case 'ArrowRight':
event.preventDefault();
moveSeriesActiveIndexToNext(cards);
syncSeriesActiveState();
break;
case 'ArrowLeft':
event.preventDefault();
moveSeriesActiveIndexToPrevious(cards);
syncSeriesActiveState();
break;
case 'ArrowDown':
event.preventDefault();
moveSeriesActiveIndexDown(cards);
syncSeriesActiveState();
break;
case 'ArrowUp':
event.preventDefault();
moveSeriesActiveIndexUp(cards);
syncSeriesActiveState();
break;
case 'Enter':
event.preventDefault();
openActiveSeriesCard();
break;
case 'Escape':
event.preventDefault();
clearSeriesActiveState();
break;
case 'Backspace':
event.preventDefault();
handleSeriesBackNavigation();
break;
default:
break;
}
});
}