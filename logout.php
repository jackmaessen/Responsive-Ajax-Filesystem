<?php 
	session_start();
	unset($_SESSION['raflogin']);
	header("Location: login.php?logout=true");
?>
