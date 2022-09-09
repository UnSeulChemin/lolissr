
<?php include("script/verification.php"); ?>

<?php require_once("db/db.php"); ?>

<?php include("db/variablesGlobal.php") ?>

<?php

$takeMessages = $dbh->query('SELECT * FROM chat ORDER BY id DESC LIMIT 0, 10');

while ($message = $takeMessages->fetch())
{
  // REPLACE EMOTICONS

  $message['message'] = str_replace(':)', '<img class="smiley" src="images/emojis/smile.png">', $message['message']);
  $message['message'] = str_replace(';)', '<img class="smiley" src="images/emojis/wink.png">', $message['message']);        
  $message['message'] = str_replace(':|', '<img class="smiley" src="images/emojis/noreaction.png">', $message['message']);
  $message['message'] = str_replace(':3', '<img class="smiley" src="images/emojis/cat.png">', $message['message']);
  $message['message'] = str_replace(':(', '<img class="smiley" src="images/emojis/sad.png">', $message['message']);

  // REPLACE BAD WORDS

  $message['message'] = str_replace('motgrossier', '****', $message['message']);

  // ADMIN

  $name = $message['name'];

  $grade_req = $dbh->prepare('SELECT * FROM chat WHERE name = :name');
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

          if (isset($_SESSION['id']) && !empty($_SESSION['id']) && in_array($_SESSION['id'], $GLOBALS['adminsSession']))
          {
            $hisName = $message['name'];

            if (!array_key_exists($hisName, $GLOBALS['adminsSession']))
            {
              ?>

              <a onclick="deleteThisMemberFromChat(<?php echo $message['id']; ?>)"><img src="images/delete.png" title="delete all chat" alt ="delete all chat"></a>                      

              <?php
            }
          }

          ?>

        </div>

        <h4>

          <?php

          if (isset($_SESSION['id']) && !empty($_SESSION['id']) && in_array($_SESSION['id'], $GLOBALS['adminsSession']))
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

?>
