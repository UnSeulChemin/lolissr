<?php 
$activeHome = (empty($_GET['p'])) ? 'active ' : null;
$activeGoddess = (str_contains($_GET['p'], 'goddess')) ? 'active ' : null;
$activeFigurine = (str_contains($_GET['p'], 'figurine')) ? 'active ' : null;
$activeNendoroid = (str_contains($_GET['p'], 'nendoroid')) ? 'active ' : null;
$activeAnime = (str_contains($_GET['p'], 'anime')) ? 'active ' : null;
$activeManga = (str_contains($_GET['p'], 'manga')) ? 'active ' : null;
$activeChinese = (str_contains($_GET['p'], 'chinese')) ? 'active ' : null;
$activeFrench = (str_contains($_GET['p'], 'french')) ? 'active ' : null;
$activeEnglish = (str_contains($_GET['p'], 'english')) ? 'active ' : null;
?>