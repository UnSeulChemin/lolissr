
<?php require_once("../db/db.php"); ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=yes, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://kit.fontawesome.com/118716b668.js" crossorigin="anonymous"></script>
    <title>LoliSSR Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans">
    <link rel="shortcut icon" type="image/png" href="../images/favicon.png">    
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/menu/forgotpassword.css"> 
</head>
<body>

<div id="container">

    <header>

        <div id="header_first_part">
            <h1><a href="../index">LoliSSR</a></h1>
        </div>  

        <div id="header_last_part">

            <div>
            
                <p id="password">Password</p>

            </div>

        </div>

    </header>

    <div id="password_background">

        <div id="password_section_background">

            <div class="password_section_top">

                <div class="h_div">
                  <h2 class="h_selector">Password</h2>

                  <div class="element">

                    <img src="../images/info.png">

                    <div class="infobulle">
                      <p>Be sure to use your name.</p>
                      <p>Set your new password.</p>
                      <p>Set your new password again.</p>
                    </div>

                  </div>

                </div>

            </div>

            <?php

            // SEND EMAIL

        	if (isset($_GET['emailExist']) && $_GET['emailExist'] == "y" && isset($_GET['email']))
        	{
        		$email = $_GET['email'];

                $takeEmail = $dbh->prepare('SELECT * FROM members WHERE email = :email');
                $takeEmail->bindValue('email', $email);
                $takeEmail->execute();

                if ($takeEmail->rowCount() > 0)
                {
                	$dataEmail = $takeEmail->fetch();

                	$name = sha1($dataEmail['name']);

                    $nameUpdate = $dbh->prepare("UPDATE members SET sha1 = :sha_one WHERE email = :email");
                    $nameUpdate->bindvalue('sha_one', $name);
                    $nameUpdate->bindValue('email', $email);
                    $nameUpdate->execute();

                    $subject = "Mot de passe oublié";

                    $message = "Hi, You recently requested to reset your password for your Lolissr account.\n";
                    $message .= "Click there to set your new password : https://lolissr.com/script/forgotPassword?hisPassword=y&name=" . $name; 

                    $headers = "From: oneechaneuw4977@gmail.com";

                    if (mail($email, $subject, $message, $headers))
                    {
                       echo "Email send.";
                    }

        	        else
        	        {
        	           echo "Email error.";
        	        }
                }
        	}

            if (isset($_GET['hisPassword']) && $_GET['hisPassword'] == "y" && isset($_GET['name']))
            {
                ?>

                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">

                    <label for="name">Name</label>
                    <input type="text" name="name" placeholder="put your name" required>

                    <label for="new_password">New password</label>
                    <input type="text" name="new_password" placeholder="put your new password" required>

                    <label for="new_password_comfirm">New password again</label>
                    <input type="text" name="new_password_comfirm" placeholder="put your new password again" required>

                    <input type="hidden" name="sha_one" value="<?php echo $_GET['name']; ?>">

                    <input type="submit" name="comfirm" value="Comfirm" id="comfirm">

                </form>

                <?php
            }

            // FORM MODIFICATE

        	if (isset($_POST['comfirm']) && !empty($_POST['comfirm']))
        	{
        		$name = $_POST['name'];

                $takeUser = $dbh->prepare('SELECT sha1 FROM members WHERE name = :name'); 
                $takeUser->bindValue('name', $name);
                $takeUser->execute();

                if ($takeUser->rowCount() > 0)
                {
                 	$name_shaone = sha1($_POST['name']);
                    $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT, array($GLOBALS['salt1']));

                 	if ($name_shaone == $_POST['sha_one'])
                 	{
                 		if ($_POST['new_password'] == $_POST['new_password_comfirm'])
                 		{
                       		$updatePassword = $dbh->prepare("UPDATE members SET password = :password, sha1 = :sha_one WHERE name = :name");
                       		$updatePassword->bindValue('password', $hashedPassword);
                       		$updatePassword->bindValue('sha_one', $name_shaone);
                       		$updatePassword->bindValue('name', $name);
                       		$updatePassword->execute();

                       		$messageValid = "New password set succesfully";
                 		}
                 	}

                 	else
                 	{
                 		$messageError = "Who are you?";
                 	}
                }

                else
                {
                    $email = $_GET['email'];

                    $takeEmail = $dbh->prepare('SELECT * FROM members WHERE email = :email');
                    $takeEmail->bindValue('email', $email);
                    $takeEmail->execute();

                    $dataEmail = $takeEmail->fetch();
                    $name = sha1($dataEmail['name']);

                	$messageError = "This user doesn't exist. Please try again. <a id='link' href='https://lolissr.com/script/forgotPassword?hisPassword=y&name=" . $name . "'>Back</a>";
                }

                // AFFICHAGE DES MESSAGES

                if (!empty($messageError))
                {
                  echo "<p class='review_wrong'>" . $messageError . "</p>";
                }    

                else if (!empty($messageValid))
                {
                    echo "<p class='review_right'>" . $messageValid . "</p>";
                }                            

        	}

            ?>

    </div>

  </div>    

</div>

</body>
</html>