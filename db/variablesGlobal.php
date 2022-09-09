
<?php

// SALT

$lower = range(97, 122);
$upper = range(65, 90);
$numeric = range(48, 57);
$base = array_map('chr', array_merge($lower, $upper, $numeric));
shuffle($base);
$salt1 = implode('', array_slice($base, 0, 25));


// ADMIN

if ($_SERVER['SERVER_NAME'] == 'localhost')
{
	$adminsSession = array("Chemin [DEV]" => 110, "azerty1" => 108);
}

else
{
	$adminsSession = array("Chemin [DEV]" => 110, "azerty1" => 108);
}

?>
