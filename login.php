
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
    <title>LoliSSR Login</title>
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">    
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu/login.css">    
    <link rel="stylesheet" href="<?php echo $themeStyleSheet; ?>" id="dark_theme">
</head>

<body class="<?php echo $themeClass; ?>">

<div id="container">

  <?php include("include/header.php"); ?>

  <div id="login_background">

    <div id="login_section_background">

      <div id="upload_section_top">

        <div class="h_div">
            <h2 class="h_selector">Log in</h2>
        </div>

      </div>

      <form id="login_section" method="POST" action="">

        <p>log in here</p>

        <div id="login_form_username_div">
          <input type="text" name="name" placeholder="Username">          
        </div>

        <div id="login_form_password_div">
          <input id="login_password" type="password" name="password" placeholder="Password">
          <img src="images/hide.png" id="eye_login" onClick="changeZ()">          
        </div>

        <div id="login_section_footer">

          <?php

          // LOGIN

          if (isset($_POST['login']))
          {
            if (!empty($_POST['name']) AND !empty($_POST['password']))
            {
              $name = trim($_POST['name']);
              $password = trim($_POST['password']);

              $takeUsers = $dbh->prepare('SELECT * FROM members WHERE name = :name'); 
              $takeUsers->bindValue('name', $name);
              $takeUsers->execute();
              $req = $takeUsers->fetch(PDO::FETCH_ASSOC);

              if ($req)
              {
                $passwordHash = $req['password'];

                if (password_verify($password, $passwordHash))
                {
                  $_SESSION['name'] = $name;
                  $_SESSION['password'] = $password;
                  $_SESSION['id'] = $req['id'];
                  $_SESSION['role'] = $req['role'];

                  unset($_SESSION['register']);       

                  header('Location: index');
                }

                else
                {
                  $messageError = "Username or password invalid.";
                }

              }

              else
              {
                $messageError = "Username or password invalid.";
              }

            } // IF !EMPTY

            else
            {
              $messageError = "Please complete all fields.";
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

          // LOGIN END

          ?>

          <input id="login_submit" type="submit" name="login" value="Log in">
          <input id="forgot_password" type="button" value="New Password">

          <div id="popup">

            <div id="popup-content">

              <h2>Forgot password</h2>

              <form method="post">

                <p>Please fill the form to comfirm your identity.</p>

                <p class="show_error"></p>
                <p class="comfirm_email"></p>

                <div id="footer_div">

                  <div id="footer_flex">
          
                    <label id="email_label" for="email"><b>Email :</b></label>
                    <input id="email" type="email" placeholder="Enter Email" name="email">

                  </div>

                  <button id="return_email" type="submit">New password</button>

                </div>

              </form>

              <a id="popup-close">x</a>

            </div>

          </div>

        </div>

      </form>

    </div>

  </div>

</div>

<?php include("include/displayError.php"); ?>

</body>
<script src="javascript/minjquery-3.6.0.js"></script>
<script src="javascript/script.js"></script>
<script type="text/javascript">
  $(document).ready(function()
  {
    // POPUP

    $('#forgot_password').on('click', function()
    {
      $('#popup').fadeIn(300);
    });

    // Close Popup
    $('#popup-close').on('click', function()
    {
      $('#popup').fadeOut(300);
    });

    // Close Popup when Click outside
    $('#popup').on('click', function()
    {
      $('#popup').fadeOut(300);
    }).children().click(function()
    {
      return false;
    });

    // EMAIL

    $('#return_email').on('click', function()
    {
      var email = $("input[name = 'email']").val();
      var regex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

      $('.comfirm_email').html('');
      $('.show_error').html('');

      if (!email.match(regex))
      {
        $('.show_error').html('Email format not valid.');
      }

      else
      {
        $.ajax({
          type: "GET",
          url: "script/forgotPassword.php?email=" + email + "&emailExist=y"
        }).done(function()
        {
          $('.comfirm_email').html('You got an email.');
        });
      }

    });
  });
</script>
</html>