<div class="alert alert-success echomessage" role="alert">
  <span class="closebtn"><i class="fas fa-times echoclose"></i></span>  
  <?php 
		if(basename($dir) == $MainFolderName) {
					$dir = 'Home';
		}
		echo '<i class="fas fa-check-square"></i> There are <b>'.$unzipped.'</b> files from <b>'.$total.'</b> extracted in<b> ' . basename($dir) . '</b>!'; ?>
</div>


