 
<?php include("script/verification.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head> 
    <meta name="google-site-verification" content="ANvAT_T4IMJ72_QwdRpUBYYN9_3OoAXZun7GsVHA9Fo" />
    <link rel="canonical" href="https://lolissr.com"/>    
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=yes, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="index, follow" />
    <meta name="author" content="loliSSR"/>
		<meta name="copyright" content="loliSSR"/>
    <meta name="description" content="Discover LoliSSR !"/>
    <meta name="description" content="LoliSSR is the best site to find your favorite waifus !"/>
    <meta name="keywords" content="Anime, Image, Manga, Cute, LoliSSR">
    <script src="https://kit.fontawesome.com/118716b668.js" crossorigin="anonymous"></script>      
    <title>LoliSSR <?php if (isset($_GET['type'])) { echo $_GET['type']; } else if (isset($_GET['gender'])) { echo $_GET['gender']; }?></title>
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="<?php echo $themeStyleSheet; ?>" id="dark_theme">
</head>

<body class="<?php echo $themeClass; ?>">

<div id="container">

  <?php include("include/header.php"); ?>

  <div id="index_background">

    <?php include("include/listRight.php"); ?>

    <div id="section_top">

      <div>
        <p>LoliSSR find your favorite waifu !</p>
        <p>Currently on page <?php if (isset($_GET['p'])) { echo $_GET['p']; } else { echo "1"; } ?>.</p>
      </div>

      <div id="section_top_div_dark">
        <div class="section_top_div_dark_container">
          <img class="btn-toggle icon_dark" src="images/dark.png" alt="dark_moon">
        </div>
      </div>   
      
    </div>

    <?php 

    if (isset($_GET['error']) && $_GET['error'] == "timeout")
    {
      ?>

      <div id="errorTimeOut">

        <p>You can't access this page.</p>

      </div>

      <?php
    }

    ?>   

    <?php

    // SECTION IMG

    // ON INITIALISE LE GET P, AUTREMENT IL SERA PAR DEFAUT A 1

    $imagesParPage = 8;

    if (!empty($_GET['p']))
    {
      $getP = $_GET['p'];
    }

    else
    {
      $getP = 1;
    }

    $depart = ($getP -1) * $imagesParPage;

    // TYPE

    if (isset($_GET['type']))
    {
      $type = $_GET['type'];

      // COUNT POUR COMPTER ET AIDER A LA PAGINATION

      $countImg = $dbh->prepare('SELECT COUNT(*) AS nombreImages FROM images WHERE type = :type');
      $countImg->bindValue('type', $type);
      $countImg->execute();

      if ($countImg->rowCount() > 0)
      {
        $numberImg = $countImg->fetch();
      } 

      $selectImg = $dbh->prepare('SELECT * FROM images WHERE type = :type ORDER BY id DESC LIMIT ' . $depart . ', ' . $imagesParPage);
      $selectImg->bindValue('type', $type);
      $selectImg->execute();
    }

    // GENDER

    else if (isset($_GET['gender']))
    {
      $gender = $_GET['gender'];

      // COUNT POUR COMPTER ET AIDER A LA PAGINATION

      $countImg = $dbh->prepare('SELECT COUNT(*) AS nombreImages FROM images WHERE gender = :gender');
      $countImg->bindValue('gender', $gender);
      $countImg->execute();

      if ($countImg->rowCount() > 0)
      {
        $numberImg = $countImg->fetch();
      }

      $selectImg = $dbh->prepare('SELECT * FROM images WHERE gender = :gender  ORDER BY id DESC LIMIT ' . $depart . ', ' . $imagesParPage);
      $selectImg->bindValue('gender', $gender);
      $selectImg->execute();
    }

    // INDEX

    else
    {
      // COUNT POUR COMPTER ET AIDER A LA PAGINATION

      $countImg = $dbh->prepare('SELECT COUNT(*) AS nombreImages FROM images');
      $countImg->execute();

      if ($countImg->rowCount() > 0)
      {
        $numberImg = $countImg->fetch();
      }

      $selectImg = $dbh->query('SELECT * FROM images ORDER BY id DESC LIMIT ' . $depart . ', ' . $imagesParPage);
    }

    $lastPageImages = $selectImg->rowCount();
    $compteur = 0;
    $compteurImg = 0;

    while ($images = $selectImg->fetch())
    {
      $date = new DateTime($images['date']);

      $type = $images['type'];
      $gender = $images['gender'];

      if ($compteur == 0)
      {
        ?>

        <div class="section_img">

        <?php
      }

      ?>

          <div class="div_img">
            <div class="img_container">
              <img src="images/loli/<?php echo $images['nom']; ?>" alt="image_anime">
            </div>

            <div class="img_text">
              <p class="img_text_date"><?php echo $date->format('d/m/Y'); ?></p>
              <a class="sub" href="index?type=<?php echo $type; ?>">List type : <?php echo $images['type']; ?></a>
              <a class="sub" href="index?gender=<?php echo $gender; ?>">Gender : <?php echo $images['gender']; ?></a>
            </div>
          </div>

      <?php

      $compteur++;
      $compteurImg++;

      if ($compteur % 4 == 0 || $compteurImg == $lastPageImages)
      {

        $compteur = 0;

        ?>

        </div>

        <?php
      }

    }

    // SECTION IMG END

    ?>

    <div class="index_footer_pages">

      <div class="index_footer_numbers">

        <?php

        // FOOTER PAGES

        $numberPages = ceil($numberImg['nombreImages'] / $imagesParPage);

        $url = "";

        if (isset($_GET['type']) && !empty($_GET['type']))
        {
          $url = "type=" . $_GET['type'] . "&";
        }

        else if (isset($_GET['gender']) && !empty($_GET['gender']))
        {
          $url = "gender=" . $_GET['gender'] . "&";         
        }

        ?>

        <?php 

        $currentPageNumber = 1;
        $myGetP = 0;

        if ($getP % 1 == 0)
        {
          $myGetPlus = 5;
          $myGetMoins = 5;
        }

        elseif ($getP % 2 == 0)
        {
            $myGetPlus = 4;
            $myGetMoins = 1;
        }

        else if ($getP % 3 == 0)
        {
          $myGetPlus = 3;
          $myGetMoins = 2;
        }

        else if ($getP % 4 == 0)
        {
          $myGetPlus = 2;
          $myGetMoins = 3;
        }

        else if ($getP % 5 == 0)
        {
          $myGetPlus = 1;
          $myGetMoins = 4;
        }

        $currentPageNumber = $getP + $myGetP;

        if ($getP > 1)
        {
          if ($getP - $myGetMoins < 1)
          {
            $myPageLess = 1;
          }

          else
          {
            $myPageLess = $getP - $myGetMoins;
          }

          ?>

          <a href="index?<?php echo $url; ?>p=<?php echo $myPageLess; ?>">-</a>

          <?php

        }

        for ($i = $getP; $i <= $getP + 4; $i++)
        {
          if ($i <= $numberPages)
          {
            ?><a href="index?<?php echo $url; ?>p=<?php echo $i; ?>"><?php echo $i; ?></a><?php
          }
        }

        if ($getP < $numberPages && $getP + 5 < $numberPages)
        {
          ?> <a href="index?<?php echo $url; ?>p=<?php echo $getP + $myGetPlus; ?>">+</a><?php
        }

        // FOOTER PAGES END

        ?>
        
      </div>

    </div>

  </div>

</div>
  
</body>
<script src="javascript/minjquery-3.6.0.js"></script>
<script src="javascript/script.js"></script>
</html>