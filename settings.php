
<?php include("script/verification.php"); ?>

<?php include("db/db.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=yes, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://kit.fontawesome.com/118716b668.js" crossorigin="anonymous"></script>
    <title>LoliSSR Settings</title>
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/style.css">    
    <link rel="stylesheet" href="css/menu/settings.css">
    <link rel="stylesheet" href="<?php echo $themeStyleSheet; ?>" id="dark_theme">
</head>

<body class="<?php echo $themeClass; ?>">

<div id="container">

  <?php include("include/header.php"); ?>

  <div id="settings_background">  

    <div id="settings_section_background">

      <section id="settings_section"> 

        <div id="settings_section_top">

          <div class="h_div">
            <h2 class="h_selector">Settings your profile</h2>

            <div class="element">

                <img src="images/info.png">

                <div class="infobulle">
                  <p>Name between 5 and 12 characters.</p>
                  <p>Can't use already exist name.</p>
                  <p>Password at least 5 characters.</p>
                  <p>Password should have 1 lowercase, 1 uppercase, 1 numeric, 1 special character.</p>
                </div>

            </div>

          </div>

          <div id="div_settings_s_name">
              <p><?php echo $_SESSION['name']; ?></p>
          </div>

        </div>

        <div id="settings_things">

          <div id="settings_things_avatar">

            <form method="post" action="" enctype="multipart/form-data">

              <?php 

              if (isset($_SESSION['id']) AND !empty($_SESSION['id']))
              {
                $id = $_SESSION['id'];

                $req = $dbh->prepare('SELECT * FROM members WHERE id = :id');
                $req->bindValue('id', $id);
                $req->execute();
                $userinfo = $req->fetch();
              }

              ?>

              <div id="settings_div_img">
                  <img id="settings_img" src="images/avatar/<?php echo $userinfo['avatar'];?>">
              </div>

              <?php 

              //AVATAR

              if (isset($_POST['valider']))
              {
                if (isset($_FILES['avatar']) AND !empty($_FILES['avatar']['name']))
                { 
                  $tailleMax = 1000000;

                  if ($_FILES['avatar']['size'] < $tailleMax)
                  {
                    $extensionsUpload = explode('/', $_FILES['avatar']['type']);

                    if($extensionsUpload[0] == 'image')
                    {
                      $type = explode('/', $_FILES['avatar']['type']);

                      if ($type[1] == 'png' || 'jpg' || 'jpeg')
                      {
                        $chemin = "images/avatar/" . $_SESSION['id'] . "." . $type[1];
                        $resultat = move_uploaded_file($_FILES['avatar']['tmp_name'], $chemin);

                        $avatar = $_SESSION['id'] . "." . $type[1];
                        $id_session = $_SESSION['id'];

                        if ($resultat)
                        {
                          $updateAvatar = $dbh->prepare("UPDATE members SET avatar = :avatar WHERE id = :id");
                          $updateAvatar->bindValue('avatar', $avatar);
                          $updateAvatar->bindValue('id', $id_session);
                          $updateAvatar->execute();

                          $updateChatAvatar = $dbh->prepare("UPDATE chat SET avatar = :avatar WHERE name = :name");
                          $updateChatAvatar->bindValue('avatar', $avatar);
                          $updateChatAvatar->bindValue('name', $_SESSION['name']);
                          $updateChatAvatar->execute();

                          header('Location: settings');
                        }
                        
                      } // IF TYPE

                      else
                      {
                        $messageError = "Wrong format. png, jpg, jpeg.";  
                      }

                    } // IF IMAGE

                    else
                    {
                      $messageError = "Invalid type.";                      
                    }

                  } // IF SIZE

                  else
                  {
                    $messageError = "File too large.";
                  }

                } // IF ISSET

                else
                {
                  $messageError = "Error, please try again.";
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

              // AVATAR END

              ?>

              <div id="avatar_send_div">
                <input id="upload_header" type="file" name="avatar">
                <label for="upload_header" class="btn">Upload</label>

                <input id="submit_header" type="submit" name="valider" value="Validate">
              </div>

            </form>

          </div>

          <div id="settings_things_name">

            <form method="POST" action="">

              <div id="name_change_div">
                <input id="submit_text" type="text" name="name" placeholder="New name">
                <input id="submit_name" type="submit" name="update" value="Validate">
              </div>

            </form>

            <?php

            // NAME

            if (isset($_POST['name']))
            {
              if (!empty($_POST['name']))
              {
                if (strlen($_POST['name']) >= 5 && strlen($_POST['name']) <= 12)
                {
                  $postname = trim($_POST['name']);

                  $takeName = $dbh->prepare('SELECT * FROM members WHERE name = :name');
                  $takeName->bindValue('name', $postname);
                  $takeName->execute();

                  if ($takeName->rowCount() == 0)
                  {
                    $sessionid = $_SESSION['id'];

                    $takeeUsers = $dbh->prepare('SELECT * FROM members WHERE id = :id');
                    $takeeUsers->bindValue('id', $sessionid);
                    $takeeUsers->execute();

                    if ($takeeUsers->rowCount() > 0)
                    {
                      $oldData  = $takeeUsers->fetch();
                      $nameData = $oldData['name'];

                      $upUsers = $dbh->prepare('UPDATE members SET name = :postname WHERE id = :id');
                      $upUsers->bindValue('postname', $postname);
                      $upUsers->bindValue('id', $sessionid);
                      $upUsers->execute();

                      $newNameMessage = $dbh->prepare('UPDATE chat SET name = :postname WHERE name = :name');
                      $newNameMessage->bindValue('postname', $postname);
                      $newNameMessage->bindValue('name', $nameData);
                      $newNameMessage->execute();                     

                      $_SESSION['name'] = $postname;

                       $messageValid = "You name has be updated.";                      
                    }

                  } // IF NAME

                  else
                  {
                    $messageError = "Name already taken.";
                  }

                } // IF STRLEN
                  
                else
                {
                  $messageError = "Username too short / long.";
                }
                         
              }  // IF !EMPTY

              else
              {
                $messageError = "Please put a new name.";                
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

          <div id="settings_things_password"> 

            <form method="POST" action="">

              <div id="password_change_div">
                  <input id="submit_text_password" type="password" name="password" placeholder="New Password">
                  <img src="images/hide.png" id="eye_settings" onClick="changeW()">
                <input id="submit_name_password" type="submit" name="update_pass" value="Validate">
              </div>

            </form>
            
              <?php

              if (isset($_POST['password']))
              {
                if (!empty($_POST['password']))
                {
                  if (strlen($_POST['password']) >= 5)
                  {
                    $pattern = "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?([^\w\s]|[_])).{5,}$/";
                    // at least 5 characters, at least 1 numeric character, at least 1 lowercase letter, at least 1 uppercase letter, at least 1 special character
                    $text = $_POST['password'];

                    if (preg_match($pattern, $text))
                    {
                      $id = $_SESSION['id'];
                      $password = password_hash($_POST['password'], PASSWORD_DEFAULT, array($GLOBALS['salt1']));

                      $takeeeUsers = $dbh->prepare('SELECT * FROM members WHERE id = :id');
                      $takeeeUsers->bindValue('id', $id);
                      $takeeeUsers->execute();

                      if ($takeeeUsers->rowCount() > 0)
                      {
                        $upUsers = $dbh->prepare('UPDATE members SET password = :password WHERE id = :id');
                        $upUsers->bindValue('password', $password);
                        $upUsers->bindValue('id', $id);
                        $upUsers->execute();

                        $_SESSION['password'] = $password;            

                        $messageValid = "You password has be updated.";                         
                      }
   
                    } // IF PASSWORD (SECURITY)

                    else
                    {
                      $messageError = "Password not enough strong.";
                    }      

                  } // IF STRLEN

                  else
                  {
                    $messageError = "Password too short / long.";
                  }

                } // IF !EMPTY

                else
                {
                  $messageError = "Please put a new password.";    
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

          <div id="settings_things_delete"> 

            <?php $id = $_SESSION['id']; ?>

            <a onclick="deleteThisMember(<?php echo $id ?>)">Delete account</a>

          </div>
        
        </div>

      </section>

    </div>

  </div>

<?php include("include/displayError.php"); ?>

</div>

</body>
<script src="javascript/minjquery-3.6.0.js"></script>
<script src="javascript/script.js"></script>
</html>