<?php
session_start();
include('settings.php');

$error = "";
if(isset($_POST['username'],$_POST['password'])){
	$user = array(
					"user" => $USERNAME,
					"pass" => $PASSWORD			
			);
	$username = $_POST['username'];
	$pass = $_POST['password'];
	if($username == $user['user'] && $pass == $user['pass']){
		//session_start();
		$_SESSION['raflogin'] = $username;
		header('Location: index.php');
		exit;
	}else{
		echo '<div class="loginecho alert alert-danger" style="margin: 10px auto; width: 360px;">Login failed</div>';
	}
	
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

	
</head>
<body>
					
					<form style="width: 360px; margin: 10px auto;" accept-charset="UTF-8" role="form" method="post" action="login.php">
						<fieldset>
							<div class="form-group">
								<input class="form-control" placeholder="Username" name="username" type="text">
							</div>
							<div class="form-group">
								<input class="form-control" placeholder="Password" name="password" type="password" value="">
							</div>
								<input class="btn btn-lg btn-block" style="background-color: #38abe3; color: #fff" type="submit" value="Login">
						</fieldset>
					</form>	

</body>
</html>
