
<?php

session_start(); 

include("../db/db.php"); 

include("../db/variablesGlobal.php");


if (isset($_GET['id']) AND !empty($_GET['id']))
{

	// VARIABLES

	$getid = $_GET['id'];
	$sessionid = $_SESSION['id'];


	// DELETE THIS CONTACT

	if (isset($_GET['deleteThisContact']) && $_GET['deleteThisContact'] == "Y")
	{
		$takeContact = $dbh->prepare('SELECT * FROM contact WHERE id = :id');
		$takeContact->bindValue('id', $getid);
		$takeContact->execute();

		if ($takeContact->rowCount() > 0 && in_array($sessionid, $GLOBALS['adminsSession']))
		{
			$deleteContact = $dbh->prepare('DELETE FROM contact WHERE id = :id');
			$deleteContact->bindValue('id', $getid);
			$deleteContact->execute();
		}
	}


	// DELETE ALL CONTACT

	if (isset($_GET['deleteAllContact']) && $_GET['deleteAllContact'] == "Y")
	{
		$takeContact = $dbh->prepare('SELECT * FROM contact WHERE id = :id');
		$takeContact->bindValue('id', $getid);
		$takeContact->execute();

		if ($takeContact->rowCount() > 0 && in_array($sessionid, $GLOBALS['adminsSession']))
		{
			$deleteContact = $dbh->prepare('DELETE FROM contact');
			$deleteContact->execute();
		}
	}


	// DELETE THIS IMAGE

	if (isset($_GET['deleteThisImage']) && $_GET['deleteThisImage'] == "Y")
	{
		$takeImage = $dbh->prepare('SELECT * FROM upload WHERE id = :id');
		$takeImage->bindValue('id', $getid);
		$takeImage->execute();

		if ($takeImage->rowCount() > 0 && in_array($sessionid, $GLOBALS['adminsSession']))
		{
			$dataImage = $takeImage->fetch();

			$deleteImage = $dbh->prepare('DELETE FROM upload WHERE id = :id');
			$deleteImage->bindValue('id', $getid);
			$deleteImage->execute();

			unlink("../images/upload/" . $dataImage['name']);
		}
	}	


	// DELETE ALL IMAGE

	if (isset($_GET['deleteAllImage']) && $_GET['deleteAllImage'] == "Y")
	{
		$takeImage = $dbh->prepare('SELECT * FROM upload WHERE id = :id');
		$takeImage->bindValue('id', $getid);
		$takeImage->execute();

		if ($takeImage->rowCount() > 0 && in_array($sessionid, $GLOBALS['adminsSession']))
		{
			$dataImage = $takeImage->fetch();

			$deleteImage = $dbh->prepare('DELETE FROM upload');
			$deleteImage->execute();

			$dir = new DirectoryIterator(dirname("../images/upload/" . $dataImage['name']));

			foreach ($dir as $fileinfo)
			{
			   	if (!$fileinfo->isDot())
			   	{
			        unlink($fileinfo->getPathname());
			    }
			}
		}
	}


	// DELETE THIS CHAT

	if (isset($_GET['deleteThisChat']) && $_GET['deleteThisChat'] == "Y")
	{
		$takeChat = $dbh->prepare('SELECT * FROM chat WHERE id = :id');
		$takeChat->bindValue('id', $getid);
		$takeChat->execute();

		if ($takeChat->rowCount() > 0 && in_array($sessionid, $GLOBALS['adminsSession']))
		{
			$deleteChat = $dbh->prepare('DELETE FROM chat WHERE id = :id');
			$deleteChat->bindValue('id', $getid);
			$deleteChat->execute();
		}	
	}


	// DELETE THIS MEMBER FROM CHAT

	if (isset($_GET['deleteThisMemberFromChat']) && $_GET['deleteThisMemberFromChat'] == "Y")
	{
		$takeChat = $dbh->prepare('SELECT * FROM chat WHERE id = :id');
		$takeChat->bindValue('id', $getid);
		$takeChat->execute();

		if ($takeChat->rowCount() > 0 && in_array($sessionid, $GLOBALS['adminsSession']))
		{
			$dataChat = $takeChat->fetch();
			$dataAvatar = $dataChat['avatar'];	
			$dataName = $dataChat['name'];

			$deleteChat = $dbh->prepare('DELETE FROM chat WHERE name = :name');
			$deleteChat->bindValue('name', $dataName);
			$deleteChat->execute();		

			$deleteMenber = $dbh->prepare('DELETE FROM members WHERE name = :name');
			$deleteMenber->bindValue('name', $dataName);
			$deleteMenber->execute();

			if ($dataAvatar != "default.jpg")
			{
				unlink("../images/avatar/" . $dataAvatar);
			}			
		}	
	}


	// BAN USER

	if (isset($_GET['banThisMember']) && $_GET['banThisMember'] == "Y")
	{
		if (isset($_GET['id']) AND !empty($_GET['id']))
		{
			$getid = $_GET['id'];
			$sessionid = $_SESSION['id'];
			$status = "Y";

		    $updateUserStatus = $dbh->prepare("UPDATE members SET ban = :ban WHERE id = :id");
		    $updateUserStatus->bindValue('ban', $status);
		    $updateUserStatus->bindValue('id', $getid);
		    $updateUserStatus->execute();
		}
	}


	// DELETE THIS MEMBER

	if (isset($_GET['deleteThisMember']) && $_GET['deleteThisMember'] == "Y")
	{
		$takeUser = $dbh->prepare('SELECT * FROM members WHERE id = :id');
		$takeUser->bindValue('id', $getid);
		$takeUser->execute();

		if ($takeUser->rowCount() > 0 && !in_array($sessionid, $GLOBALS['adminsSession']))
		{
			$dataUser = $takeUser->fetch();
			$dataAvatar = $dataUser['avatar'];
			$dataName = $dataUser['name'];		

			$banUsers = $dbh->prepare('DELETE FROM members WHERE id = :id');
			$banUsers->bindValue('id', $getid);
			$banUsers->execute();

			$deleteChat = $dbh->prepare('DELETE FROM chat WHERE name = :name');
			$deleteChat->bindValue('name', $dataName);
			$deleteChat->execute();		

		    session_destroy();

			if ($dataAvatar != "default.jpg")
			{
				unlink("../images/avatar/" . $dataAvatar);
			}
		}
	}


	// DELETE THIS MEMBER FROM ADMIN

	if (isset($_GET['deleteThisMemberFromAdmin']) && $_GET['deleteThisMemberFromAdmin'] == "Y")
	{
		$takeUser = $dbh->prepare('SELECT * FROM members WHERE id = :id');
		$takeUser->bindValue('id', $getid);
		$takeUser->execute();

		if ($takeUser->rowCount() > 0 && in_array($sessionid, $GLOBALS['adminsSession']))
		{
			$dataUser = $takeUser->fetch();
			$dataAvatar = $dataUser['avatar'];
			$dataName = $dataUser['name'];

			$banUsers = $dbh->prepare('DELETE FROM members WHERE id = :id');
			$banUsers->bindValue('id', $getid);
			$banUsers->execute();

			$deleteChat = $dbh->prepare('DELETE FROM chat WHERE name = :name');
			$deleteChat->bindValue('name', $dataName);
			$deleteChat->execute();		

			if ($dataAvatar != "default.jpg")
			{
				unlink("../images/avatar/" . $dataAvatar);
			}
		}
	}

}

?>
