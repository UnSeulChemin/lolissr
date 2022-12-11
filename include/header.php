
<?php require_once("db/db.php"); ?>

<?php include ("db/variablesGlobal.php"); ?>

<header>

	<div id="header_first_part">
		<h1><a href="index">LoliSSR</a></h1>
	</div>	

	<div class="accordion">
		<h2>Menu</h2>
	</div>

	<div class="accordion-item">

		<nav>
			<ul>
				<li><a href="list">List</a></li>
				<li><a href="contact">Contact</a></li>
				<li><a href="upload">Upload</a></li>

				<?php

				if (isset($_SESSION['id']) && !empty($_SESSION['id']))
				{
					?>

					<li><a href="chat">Chat</a></li>
					<li><a href="message">Message</a></li>

					<?php

					$sessionid = $_SESSION['id'];

					if (isset($_SESSION['id']) && !empty($_SESSION['id']) && in_array($sessionid, $GLOBALS['adminsSession']))
					{
						?>

						<li><a href="admin">Admin</a></li>

						<?php
					}

				}			

				?>			

			</ul>

		</nav>

	</div>

	<div id="header_last_part">

		<?php

		if (isset($_SESSION['id']) && !empty($_SESSION['id']))
		{
			?>

			<div id="header_member">

				<?php

				if(isset($_SESSION['id']) && !empty($_SESSION['id']))
				{
					 $id = $_SESSION['id'];

					 $req = $dbh->prepare('SELECT * FROM members WHERE id = :id');
					 $req->bindValue('id', $id);
					 $req->execute();
					 $userinfo = $req->fetch();
				}

				?>

			    <div id="header_member_div_img">
			    	<img src="images/avatar/<?php echo $userinfo['avatar'];?>">
			    </div>

		      	<div id="header_member_div">
		      		<a id="header_member_name" href="settings"><?php echo $_SESSION['name']; ?></a>
					<a id="header_member_logout" href="script/logout">Logout</a>
				</div>

			</div>

			<?php	
		}

		else	
		{
			?>

			<h2><a href="register">Register</a></h2>
			<h2><a href="login">Log in</a></h2>

			<?php
		}

		?>

	</div>

</header>
