<?php 
$activeHome = (empty($_GET['p'])) ? 'active ' : null;
$activeGoddess = (str_contains($_GET['p'], 'goddess')) ? 'active ' : null;
$activeChinese = (str_contains($_GET['p'], 'chinese')) ? 'active ' : null;
$activeFrench = (str_contains($_GET['p'], 'french')) ? 'active ' : null;
$activeEnglish = (str_contains($_GET['p'], 'english')) ? 'active ' : null;
?>