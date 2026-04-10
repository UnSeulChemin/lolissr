<?php 

$currentPage = $_GET['p'] ?? '';

$activeHome = empty($currentPage) ? 'active ' : null;
$activeFigurine = str_contains($currentPage, 'figurine') ? 'active ' : null;
$activeNendoroid = str_contains($currentPage, 'nendoroid') ? 'active ' : null;
$activePlush = str_contains($currentPage, 'plush') ? 'active ' : null;
$activeManga = str_contains($currentPage, 'manga') ? 'active ' : null;
$activeChinese = str_contains($currentPage, 'chinese') ? 'active ' : null;

?>