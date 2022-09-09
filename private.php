
<?php include("script/verification.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=yes, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://kit.fontawesome.com/118716b668.js" crossorigin="anonymous"></script>
    <title>LoliSSR Private</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/style.css">    
    <link rel="stylesheet" href="css/menu/private.css">
    <link rel="stylesheet" href="<?php echo $themeStyleSheet; ?>" id="dark_theme">
</head>

<body class="<?php echo $themeClass; ?>">

<div id="container">

  <?php include("include/header.php"); ?>

  <div id="private_background">
 
    <div id="private_section_background">

      <section id="private_section">

        <div id="private_section_user">

          <?php 

          // USERINFO

          if (isset($_SESSION['id']) AND !empty($_SESSION['id']))
          {
            $getid = $_GET['id'];

            $req = $dbh->prepare('SELECT * FROM members WHERE id = :getid');
            $req->bindValue('getid', $getid);
            $req->execute();  
            $userinfo = $req->fetch();         
          }

          ?>

          <div>
            <img id="img_header" width="50" src="images/avatar/<?php echo $userinfo['avatar'];?>">
          </div>

          <?php echo "<p>" . $userinfo['name'] . "</p>"; ?>

        </div>

        <section id="private_log">

          <?php
            
          // AJOUT DES MESSAGES DANS LA BDD

          if (isset($_GET['id']) AND !empty($_GET['id']))
          {
            $getid = $_GET['id'];

            $takeUsers = $dbh->prepare('SELECT * FROM members WHERE id = :getid');
            $takeUsers->bindValue('getid', $getid);
            $takeUsers->execute();

            if ($takeUsers->rowCount() > 0)
            {
              if (isset($_POST['send']))
              {
                $message = $_POST['message'];

                $insertMessage = $dbh->prepare('INSERT INTO private(message, id_receipter, id_sender) VALUES (:message, :getid, :session)');
                $insertMessage->bindValue('message', $message);
                $insertMessage->bindValue('getid', $getid);
                $insertMessage->bindValue('session', $_SESSION['id']);
                $insertMessage->execute();

                // if ($insertMessage->rowCount() > 0 && $_SESSION['id'] != $getid)
                // {
                //     $selectUser = $dbh->prepare('SELECT * FROM members WHERE id = :id');
                //     $selectUser->bindValue('id', $_SESSION['id']);
                //     $selectUser->execute();

                //     if ($selectUser->rowCount() > 0)
                //     {
                //       $dataUser = $selectUser->fetch();
                //       $dataName = $dataUser['name'];

                //       if ($dataName)
                //       {
                //         $_SESSION['notification'] = $dataName;
                //       }
                //     }
                // } 
              }
            }

            else
            {
              echo "none user found";
            }

          }

          else
          {
              echo "none id found";
          }

          ?>

          <div id="show_msg">
          
            <?php 

            // AFFICHER LES MESSAGES

            $getid = $_GET['id'];

            $takeMessages = $dbh->query('SELECT * FROM private ORDER BY id DESC LIMIT 0, 10');

            while ($message = $takeMessages->fetch())
            {
              if ($message['id_receipter'] == $_SESSION['id'] && $message['id_receipter'] != $getid)
              {
                ?>

                <div id="show_msg_div_un">

                  <div class="show_msg_img_div">

                    <?php

                    if(isset($_GET['id']) AND !empty($_GET['id']))
                    {
                      $id = $_GET['id'];

                      $req = $dbh->prepare('SELECT * FROM members WHERE id = :id');
                      $req->bindValue('id', $id);
                      $req->execute();
                      $getUser = $req->fetch();
                    }

                    ?>

                    <img width="50" src="images/avatar/<?php echo $getUser['avatar'];?>">

                  </div>

                  <div class="show_msg_text">
                    <p id="show_msg_div_pun"><?php echo $getUser['name']; ?></p>
                    <p id="show_msg_div_pdeux"><?php echo $message['message']; ?></p>
                  </div>

                </div>

                <?php 
              }

              elseif ($message['id_receipter'] == $_GET['id'])
              {
                ?>

                <div id="show_msg_div_deux">

                  <div class="show_msg_img_div">

                    <?php

                    if(isset($_SESSION['id']) AND !empty($_SESSION['id']))
                    {
                      $sessionid = $_SESSION['id'];

                      $req = $dbh->prepare('SELECT * FROM members WHERE id = :id');
                      $req->bindValue('id', $sessionid);
                      $req->execute();
                      $userinfo = $req->fetch();
                    }

                    ?>

                    <img width="50" src="images/avatar/<?php echo $userinfo['avatar'];?>"> 

                  </div>

                  <div class="show_msg_text">
                    <p id="show_msg_div_pun"><?php echo $_SESSION['name']; ?></p>
                    <p id="show_msg_div_pdeux"><?php echo $message['message']; ?></p>
                  </div>

                </div>

                <?php
              }

            }

            ?>

          </div>

        </section>

        <form id="private_form" method="POST" action="">

          <textarea name="message" placeholder="write a message"></textarea>
          <input type="submit" name="send" value="Send"></input>

        </form>

        <script>

          setInterval('load_messages()', 1500);
          function load_messages()
          {
            $('#private_log').load('privateLoop?id=<?php echo $getid; ?>');
          }

        </script>

      </section>

    </div>

  </div>

<?php include("include/displayError.php"); ?>

</div>

</body>
<script src="javascript/minjquery-3.6.0.js"></script>
<script src="javascript/script.js"></script>
</html>