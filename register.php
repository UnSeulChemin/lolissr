
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
    <title>LoliSSR Register</title>
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">		
    <link rel="stylesheet" href="css/style.css">		
    <link rel="stylesheet" href="css/menu/register.css">
    <link rel="stylesheet" href="<?php echo $themeStyleSheet; ?>" id="dark_theme">    
</head>

<body class="<?php echo $themeClass; ?>">

<div id="container">

  <?php include("include/header.php"); ?>

  <div id="register_background">

	  <div id="register_section_background">

      <div id="register_section_top">

			 	<div class="h_div">
			    <h2 class="h_selector">Register your account</h2>

	        <div class="element">

	            <img src="images/info.png">

	            <div class="infobulle">
	              <p>Name between 5 and 12 characters.</p>
	              <p>Can't use already exist name.</p>
	              <p>Password at least 5 characters.</p>
	              <p>Password should have 1 lowercase, 1 uppercase, 1 numeric, 1 special character.</p>
	              <p>Captcha code : write the same code and validate.</p>
	            </div>

	      	</div>

				</div>

			</div>

			<form id="register_section" method="POST" action="">

				<p>register your account here</p>

				<div id="register_section_username_div">
					<input type="text" name="name" placeholder="Username">
				</div>

				<div id="register_section_email_div">
					<input type="email" name="email" placeholder="Email">
				</div>

				<div id="register_section_password_div">
					<input type="password" name="password" placeholder="Password" id="pass">
					<img src="images/hide.png" id="eye" onClick="changeY()">
				</div>

				<div id="register_section_password_x_div">
					<input id="reg_x_password" type="password" name="reg_x_password" placeholder="Password Validation">
					<img src="images/hide.png" id="eye_x" onClick="changeX()">
				</div>

				<div id="register_section_footer">
	        <img id="img_captcha" src="script/captcha.php">
	        <input id="input_captcha" name="captcha" type="text" placeholder="CODE" maxlength="4">

					<?php

					// REGISTER

					if (isset($_POST['register']))
					{
	          $name = trim($_POST['name']);
	          $email = trim($_POST['email']);
	          $password = trim($_POST['password']);
	          $password_verify = trim($_POST['reg_x_password']);
	          $captcha = trim($_POST['captcha']);
						$avatar = "default.jpg";
						$ban ="N";

						$messageError = "";
						$messageValid = "";

						if (!empty($name) AND !empty($email) AND !empty($password) AND !empty($password_verify) AND !empty($captcha))
						{
							if (strlen($name) >= 5 && strlen($name) <= 12 AND strlen($password) >= 5 AND strlen($password_verify) >= 5)
							{
								$takeName = $dbh->prepare('SELECT * FROM members WHERE name = :name');
								$takeName->bindValue('name', $name);
								$takeName->execute();

								if ($takeName->rowCount() == 0)
								{
									if (filter_var($email, FILTER_VALIDATE_EMAIL))
									{
										$verifyEmail = $dbh->prepare('SELECT * FROM members WHERE email = :email');
										$verifyEmail->bindValue('email', $email);
										$verifyEmail->execute();

										if ($verifyEmail->rowCount() == 0)
										{
											$passwordPattern = "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?([^\w\s]|[_])).{5,}$/";
											// at least 5 characters, at least 1 numeric character, at least 1 lowercase letter, at least 1 uppercase letter, at least 1 special character

											if (preg_match($passwordPattern, $password))
											{
												if ($password === $password_verify)
												{
													if (filter_var($captcha, FILTER_VALIDATE_INT))
													{
														if ($_SESSION["code"] == $captcha)
														{
															$password = password_hash($_POST['password'], PASSWORD_DEFAULT, array($GLOBALS['salt1']));		

															$insertUser = $dbh->prepare('INSERT INTO members(name, email, password, avatar, ban) VALUES (:name, :email, :password, :avatar, :ban)');
															$insertUser->bindValue('name', $name);
															$insertUser->bindValue('email', $email);
															$insertUser->bindValue('password', $password);
															$insertUser->bindValue('avatar', $avatar);
															$insertUser->bindValue('ban', $ban);
															$req = $insertUser->execute();

															if ($req)
															{
																$_SESSION['name'] = $name;
																$_SESSION['password'] = $password;
																$_SESSION['email'] = $email;
	 															$_SESSION['id'] = !empty($req['id']) ? $req['id'] : NULL;
	 
				                    		$_SESSION['register'] = "new";

																$messageValid = "you can now log in here.<a class='review_right_a' href='login'>Log In</a>";
															}

														} // IF STRLEN (CODE) + VALID

														else
														{
															$messageError = "Captcha code does not match. Try Again.";											
														}
														
													} // IF CAPTCHA FILTER INT

													else 
													{
														$messageError = "Capcha only number are allowed.";	
													}

												} // IF PASSWORD (IDENTICAL)

												else
												{
													$messageError = "Password doesn't match.";
												}

											} // IF PASSWORD (SECURITY)

											else
											{
												$messageError = "Password not enough strong.";
											}

										}  // IF EMAIL EXIST

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
									$messageError = "Name already taken.";
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

					// REGISTER END

					?>

					<input id="register_submit" type="submit" name="register" value="Register">
				</div>

			</form>

  	</div>

  </div>

</div>

<?php include("include/displayError.php"); ?>

</body>
<script src="javascript/minjquery-3.6.0.js"></script>
<script src="javascript/script.js"></script>
</html>