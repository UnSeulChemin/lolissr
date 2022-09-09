
<?php include("script/verification.php"); ?>

<?php require_once("db/db.php"); ?>

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
          $id = $_SESSION['id'];

          $req = $dbh->prepare('SELECT * FROM members WHERE id = :id');
          $req->bindValue('id', $id);
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
