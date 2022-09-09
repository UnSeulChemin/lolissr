
<?php include("script/verification.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=yes, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://kit.fontawesome.com/118716b668.js" crossorigin="anonymous"></script>
    <title>LoliSSR Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu/admin.css">
    <link rel="stylesheet" href="<?php echo $themeStyleSheet; ?>" id="dark_theme">
</head>

<body class="<?php echo $themeClass; ?>">

<div id="container">

    <?php include("include/header.php"); ?>

    <div id="admin_background">

        <div id="admin_section_background">

            <section id="admin_section">

                <div id="admin_section_top">      

                    <div class="h_div">
                      <h2 class="h_selector">Admin part</h2>
                    </div>

                    <div id="div_admin_section_name">
                        <p><?php echo $_SESSION['name']; ?></p>
                    </div>  

                </div>      

                <form id ="search_form" method="GET">

                    <div id="search_form_div">
                        
                        <input id="search_form_search" type="search" name="search" placeholder="Search user">
                        <input id="search_form_submit" type="submit" name="" value="Validate">

                        <?php 

                        $allUsers = $dbh->query('SELECT * FROM members ORDER BY id DESC');

                        if (isset($_GET['search']) AND !empty($_GET['search']))
                        {
                            $search = htmlspecialchars($_GET['search']);

                            $allUsers = $dbh->query('SELECT * FROM members WHERE name LIKE "%' . $search . '%" ORDER BY id DESC');
                        }

                        ?>

                    </div>

                </form>            

                <form id="add_form" method="POST" action="">

                    <div id="add_form_div">

                        <input id="add_form_username" name="name" placeholder="Username">          
                        <input id="add_form_email" name="email" placeholder="Email">
                        <input id="add_form_password" type="password" name="password" placeholder="Password">

                        <input id="add_form_submit" type="submit" name="register" value="Add">

                    </div>

                </form>

                <?php

                // ADD MEMNBER

                if (isset($_POST['register']))
                {
                    $name = trim($_POST['name']);
                    $email = trim($_POST['email']);
                    $password = trim($_POST['password']);
                    $avatar = "default.jpg";
                    $ban ="N"; 

                    if (!empty($name) AND !empty($email) AND !empty($password))
                    {
                        if (strlen($name) >= 5 && strlen($name) <= 12 AND strlen($password) >= 5)
                        {
                            $takeName = $dbh->prepare('SELECT * FROM members WHERE name = :name');
                            $takeName->bindValue('name', $name);
                            $takeName->execute();

                            if ($takeName->rowCount() == 0)
                            {
                                if(filter_var($email, FILTER_VALIDATE_EMAIL))
                                {
                                    $verifyEmail = $dbh->prepare('SELECT * FROM members WHERE email = :email');
                                    $verifyEmail->bindValue('email', $email);
                                    $verifyEmail->execute();

                                    if ($verifyEmail->rowCount() == 0)
                                    {
                                        $pattern = "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?([^\w\s]|[_])).{5,}$/";
                                        // at least 5 characters, at least 1 numeric character, at least 1 lowercase letter, at least 1 uppercase letter, at special character

                                        if (preg_match($pattern, $password))
                                        {
                                            $password = password_hash($_POST['password'], PASSWORD_DEFAULT, array($GLOBALS['salt1']));

                                            $insertUser = $dbh->prepare('INSERT INTO members (name, email, password, avatar, ban) VALUES (:name, :email, :password, :avatar, :ban)');
                                            $insertUser->bindValue('name', $name);
                                            $insertUser->bindValue('email', $email);
                                            $insertUser->bindValue('password', $password);
                                            $insertUser->bindValue('avatar', $avatar);    
                                            $insertUser->bindValue('ban', $ban);
                                            $insertUser->execute();

                                            header('Location: admin.php');                                

                                        } // IF PASSWORD (SECURITY)

                                        else
                                        {
                                            $messageError = "Password not enough strong";
                                        }

                                    } // IF EMAIL EXIST

                                    else
                                    {
                                        $messageError = "Email already taken.";                                        
                                    }

                                } // IF EMAIL FORMAT

                                else
                                {
                                    $messageError = "Email error.";                     
                                }                                

                            } // IF NAME EXIST

                            else
                            {
                                $messageError = "Name already taken";
                            }

                        } // IF STRLEN

                        else
                        {
                            $messageError = "Username or password too short / long.";
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

                // ADD MEMNBER END

                ?>

                <div id="total_users">

                    <?php

                    // TOTAL REGISTERED USERS

                    $show_user_number = $dbh->query('SELECT * FROM members');
                    $user_number = $show_user_number->rowCount();

                    echo "<p>" . $user_number . " user"?><?php if($user_number !=1) { echo "s"; } ?><?php echo " registered." . "</p>";

                    ?>

                </div>

                <div id="show_n_react">
                    
                <?php

                // SHOW AND BAN MEMBERS

                if ($allUsers->rowCount() > 0)
                {
                    while ($user = $allUsers->fetch())
                    {
                        ?>
                        <div class="show_n_react_div">

                            <div class="show_n_react_div_un">

                                <div id="show_n_react_div_un_img">
                                  <img id="img_header" src="images/avatar/<?php echo $user['avatar'];?>">
                                </div>

                                <div>
                                    <p><?php echo $user['name']; ?></p>
                                </div>

                            </div>

                            <div class="show_n_react_div_deux">
                                <a onclick="deleteMemberFromAdmin(<?php echo $user['id']; ?>)"><img src="images/delete.png" title="delete user" alt ="delete user"></a>
                            </div>

                            <div class="show_n_react_div_trois">
                                <a id="private" href="private.php?id=<?php echo $user['id']; ?>"><img src="images/message.png"></a>
                            </div>

                        </div>
                        <?php
                    }
                }

                else
                {
                    echo "<p>" . "Aucun utilisateur trouvé. " . "</p>";
                }

                // SHOW AND BAN MEMBERS END

                ?>

                </div>

            </section>

        </div>

    </div>

<?php include("include/displayError.php"); ?>

</div>

</body>
<script src="javascript/minjquery-3.6.0.js"></script>
<script src="javascript/script.js"></script>
</html>