
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
    <title>LoliSSR Generate</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu/generate.css">
    <link rel="stylesheet" href="<?php echo $themeStyleSheet; ?>" id="dark_theme">  
</head>

<body class="<?php echo $themeClass; ?>">

<div id="container">

  <?php include("include/header.php"); ?>

  <div id="generate_background">

    <div id="generate_section_background">

      <div id="generate_section_top">      

          <div class="h_div">
            <h2 class="h_selector">Generate part</h2>
          </div>

          <div id="div_generate_section_name">
              <p><?php echo $_SESSION['name']; ?></p>
          </div>  

      </div>  

      <form id="generate_form" method="post" action="">

        <div id="generate_input_div">
          <input id="input_un" type="submit" name="generate" value="Generate">
          <input id="input_deux" type="submit" value="Delete">
        </div>

        <?php

        // SHOW USERS    

        if (isset($_POST['generate']))
        {
          $takeUser = $dbh->query('SELECT * FROM members');

          if ($takeUser->rowCount() > 0)
          {

            ?>

            <div id="generate_show">

            <?php

            while ($user = $takeUser->fetch())
            {
              ?>

              <div id="generate_show_all">

                <div id="generate_show_both">
                      
                  <div id="div_un">
                    <p><?php echo $user['name']; ?></p>
                  </div>

                  <div id="div_deux">
                    <p>ban user</p>
                    <a onclick="banUser(<?php echo $user['id']; ?>)"><img src="images/delete.png" title="ban user" alt ="ban user"></a>
                  </div>

                </div>

              </div>

              <?php

            }

            ?>

            </div>

            <?php
          }

        }

        ?>

      </form>

    </div>

  </div>

<?php include("include/displayError.php"); ?>

</div>

</body>
<script src="javascript/minjquery-3.6.0.js"></script>
<script src="javascript/script.js"></script>
</html>