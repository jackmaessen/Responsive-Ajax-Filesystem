
<div class="alert alert-warning echomessage-exists" role="alert">
  <span class="closebtn"><i class="fas fa-times echoclose"></i></span>  
    <i class="fas fa-exclamation-circle"></i> File already exists: <?php echo '<b>' . basename($tmpStoreFile) . '</b>'; ?><br />
	Do you want to overwrite it? 
	<br /><br />
	<form class="rafform" method="post" action="">
		<input type="hidden" name="overwrite" value="<?php echo $tmpStoreFile; ?>" />
		
		<input type="hidden" name="destination" value="<?php   if($MainFolderName == basename($dir)) {
																	  echo '';
																	}	
																	else {
																		echo basename($dir);
																} 
														?>" />
		<input type="hidden" name="move" value="true" />
		<input type="submit" class="closebtn submitmodal rafmove movefile btn "value="Yes" />
		
	</form> 

	<form class="rafform" method="post" action="">
		<input type="hidden" name="overwrite" value="<?php echo $tmpStoreFile; ?>" />
		
		<input type="hidden" name="destination" value="<?php   if($MainFolderName == basename($dir)) {
																	  echo '';
																	}	
																	else {
																		echo basename($dir);
																} 
														?>" />
		<input type="hidden" name="move" value="false" />
		<input type="submit" class="closebtn submitmodal rafmove btn btn-danger btn-cancel"value="Cancel" />
		
	</form> 	
	
</div>






