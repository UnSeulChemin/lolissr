<?php

// echo "<pre>"; print_r($_SERVER);echo "</pre>"; exit;

try
{
    if ($_SERVER['SERVER_NAME'] == 'localhost')
    {
        $dbh = new PDO('mysql:host=localhost;dbname=lolissr', 'UnSeulChemin', 'N0zenith1___', 
            array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
    }

    else
    {
        $dbh = new PDO('mysql:host=localhost;dbname=u676005694_lolissr', 'u676005694_UnSeulChemin', 'N0zenith1___',
            array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
    }

}

catch (PDOException $exception)
{
    var_dump($exception);
    echo "problem";
    exit;
}

?>
