
<?php

require_once("db/db.php");

session_start();


// SESSION VERIFICATION

if ($_SERVER['SERVER_NAME'] == 'localhost')
{
  $defaultLink = $_SERVER['PHP_SELF'];

  $old_link = array("/loli/register.php", "/loli/login.php");
  $actual_link = array("/loli/chat.php", "/loli/message.php", "/loli/admin.php",  "/loli/generate.php", "/loli/settings.php");
  $actual_link_admin = array("/loli/admin.php",  "/loli/generate.php");


  // SI LE MENBRE N'EST PAS CONNECTE ET N'A PAS DE COMPTE

  if (in_array($defaultLink, $actual_link) && !isset($_SESSION['password']))
  {
      header('Location: login');
  }

  // SI LE MENBRE N'EST PAS CONNECTE ET A UN COMPTE

  else if (in_array($defaultLink, $actual_link) && isset($_SESSION['password']) && isset($_SESSION['register']) && $_SESSION['register'] == "new")
  {
      header('Location: login');
  }

  // SI LE MENBRE EST CONNECTE ET VEUT ACCEDER AUX ANCIENNES PAGES

  else if (in_array($defaultLink, $old_link) && isset($_SESSION['password']) && isset($_SESSION['role']) && $_SESSION['role'] == "member")
  {
      header('Location: index?error=timeout');
  }

  // SI LE MENBRE EST CONNECTE ET VEUT ACCEDER AUX PAGES ADMINS

  else if (in_array($defaultLink, $actual_link_admin) && isset($_SESSION['password']) && isset($_SESSION['role']) && $_SESSION['role'] == "member")
  {
      header('Location: error/403?error=MemberToAdmin');
  }
}

else if ($_SERVER['HTTP_HOST'] == 'lolissr.com')
{
  $defaultLink = $_SERVER['PHP_SELF'];

  $old_link = array("/register.php", "/login.php");
  $actual_link = array("/chat.php", "/message.php",  "/admin.php",  "/generate.php", "/settings.php");
  $actual_link_admin = array("/admin.php",  "/generate.php");


  // SI LE MENBRE N'EST PAS CONNECTE ET N'A PAS DE COMPTE

  if (in_array($defaultLink, $actual_link) && !isset($_SESSION['password']))
  {
      header('Location: login');
  }

  // SI LE MENBRE N'EST PAS CONNECTE ET A UN COMPTE

  else if (in_array($defaultLink, $actual_link) && isset($_SESSION['password']) && isset($_SESSION['register']) && $_SESSION['register'] == "new")
  {
      header('Location: login');
  }

  // SI LE MENBRE EST CONNECTE ET VEUT ACCEDER AUX ANCIENNES PAGES

  else if (in_array($defaultLink, $old_link) && isset($_SESSION['password']) && isset($_SESSION['role']) && $_SESSION['role'] == "member")
  {
      header('Location: index?error=timeout');
  }

  // SI LE MENBRE EST CONNECTE ET VEUT ACCEDER AUX PAGES ADMINS

  else if (in_array($defaultLink, $actual_link_admin) && isset($_SESSION['password']) && isset($_SESSION['role']) && $_SESSION['role'] == "member")
  {
      header('Location: error/403?error=MemberToAdmin');
  }
}


// IP VERIFICATION

if (isset($_SESSION['name']) && !empty($_SESSION['name']))
{
  $ban = "Y";
  $sessionName = $_SESSION['name'];

  $takeUser = $dbh->prepare('SELECT * FROM members WHERE ban = :ban AND name = :name'); 
  $takeUser->bindValue('ban', $ban);
  $takeUser->bindValue('name', $sessionName);
  $takeUser->execute();

  if ($takeUser->rowCount() > 0)
  {
    http_response_code(403);
    header('Location: error/403?error=userBan');
  }

}


// DARK MODE

$themeClass = '';

if (!empty($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark')
{
  $themeClass = 'dark-theme';
}

$themeStyleSheet = 'css/style.css';

if (!empty($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark')
{
  $themeStyleSheet = 'css/dark_theme.css';
}

?>
