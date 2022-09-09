
<?php include("script/verification.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=yes, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://kit.fontawesome.com/118716b668.js" crossorigin="anonymous"></script>
    <title>LoliSSR Message</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/style.css">    
    <link rel="stylesheet" href="css/menu/message.css">
    <link rel="stylesheet" href="<?php echo $themeStyleSheet; ?>" id="dark_theme">
</head>

<body class="<?php echo $themeClass; ?>">

<div id="container">

  <?php include("include/header.php"); ?>

  <div id="message_background">

    <div id="message_section_background">

      <div id="message_section_un">
        
        <div class="h_div">
          <h2 class="h_selector">ALL NEWS ACCOUNT</h2>
        </div>

        <?php

        $allUsers = $dbh->query('SELECT * FROM members ORDER BY id DESC LIMIT 0, 4');

        if ($allUsers->rowCount() > 0)
        {
          while ($user = $allUsers->fetch())
          {
            ?>

            <div id="s_un_main">

              <div class="s_un_main_pun">
                <img src="images/avatar/<?php echo $user['avatar'];?>">                    
                <p><?php echo $user['name']; ?></p>
              </div>

              <div class="s_un_main_pdeux">
                <a href="private?id=<?php echo $user['id']; ?>"><img src="images/message.png"></a>

                <?php

                if (isset($_SESSION['id']) && !empty($_SESSION['id']) && in_array($sessionid, $GLOBALS['adminsSession']))
                {
                  ?>

                  <a id="s_un_main_admin_delete" onclick="deleteMemberFromAdmin(<?php echo $user['id']; ?>)"><img id="delete" src="images/delete.png" title="delete user" alt ="delete user"></a>

                  <?php
                }

                ?>

              </div>

            </div>

            <?php
          }

        }

        ?>
        
      </div>

      <div id="message_section_deux">
        <div class="h_div">
          <h2 class="h_selector">ALL STAFF ACCOUNT</h2>
        </div>

        <?php

        // STAFF

        $ids = $GLOBALS['adminsSession'];
        $inQuery = implode(',', $ids);

        $allStaff = $dbh->prepare("SELECT * FROM members WHERE id IN ($inQuery) ORDER BY id DESC LIMIT 0, 2");
        $allStaff->execute();

        while ($staff = $allStaff->fetch())
        {

          ?>
            
          <div id="s_deux_main">

            <div class="s_un_main_pun">
              <img src="images/avatar/<?php echo $staff['avatar'];?>">
              <p><?php echo $staff['name']; ?></p>
            </div>

            <div class="s_un_main_pdeux">
              <a id="private" href="private?id=<?php echo $staff['id']; ?>"><img src="images/message.png" width="50"></a>
            </div>

          </div>

          <?php

        }

        ?>

      </div>

    </div>

  </div>

<?php include("include/displayError.php"); ?>

</div>

</body>
<script src="javascript/minjquery-3.6.0.js"></script>
<script src="javascript/script.js"></script>
</html>