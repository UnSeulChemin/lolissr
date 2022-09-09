
<?php include("script/verification.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=yes, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://kit.fontawesome.com/118716b668.js" crossorigin="anonymous"></script>
    <title>LoliSSR Upload</title>
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu/upload.css">    
    <link rel="stylesheet" href="<?php echo $themeStyleSheet; ?>" id="dark_theme"> 
</head>

<body class="<?php echo $themeClass; ?>">

<div id="container">

  <?php include("include/header.php"); ?>

  <div id="upload_background">

    <div id="upload_section_background">

      <div id="upload_section_top">

        <div class="h_div">
          <h2 class="h_selector">Upload</h2>

          <div class="element">

              <img src="images/info.png">

              <div class="infobulle">
                <p>Size max : 1mo.</p>
                <p>Jpg or png.</p>
              </div>      

          </div>

        </div>

        <div id="upload_text">
          <p>Recommandation picture will be added to "Community pictures" ! Your picture will be added after having been verified.</p>
        </div> 

      </div>

      <div id="upload_section">

        <?php

        // UPLOAD IMAGE

      	if (isset($_POST['valider']))
      	{
          if (isset($_FILES['image']) AND !empty($_FILES['image']['name']))
          {
            $tailleMax = 1000000;

            if ($_FILES['image']['size'] < $tailleMax)
            {
              $extensionsUpload = explode('/', $_FILES['image']['type']);

              if($extensionsUpload[0] == 'image')
              {
                $type = explode('/', $_FILES['image']['type']);

                if ($type[1] == 'png' || 'jpg' || 'jpeg')
                {
                  $imageName = $_FILES["image"]["name"];
                  $imageSize = $_FILES["image"]["size"];
                  $imageType = $_FILES["image"]["type"];

                  $uploadImage = $dbh->prepare("INSERT INTO upload (name, size, type) VALUES (:name, :size, :type)");
                  $uploadImage->bindValue('name', $imageName);
                  $uploadImage->bindValue('size', $imageSize);
                  $uploadImage->bindValue('type', $imageType);
                  $uploadImage->execute();

                  if ($uploadImage->rowCount() > 0)
                  { 
                      $idImageType = $dbh->lastInsertId() . "." . $type[1];
                      $idImage = $dbh->lastInsertId();

                      $updateImage = $dbh->prepare("UPDATE upload SET name = :name WHERE id = :id");
                      $updateImage->bindValue('name', $idImageType);
                      $updateImage->bindValue('id', $idImage);
                      $updateImage->execute();               

                      $chemin = "images/upload/" . $idImage  . "." . $type[1];
                      $resultat = move_uploaded_file($_FILES['image']['tmp_name'], $chemin);

                      if ($resultat)
                      {
                        $messageValid = "Your image file has been successfully sent."; 
                      }
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

        // UPLOAD IMAGE END

        ?>

      </div>

      <form name="" id="upload_img" method="post" action="" enctype="multipart/form-data">
          <input id="upload_file" type="file" name="image">
          <label for="upload_file" class="btn">Select Image</label>
          
          <input id="upload_submit" type="submit" name="valider" value="Send">
      </form>

    </div>

    <?php

    // SHOW IMAGE

    if (isset($_SESSION['id']) && !empty($_SESSION['id']) && in_array($sessionid, $GLOBALS['adminsSession']))
    {

      $allImg = $dbh->query('SELECT * FROM upload ORDER BY id DESC');

      // POUR LE COMPTEUR DIV DE 4 IMAGES A LA FIN
      $last = $allImg->rowCount();

      if ($allImg->rowCount() > 0)
      {
        
        $all = $allImg->fetch();

        ?>      

        <div id="upload_section_admin">

          <div id="delete_all_img_div">

            <a onclick="deleteAllImage(<?php echo $all['id']; ?>)"><img src="images/delete.png" title="delete all image" alt ="delete all image"></a>

          </div>

        <?php

        $compteur = 0;
        $compteurElement = 0; 

        $allImg = $dbh->query('SELECT * FROM upload ORDER BY id DESC');

        while ($image = $allImg->fetch())
        {
          $date = new DateTime($image['date']);

          if ($compteur == 0)
          {
            ?>

            <div id="show_img">

            <?php
          }

          ?>

              <div id="show_img_div">

                <div id="img_container">
                  <img src="images/upload/<?php echo $image['name'];?>">
                </div>

                <div id="img_text">

                  <p id="img_text_date"><?php echo $date->format('d/m/Y'); ?>

                    <a id="img_delete" onclick="deleteThisImage(<?php echo $image['id']; ?>)">
                      <img src="images/delete.png" title="delete image" alt="delete image">
                    </a>

                  </p>

                  <p id="img_text_size">size : <?php echo $image['size']; ?>
                    
                    <a id="img_download" href="images/upload/<?php echo $image['name'];?>" download>
                      <img src="images/download.png" title="download image" alt="download image">
                    </a>

                  </p>

                  <p id="img_text_type"><?php echo $image['type']; ?></p>

                </div>

              </div>

          <?php

          $compteur++;
          $compteurElement++;

          if ($compteur % 4 == 0 || $compteurElement == $last)
          {
            $compteur = 0;

            ?>

            </div>

            <?php
          }

        }

        ?>

        </div>

      <?php

      }

    }

    // SHOW IMAGE END

    ?>

  </div>

<?php include("include/displayError.php"); ?>

</div>

</body>
<script src="javascript/minjquery-3.6.0.js"></script>
<script src="javascript/script.js"></script>
</html>