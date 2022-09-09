
<?php include("script/verification.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=yes, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://kit.fontawesome.com/118716b668.js" crossorigin="anonymous"></script>
    <title>LoliSSR Chat</title>
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu/chat.css">
    <link rel="stylesheet" href="<?php echo $themeStyleSheet; ?>" id="dark_theme">
</head>

<body class="<?php echo $themeClass; ?>">

<div id="container">

  <?php include("include/header.php"); ?>

  <div id="chat_background">

    <div id="chat_section_background">

      <form id="chat_form_avatar_name">

        <img id="chat_form_img" src="images/avatar/<?php echo $userinfo['avatar'];?>">

        <p><?php echo $_SESSION['name']; ?></p>

      </form>

      <?php

      if (isset($_POST['send']))
      {
        if (!empty($_POST['message']))
        {
          $name =  $_SESSION['name'];
          $message = nl2br(htmlspecialchars($_POST['message']));
          $avatar = $userinfo['avatar'];

          $insertMessage = $dbh->prepare('INSERT INTO chat(name, message, avatar) VALUES (:name, :message, :avatar)');
          $insertMessage->bindValue('name', $name);
          $insertMessage->bindValue('message', $message);
          $insertMessage->bindValue('avatar', $avatar);
          $insertMessage->execute();
        }
      }

      ?>

      <section id="messages">

        <?php

        // MESSAGE

        $takeMessages = $dbh->query('SELECT * FROM chat ORDER BY id DESC LIMIT 0, 10');

        while ($message = $takeMessages->fetch())
        {
          // REPLACE EMOTICONS

          // $search
          // $replace
          // $subject

          $message['message'] = str_replace(':)', '<img class="smiley" src="images/emojis/smile.png">', $message['message']);
          $message['message'] = str_replace(';)', '<img class="smiley" src="images/emojis/wink.png">', $message['message']);        
          $message['message'] = str_replace(':|', '<img class="smiley" src="images/emojis/noreaction.png">', $message['message']);
          $message['message'] = str_replace(':3', '<img class="smiley" src="images/emojis/cat.png">', $message['message']);
          $message['message'] = str_replace(':(', '<img class="smiley" src="images/emojis/sad.png">', $message['message']);

          // REPLACE BAD WORDS

          $message['message'] = str_replace('motgrossier', '****', $message['message']);

          // ADMIN

          $name = $message['name'];

          $grade_req = $dbh->prepare('SELECT id FROM chat WHERE name = :name');
          $grade_req->bindValue('name', $name);
          $grade_req->execute();
          $grade = $grade_req->rowCount();

          if (array_key_exists($name, $GLOBALS['adminsSession']))
          {
            $grade = " (ADMIN) ";
          }

          elseif ($grade > 0 AND $grade < 10)
          {
            $grade = " (LVL1) " . $grade . "/10";
          }          

          elseif ($grade >= 10 AND $grade < 50)
          {
            $grade = " (LVL2) " . $grade . "/50";
          }

          elseif ($grade >= 50 AND $grade < 100)
          {
            $grade = " (LVL3) " . $grade . "/100";
          }

          else
          {
            $grade = " (LVL4) ";
          }
          
          ?>

          <div class="message">

            <div id="message_div_img_header">
              <img id="img_header" src="images/avatar/<?php echo $message['avatar'];?>">
            </div>

            <div id="message_div_h_p">

              <div>

                <div id="message_ban_all">

                  <?php

                  if (isset($_SESSION['id']) && !empty($_SESSION['id']) && in_array($sessionid, $GLOBALS['adminsSession']))
                  {
                    $hisName = $message['name'];

                    if (!array_key_exists($hisName, $GLOBALS['adminsSession']))
                    {
                      ?>

                      <a onclick="deleteThisMemberFromChat(<?php echo $message['id']; ?>)"><img src="images/delete.png" title="delete all chat" alt="delete all chat"></a>

                      <?php
                    }
                  }

                  ?>

                </div>

                <h4>

                  <?php

                  if (isset($_SESSION['id']) && !empty($_SESSION['id']) && in_array($sessionid, $GLOBALS['adminsSession']))
                  {
                    $hisName = $message['name'];

                    if (!array_key_exists($hisName, $GLOBALS['adminsSession']))
                    {
                      ?>

                      <a onclick="deleteThisChat(<?php echo $message['id']; ?>)"><img src="images/delete.png" title="delete chat" alt ="delete chat"></a>

                      <?php
                    }
                  }

                  ?>

                  <?php echo $message['name']; ?><?php echo $grade; ?>

                </h4>

                <p><?php echo $message['message']; ?></p>

              </div>

            </div>

          </div>

          <?php
        }

        // MESSAGE END

        ?>

      </section>

      <form id="chat_form_send" method="POST" action="">

        <textarea name="message" placeholder="write a message"></textarea>
        <input id="chat_send" type="submit" name="send" value="Send">

      </form>

        <script>

          setInterval('load_messages()', 1500);
          function load_messages()
          {
            $('#messages').load('chatLoop'); 
          }

        </script>

    </div>

  </div>

<?php include("include/displayError.php"); ?>

</div>

</body>
<script src="javascript/minjquery-3.6.0.js"></script>
<script src="javascript/script.js"></script>
</html>