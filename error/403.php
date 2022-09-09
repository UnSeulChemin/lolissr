<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>403</title>
    <link rel="stylesheet" href="403.css">	
</head>
<body>

<?php

if (isset($_GET['error']) && $_GET['error'] == "MemberToAdmin")
{
  echo "you can't look at this !";
}

else if (isset($_GET['error']) && $_GET['error'] == "userBan")
{
  echo "Your account has been banned.";
}

?>

<div class="conteiner">
  
  <div class="fish">
      <div class="eye"></div>
    <div class="body"></div>
    <div class="tail"></div>
    <div class="fin"></div>
    <div class="backfin"></div>
  </div>
  
</div>

</body>
</html>