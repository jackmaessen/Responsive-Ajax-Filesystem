<div class="alert alert-success echomessage" role="alert">
  <span class="closebtn"><i class="fas fa-times echoclose"></i></span>  
    <?php  
    if($new_dest == '') {
		$new_dest = 'Home';
	}
	echo '<i class="fas fa-check-square"></i><b>' . $numberFilesTransferred  . '</b> files copied to <b>'. basename($new_dest) .'</b>'; 
						
    ?>
</div>



