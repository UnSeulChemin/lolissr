
<?php include("script/verification.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=yes, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://kit.fontawesome.com/118716b668.js" crossorigin="anonymous"></script>
    <title>LoliSSR Contact</title>
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">    
    <link rel="stylesheet" href="css/style.css">		
    <link rel="stylesheet" href="css/menu/contact.css">
    <link rel="stylesheet" href="<?php echo $themeStyleSheet; ?>" id="dark_theme">    
</head>

<body class="<?php echo $themeClass; ?>">

<div id="container">

  <?php include("include/header.php"); ?>

  <div id="contact_background">

    <div id="contact_section_background">

      <div class="contact_section_top">

        <div class="h_div">
          <h2 class="h_selector">Contact</h2>

          <div class="element">

            <img src="images/info.png">

            <div class="infobulle">
              <p>Name between 5 and 12 characters.</p>
              <p>Name only allow letters and white space.</p>
              <p>Email correct format.</p>
            </div>

          </div>

        </div>

      </div>

      <form id="contact_section" method="POST" action="">

          <label for="name">You name</label>
          <input type="text" name="name" placeholder="put your name">

          <label for="email">Your email</label>
          <input type="email" name="email" placeholder="put your email">

          <label for="subject">Your subject</label>
          <input type="text" name="subject" placeholder="put your subject">

          <label for="message">Your message</label>
          <textarea type="text" name="message" placeholder="put your message"></textarea>

          <?php

          if (isset($_POST['contact']))
          {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $subject = trim($_POST['subject']);
            $message = trim($_POST['message']);

            if (!empty($name) && !empty($email) && !empty($subject) && !empty($message))
            {
              if (strlen($name) >= 5 && strlen($name) <= 12)
              {   
                if (preg_match("/^[a-zA-Z-' ]*$/", $name)) // only letter and white space
                {
                  if (filter_var($email, FILTER_VALIDATE_EMAIL))
                  {
                    echo "<p class='review_right'>Your message has been sent successfully.</p>";

                    // SEND INTO BDD

                    $insertContact = $dbh->prepare("INSERT INTO contact (name, email, subject, message) VALUES (:name, :email, :subject, :message)");
                    
                    $insertContact->bindValue('name', $name);
                    $insertContact->bindValue('email', $email);
                    $insertContact->bindValue('subject', $subject);
                    $insertContact->bindValue('message', $message);
                    $insertContact->execute();
                  }

                  else
                  {
                    echo "<p class='review_wrong'>Invalid email format.</p>";
                  }


                } // IF PREG NAME

                else
                {
                  echo "<p class='review_wrong'>Only letters and white space allowed for your name.</p>";
                }

              } // IF STRLEN

              else
              {
                echo "<p class='review_wrong'>Name too short / long.</p>";
              }

            } // IF !EMPTY

            else
            {
              echo "<p class='review_wrong'>Please complete all fields.</p>";
            }

          }

          ?>

          <input id="form_submit" type="submit" name="contact" value="Envoyer">

      </form>

    </div>

    <?php 

    if (isset($_SESSION['id']) && !empty($_SESSION['id']) && in_array($sessionid, $GLOBALS['adminsSession']))
    {

      $allContacts = $dbh->query('SELECT * FROM contact ORDER BY id DESC');

      while ($contacts = $allContacts->fetch())
      {
        ?>

        <div id="contact_section_admin">

          <div id="message_n_dump_all">

            <a onclick="deleteAllContact(<?php echo $contacts['id']; ?>)"><img src="images/delete.png" title="delete all contact" alt ="delete all contact"></a>

          </div>

          <div id="contact_section_admin_text">

            <p>name : <?php echo $contacts['name'];  ?> (<?php echo $contacts['id']; ?>)
              <a onclick="deleteThisContact(<?php echo $contacts['id']; ?>)">
                <img src="images/delete.png" title="delete contact" alt ="delete contact">
              </a>
            </p>

            <p>email : <?php echo $contacts['email']; ?></p>
            <p>subject : <?php echo $contacts['subject']; ?></p>
            <p>subject : <?php echo $contacts['message']; ?></p>

          </div>      

        </div>

        <?php
      }
    }

    ?>

  </div>

<?php include("include/displayError.php"); ?>  

</div>

</body>
<script src="javascript/minjquery-3.6.0.js"></script>
<script src="javascript/script.js"></script>
</html>