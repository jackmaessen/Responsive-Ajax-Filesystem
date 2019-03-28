
<div class="alert alert-warning echomessage-exists" role="alert">
  <span class="closebtn"><i class="fas fa-times echoclose"></i></span>  
    <i class="fas fa-exclamation-circle"></i> File/Folder already exists: <?php echo '<b>' . basename($src_file) . '</b>'; ?><br />
	Do you want to overwrite it?
	<br /><br />
<form class="rafform" method="post" action="">
	
	<input type="hidden" name="overwrite" value="<?php echo $src_file; ?>" />
	<input type="hidden" name="destination" value="<?php echo $new_dest; ?>" />
	<input type="submit" class="closebtn submitmodal rafcopy copyfile btn " name="copy" value="Yes" />
	
</form> 
	<button class="closebtn btn btn-danger btn-cancel">Cancel</button>
	
</div>






