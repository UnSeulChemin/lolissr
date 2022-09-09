
<?php include("script/verification.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=yes, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://kit.fontawesome.com/118716b668.js" crossorigin="anonymous"></script>
    <title>LoliSSR List</title>
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">    
    <link rel="stylesheet" href="css/style.css">		
    <link rel="stylesheet" href="css/menu/list.css">
    <link rel="stylesheet" href="<?php echo $themeStyleSheet; ?>" id="dark_theme">
</head>

<body class="<?php echo $themeClass; ?>">

<div id="container">

  <?php include("include/header.php"); ?>

  <div id="list_background">

    <div id="list_section_background">

      <div id="list_section">

        <div id="picture_by_type">
          <div class="h_div">
            <h2 class="h_selector">All pictures by type</h2>
          </div>

          <ul>
<!--             <li><a href="index?type=可爱" target="_blank"><span lang="cn">可爱</span></a></li>          
            <li><a href="index?type=萝莉" target="_blank"><span lang="cn">萝莉</span></a></li>
            <li><a href="index?type=御姐" target="_blank"><span lang="cn">御姐</span></a></li> -->

            <div id="list_type_div_un">     
              <li><a href="index?type=原神" target="_blank"><span lang="cn">原神</span></a></li>
              <li><a href="index?type=崩坏3" target="_blank"><span lang="cn">崩坏3</span></a></li>
              <li><a href="index?type=明日方舟" target="_blank"><span lang="cn">明日方舟</span></a></li>
            </div>

            <div id="list_type_div_deux">
              <li><a href="index?type=崩坏:星穹铁道" target="_blank"><span lang="cn">崩坏:星穹铁道</span></a></li>
            </div>

          </ul>
        </div>

        <div id="picture_by_gender">
          <div class="h_div">
            <h2 class="h_selector">All pictures by gender</h2>
          </div>

          <ul>
            <li><a href="index?gender=女" target="_blank">女的</a></li>
            <li><a href="index?gender=男" target="_blank">男的</a></li>
          </ul>

        </div>

        <div id="other_picture">
          <div class="h_div">
            <h2 class="h_selector">Others pictures</h2>
          </div>

          <ul>
<!--             <li><a href="index?type=原神" target="_blank">原神</a></li>
            <li><a href="index?type=情侣头像" target="_blank">情侣头像</a></li> -->
            <li><a href="">INCOMMING</a></li>
          </ul>

        </div>

      </div>

    </div>

  </div>

<?php include("include/displayError.php"); ?>

</div>

</body>
<script src="javascript/minjquery-3.6.0.js"></script>
<script src="javascript/script.js"></script>
</html>