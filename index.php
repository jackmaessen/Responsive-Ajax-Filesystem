<?php 
session_start();

//error_reporting(E_ALL);

if(!isset($_SESSION['raflogin'])){
	
	echo "<script>window.location.href = 'login.php'</script>";
	exit();
}

include('settings.php');


/* SET ID */
//$UserID  = hash('sha256', $USERNAME);


/* SET MAIN DIRECTORY */
$dir = $MainFolderName;

if (!is_dir($dir)) {
    mkdir($MainFolderName, 0755, true);
}

// create dir.txt to store actual dir 
if (!file_exists('dir.txt')) {
	$actualdir = fopen("dir.txt", "w") or die("Unable to open file!");	
	fwrite($actualdir, $dir);
	fclose($actualdir);
}

// Read actual dir from file "dir.txt"
$readdir = fopen("dir.txt", "r") or die("Unable to open file!");
$dir = fread($readdir,filesize("dir.txt"));
fclose($readdir);


/* SCANDIR */
// exclude dot, double dot and tmp folder
$files = array_diff( scandir($dir), array(".", "..", "tmp") );

/* FUNCTION NAVIGATEFOLDERS, treeview and anchors on directories */
function navigateFolders($dir){

	// scan files
    $files = array_diff( scandir($dir), array(".", "..", "tmp") );
	   
    // prevent empty ordered elements
    if (count($files) < 1)
        return;
    
    echo '<ul class="ul-navigatefolders">';
    foreach($files as $file){
		if(is_dir($dir.'/'.$file)) {
			$navstring = $dir . '/' . $file;
			preg_match("/$MainFolderName\\/[^\\/]+\\/(.*)/", $navstring, $homedir);						
			?>
		
			<li class="li-navigatefolders"><i class="fas fa-level-up-alt"></i>	
			
			<!--<a href="?dir=<?php //echo $navstring; ?>" class="folderanchor"><?php //echo $file; ?></a>
			<!-------------------------------------->
			
			<form class="rafform navform" method="post" action="">
				<input type="hidden" name="writedir" value="<?php echo $navstring; ?>" />
				<button type="submit" class="diranchor navanchor" name="submit_dir"><?php echo $file; ?></button>												
			</form>
			
			<!-------------------------------------->
			</li>							
			<?php
			
			navigateFolders($dir.'/'.$file);
			
		}
	}
    echo '</ul>';
	
}

/* FUNCTION VIEWFOLDERS, treeview directories */
function viewFolders($dir){
	
	// scan files
    $files = array_diff( scandir($dir), array(".", "..", "tmp") );
	    
    // prevent empty ordered elements
    if (count($files) < 1)
        return;
    
    echo '<ul class="ul-viewfolders">';

    foreach($files as $file){
		if(is_dir($dir.'/'.$file)) {
			$viewstring = $dir . '/' . $file;
			//preg_match("/$MainFolderName\\/[^\\/]+\\/(.*)/", $string, $homedir);						
			?>					
			<li class="li-viewfolders set-value" data-value="<?php echo str_replace('uploads/', '', $viewstring); ?>"><i class="fas fa-level-up-alt"></i><?php echo $file; ?></li>							
			<?php
			
			viewFolders($dir.'/'.$file);
			
		}
	}
    echo '</ul>';
	
}

/* FUNCTION SORT FOLDERS first, then by type, then alphabetically */
usort ($files, create_function ('$a,$b', '
	return	is_dir ($a)
		? (is_dir ($b) ? strnatcasecmp ($a, $b) : -1)
		: (is_dir ($b) ? 1 : (
			strcasecmp (pathinfo ($a, PATHINFO_EXTENSION), pathinfo ($b, PATHINFO_EXTENSION)) == 0
			? strnatcasecmp ($a, $b)
			: strcasecmp (pathinfo ($a, PATHINFO_EXTENSION), pathinfo ($b, PATHINFO_EXTENSION))
		))
	;
'));

 
/* FUNCTION SIZE OF FOLDER */
 function recursive_directory_size($directory, $format=FALSE) {
     $size = 0;
     if(substr($directory,-1) == '/')
     {
         $directory = substr($directory,0,-1);
     }
     if(!file_exists($directory) || !is_dir($directory) || !is_readable($directory))
     {
         return -1;
     }
     if($handle = opendir($directory))
     {
         while(($file = readdir($handle)) !== false)
         {
             $path = $directory.'/'.$file;
             if($file != '.' && $file != '..')
             {
                 if(is_file($path))
                 {
                     $size += filesize($path);
                 }elseif(is_dir($path))
                 {
                     $handlesize = recursive_directory_size($path);
                     if($handlesize >= 0)
                     {
                         $size += $handlesize;
                     }else{
                         return -1;
                     }
                 }
             }
         }
         closedir($handle);
     }
     
     return $size;
}



/* FUNCTION convert bytes to Kb, Mb en Gb */
function sizeFormat($bytes){ 
    $kb = 1024;
    $mb = $kb * 1024;
    $gb = $mb * 1024;
    $tb = $gb * 1024;
    
    if (($bytes >= 0) && ($bytes < $kb)) {
    return $bytes . ' B';
    
    } elseif (($bytes >= $kb) && ($bytes < $mb)) {
    return ceil($bytes / $kb) . ' KB';
    
    } elseif (($bytes >= $mb) && ($bytes < $gb)) {
    return round($bytes / $mb, 2) . ' MB';
    
    } elseif (($bytes >= $gb) && ($bytes < $tb)) {
    return ceil($bytes / $gb) . ' GB';
    
    } elseif ($bytes >= $tb) {
    return ceil($bytes / $tb) . ' TB';
    } else {
    return $bytes . ' B';
    }
}

/* FUNCTION ZIP DIR */
function Zip($source, $destination)
{
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    $source = str_replace('\\', '/', realpath($source));

    if (is_dir($source) === true)
    {
        //$zipfiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
	$zipfiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::LEAVES_ONLY);


        foreach ($zipfiles as $zipfile)
        {
            $zipfile = str_replace('\\', '/', $zipfile);

            // Ignore "." and ".." folders
            if( in_array(substr($zipfile, strrpos($zipfile, '/')+1), array('.', '..')) )
                continue;

            $zipfile = realpath($zipfile);

            if (is_dir($zipfile) === true)
            {
                $zip->addEmptyDir(str_replace($source . '/', '', $zipfile . '/'));
            }
            else if (is_file($zipfile) === true)
            {
                $zip->addFromString(str_replace($source . '/', '', $zipfile), file_get_contents($zipfile));
            }
        }
    }
    else if (is_file($source) === true)
    {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    return $zip->close();
}



// FUNCTION recursive delete directory
function removeDirectory($path) {
 	$files = glob($path . '/*');
	foreach ($files as $file) {
		is_dir($file) ? removeDirectory($file) : unlink($file);
	}
	rmdir($path);
 	return;
}

// FUNCTION copy entire folder
	
function recurse_copy($src_folder,$dest_folder ) {
  $success = true;
  $copydir = opendir($src_folder);
  @mkdir($dest_folder);
  if(!$copydir)
	return false;

  while(false !== ( $folder = readdir($copydir)) ) {
	if (( $folder != '.' ) && ( $folder != '..' )) {
	  if ( is_dir($src_folder . '/' . $folder) ) {
		$success = recurse_copy($src_folder. '/' . $folder,$dest_folder . '/' . $folder) && $success;
	  }
	  else {
		$success = copy($src_folder. '/' . $folder , $dest_folder . '/' . $folder) && $success;
	  }
	}
  }
  closedir($copydir);

  return $success;
}

/* CALCULATE USED STORAGE */
$MainDir = $MainFolderName;
$iterator = new RecursiveIteratorIterator(
new RecursiveDirectoryIterator($MainDir)
);

$SumStorage = 0;
foreach ($iterator as $file) {
	$SumStorage += $file->getSize();
}
$UsedStorage = $SumStorage/$LimitStorage*100;
	
    
 	

/* $_POST ACTIONS */

//////////////////////////////// WRITEDIR ///////////////////////////////////

if( isset($_POST['writedir']) ){
	
	//protect manipulating hidden field
	$pieces = explode("/", $_POST['writedir']);
	$protectedvalue = $pieces[0];			
	if( $MainFolderName != $protectedvalue ) {			    
		include('alerts/forbidden.php');				
		exit;
	} 
	
	// create file "dir.txt" and write actual dir to file
	$write_to_dir = fopen("dir.txt", "w") or die("Unable to open file!");
	$actualdir = $_POST['writedir'];
	
	fwrite($write_to_dir, $actualdir);
	fclose($write_to_dir);

	exit;
}

/////////////////////////////// END WRITEDIR ///////////////////////////////////
   
///////////////////////////// MAKEDIR ////////////////////////////////////////////	 
if( isset($_POST['dirname']) ){
		  
		if($_POST['dirname'] == '')  {
			include('alerts/warning_input.php');
			exit;
		}
		if (!preg_match('/^[a-zA-Z0-9\d-_]+$/', $_POST['dirname'])) {
			include('alerts/warning_input.php');
			exit;
		}

		if(!file_exists($dir . '/' . $_POST['dirname']) ) {
		$DirectoryName = $_POST['dirname']; // only allow a-z, A-Z, 0-9 - and _ for directory name
		mkdir($dir.'/'.$DirectoryName, 0755, true);
		include('alerts/created.php');
		
		exit;
		}
		else {
			include('alerts/folder_exists.php');
			exit;
		}
		
		
} // endif $_POST['dirname'];	
/////////////////////////////////// END MAKEDIR //////////////////////////////////////////////


/////////////////////////////////// DELETE //////////////////////////////////////////////

// Multiple delete (checkboxes)
if( isset($_POST["checkboxes_delete"]) ) { 

	//protect manipulating hidden field
	$pieces = explode("/", $_POST['checkboxes_delete']);
	$protectedvalue = $pieces[0];			
	if( $MainFolderName != $protectedvalue ) {			    
		include('alerts/forbidden.php');				
		exit;
	} 
     
    $checkboxfiles = explode("," , $_POST["checkboxes_delete"]);  
	
	// number of files that are deleted		
	$numberFiles = count($checkboxfiles);
	
    foreach($checkboxfiles as $checkboxfile){
		  
		if(is_dir($checkboxfile)){  
		// if is directory -> remove dir
		removeDirectory($checkboxfile);		
		}
		else {
		// unlink file		
        unlink($checkboxfile);
        }
      
	}
	include('alerts/cbdelete.php');
	exit;
}

/////////////////////////////////// END DELETE //////////////////////////////////////////////


/////////////////////////////////// EXTRACT //////////////////////////////////////////////
if( isset($_POST['extractfile']) ){
		
		    //protect manipulating hidden filed
		    $pieces = explode("/", $_POST['extractfile']);
		    $protectedvalue = $pieces[0];			
		     if( $MainFolderName != $protectedvalue ) {			    
				include('alerts/forbidden.php');				
				exit;
		    }
			
		    $zip = new ZipArchive;
			$extractfiles = [];
		    if ($zip->open($_POST['extractfile']) === TRUE) {
			
				$unzipped = 0; 
				$fails = 0; 
				$total = 0; 
				
				for ($i = 0; $i < $zip->numFiles; $i++) {
					$path_info = pathinfo($zip->getNameIndex($i));
					$ext = $path_info['extension'];
					$total ++; 
					
					if($SumStorage > $LimitStorage) { // if max available storage is reached
						include('alerts/extractfailed.php');
						$fails ++;
						exit;
					}
					
					if(!in_array($ext, $AllowedExts)) { // only files with allowed Exts can be extracted
						//$zip->extractTo(dirname($_POST['extractfile']), $zip->getNameIndex($i)); // extract in the same folder as where the zip file is
						$fails ++;
					}
					elseif( file_exists($dir . '/' . $zip->getNameIndex($i)) ) { 
						
						$tmpStoreFile = $MainFolderName . '/tmp/' . $zip->getNameIndex($i); 
						// make tmp folder if not exists 
						$tmpFolder = $MainFolderName . '/tmp';
						if (!is_dir($tmpFolder)) {
							mkdir($tmpFolder, 0755, true);
						}
						
						$zip->extractTo( $MainFolderName . '/tmp', $zip->getNameIndex($i) ); // extract in tmp folder
						$fails ++;
						include('alerts/file_exists.php');
					}
					else {
						$zip->extractTo(dirname($_POST['extractfile']), $zip->getNameIndex($i)); // extract in the same folder as where the zip file is
						$unzipped ++; 
					}
					
									
				}
				
				include('alerts/extract.php'); // echo 
						
				
			
		    } 
			$zip->close();
			exit;						
						
}
/////////////////////////////////// END EXTRACT //////////////////////////////////////////////

////////////////////////////////// EDIT TXT FILES ///////////////////////////////////////////

if( isset($_POST['editcontent']) ){
	$fn = $_POST['editfile'];
	
	//protect manipulating hidden field
	$pieces = explode("/", $_POST['editfile']);
	$protectedvalue = $pieces[0];			
	 if( $MainFolderName != $protectedvalue ) {			    
		include('alerts/forbidden.php');				
		exit;
	}
	
	$content = stripslashes($_POST['editcontent']);
	$fp = fopen($fn,"w") or die ("Error opening file in write mode!");
	fputs($fp,$content);
	fclose($fp) or die ("Error closing file!");
	include('alerts/edited.php');
}

///////////////////////////////// END EDIT TXT FILES //////////////////////////////////////

/////////////////////////////////// RENAME //////////////////////////////////////////////
if( isset($_POST['new_name']) ){
						  	   
		//protect manipulating hidden field
		$pieces = explode("/", $_POST['old_name']);
		$protectedvalue = $pieces[0];			
		 if( $MainFolderName != $protectedvalue ) {			    
			include('alerts/forbidden.php');				
			exit;
		}
		
		if($_POST['new_name'] == '')  {
			include('alerts/warning_input.php');
			exit;
		}
		// only allow a-z, A-Z,0-9, - and _
		if (!preg_match('/^[a-zA-Z0-9\d-_]+$/', $_POST['new_name'])) {
			include('alerts/warning_input.php');
			exit;
		}
			
				
		$FileExtension = pathinfo($_POST['old_name'], PATHINFO_EXTENSION);
		
		// foldername already exists		
		if(file_exists($dir . '/' . $_POST['new_name']) && $FileExtension == '') {	// if $FileExtension == '' -> must be a directory					
				include('alerts/foldername_exists.php');
				exit;			
		}
		
		// filename already exists
		if(file_exists($dir . '/' . $_POST['new_name'] . '.' . $FileExtension) ) {
			include('alerts/filename_exists.php');
			exit;
		}
		
		
		//rename folder
		if(is_dir($_POST['old_name'])) {
			$NewDirName = $dir . '/' . $_POST['new_name']; 
			rename($_POST['old_name'], $NewDirName);
			include('alerts/renamefolder.php');
					
			exit;						
		}
		
		//rename file
		elseif(!is_dir($_POST['old_name'])) {				
			$NewFileName = $dir . '/' . $_POST['new_name'] . '.' . $FileExtension; 
			rename($_POST['old_name'], $NewFileName);
			include('alerts/renamefile.php'); 
			
			exit;			  
		}
		   		   					
}
/////////////////////////////////// END RENAME //////////////////////////////////////////////

//////////////////////////////////DOWNOAD ZIP & FOLDER ///////////////////////////////////////////////////

if( isset($_POST['downloadzip']) ){
	
		if(file_exists($MainFolderName . '/tmp/archive.zip')){
			$filename = "archive.zip";
			
			// http headers for zip downloads
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"".$filename."\"");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ". filesize($MainFolderName . '/tmp/archive.zip'));
						
			while (ob_get_level()) {
				ob_end_clean();
			}
			@readfile($MainFolderName . '/tmp/archive.zip');
			unlink($MainFolderName . '/tmp/archive.zip'); // unlink archive.zip			
			rmdir($MainFolderName . '/tmp'); // remove tmpdir
			exit;
		
		}
		    			    
}
/////////////////////////////////END DOWNLOAD FOLDER; COMPRESS////////////////////////////////////////////////

///////////////////////////////////// CHECKBOXES DOWNLOAD ///////////////////////////////////////////////////////
// Multiple download (checkboxes)
if(isset($_POST["checkboxes_down"])) { 

	// create a tmp folder for the zip file
	$tmpfolder = $MainFolderName.'/tmp';
	if (!is_dir($tmpfolder)) {
		 mkdir($tmpfolder, 0755, true);
	}

	$checkboxfiles = explode("," , $_POST["checkboxes_down"]); 
    $filename = "archive.zip";
	$filepath = $tmpfolder."/";
			
    foreach($checkboxfiles as $checkboxfile) {	
		if( !is_dir($checkboxfile) && count($checkboxfiles)  < 2 )  { // if selected only 1 checkbox and is not folder						
            include('alerts/downloadsingle.php');			
			exit;
		}
		else {		
			Zip($checkboxfile, $tmpfolder."/archive.zip");			
		}
		
	}
	include('alerts/downloadmultiple.php'); 
	exit;
			
}


//////////////////////////////////////  END CHECKBOXES DOWNLOAD //////////////////////////////////////////////////////

/////////////////////////////////OVERWRITE FILE////////////////////////////////////////////////

// Overwrite Folder & File
if( isset($_POST['overwrite']) ){
		 
	//protect manipulating hidden field
	$pieces = explode("/", $_POST['overwrite']);
	$protectedvalue = $pieces[0];			
	if( $MainFolderName != $protectedvalue ) {			    
		include('alerts/forbidden.php');				
		exit;
	}
	
		 
	$src_file = $_POST['overwrite'];
    $fileName = basename($src_file);
    $new_dest = $_POST['destination'];	
	
				
	/* New path for this file */
	$dest_file = $MainFolderName.'/'. $new_dest . '/' . $fileName;
	
				
	$src_folder = $_POST['overwrite'] . '/';
	$old_basename_src_folder = basename($_POST['overwrite']);
	/* New  path for this folder */
	$dest_folder = $MainFolderName.'/'.$new_dest.'/' . $old_basename_src_folder . '/';
	
	
	/* check available storage before overwriting */
	if($SumStorage > $LimitStorage) { // if max available storage is reached, do not copy			
		include('alerts/copyfailed.php');		
		exit;
	}
				
	// if is directory
	if(is_dir($_POST['overwrite'])) {
		
		$copyfolder = recurse_copy($src_folder,$dest_folder);
		if($copyfolder) {
			if($_POST['move'] == 'true') {
				removeDirectory($src_folder); //  remove src dir
				
			}
			include('alerts/overwrite.php');
			exit;			
		}
		else {
			include('alerts/overwritefailed.php');
			exit;
		}
	}
	// else, must be a file
	else {
		// in case Cancel overwrite, just remove file from tmp
		if($_POST['move'] == 'false') { 
			unlink($src_file); // remove src file
			exit;
		}
		
		$copy = copy( $src_file, $dest_file );
		if($copy) {
			if($_POST['move'] == 'true') { 
		
				unlink($src_file); // remove src file				
				
			}
			
			include('alerts/overwrite.php');
			exit;			
		}
		else {
			include('alerts/overwritefailed.php');
			exit;
		}
	}

	 
	
}
///////////////////////////////// END OVERWRITE FILE ////////////////////////////////////////////////

///////////////////////////////// MULTIPLE COPY AND MOVE FILES; CHECKBOXES ////////////////////////////////////////////////
if( isset($_POST["checkboxes_value"]) && isset($_POST["cbdestination"]) && isset($_POST["buttonvalue"]) )  { 

	$checkboxfiles = explode("," , $_POST["checkboxes_value"]);
	
	// number of files that are being copied or moved		
	$numberFiles = count($checkboxfiles);
			
	$numberFilesExists = 0;
	foreach($checkboxfiles as $checkboxfile) {
			
		$src_file = $checkboxfile;
		$fileName = basename($src_file);
		$new_dest = $_POST['cbdestination'];
		
		// filter input; allow empty input for copying to home
		if (!preg_match('/^[a-zA-Z0-9\d-_\/]+$/', $_POST['cbdestination']) && $_POST['cbdestination'] != '') {
			include('alerts/folder_notexists.php');
			exit;
		}
				
		/* New path for this file */
		$dest_file = $MainFolderName.'/'. $new_dest . '/' . $fileName;		
		
		// count nuber of files that already exists in destination folder
		if(file_exists($dest_file)){
			$numberFilesExists++;
		}
		// exclude checkAll checkbox in $numberFiles
		if($checkboxfile == 'checkAll') {
			$numberFiles = $numberFiles - 1; 
		}
		$numberFilesTransferred = $numberFiles - $numberFilesExists;		 
							
		$src_folder = $checkboxfile . '/';
		$old_basename_src_folder = basename($checkboxfile);
		/* New  path for this folder */
		$dest_folder = $MainFolderName.'/'.$new_dest.'/' . $old_basename_src_folder . '/';
		
		// check available storage before copying 
		if($SumStorage > $LimitStorage) { // if max available storage is reached, do not copy				
			include('alerts/copyfailed.php');			
			exit;
		}
		
		// if destination folder not exists
		if (!file_exists($MainFolderName.'/'.$new_dest)) {
			include('alerts/folder_notexists.php');
			exit;
		}
		
		/* check for overwriting */		
		$allow = $_POST['overwrite'];
		if( $allow == '' && file_exists($dest_file) ) {
			
			if($_POST["buttonvalue"] == 'Move') {
				include('alerts/file_move_exists.php'); 
				
			}
			 else {
				include('alerts/file_copy_exists.php'); 
								
			 }	
						
		}
		else {
				
			// Copy and Move folders and files
			
			// if is directory
			if(is_dir($checkboxfile)) {
				// copy folder
				$copyfolder = recurse_copy($src_folder,$dest_folder);				
				if( $_POST["buttonvalue"] == 'Move') { // if clicked on Move button, remove folder after copy
					removeDirectory($src_file); // remove the original dir
				}
				
			}
			// else, must be a file	-> copy file		
			$copy = copy( $src_file, $dest_file );
			if( $_POST["buttonvalue"] == 'Move') { // if clicked on Move button, unlink file after copy
				unlink($src_file); // remove the original file
			}
		}// end else check overwrite
		
	} // end foreach
			
	if( $copyfolder or $copy) {
		if($_POST["buttonvalue"] == 'Move') {
			include('alerts/cbmoved.php');
			exit;
		}
		else {
			include('alerts/cbcopied.php');
			exit;
		}
		
	}
	else {
		if($_POST["buttonvalue"] == 'Move') {
			include('alerts/cbmovefailed.php');
			exit;
		}
		else {
			include('alerts/cbcopyfailed.php');
			exit;
		}
		exit;
	}
	
			
				
}

///////////////////////////////// END MULTIPLE COPY AND MOVE FILES ////////////////////////////////////////////////


//////////////////////////////// UPLOAD//////////////////////////////////////////////
	
if( isset($_FILES['file']['name']) ) {
	
	
	$extensionName = explode(".", $_FILES["file"]["name"]);	
	$extension = strtolower(end($extensionName));
		
				
	if($SumStorage > $LimitStorage) { // if max available storage is reached
		
		include('alerts/outstorage.php');

		exit;
	}
	elseif( $_FILES['file']['size'] > $MaxUploadSize ) { // if maxupload size is exceeded
			
		include('alerts/toobig.php');

		exit;
		
	}
	elseif(in_array($extension, $AllowedExts) == 0) { // if extension is not allowed
		  		
		include('alerts/extension_notallowed.php');

		exit;
		
	}
	
	else {

		// file is ready to be uploaded	   
		$tmpFilePath = $_FILES['file']['tmp_name']; 		
		$newFilePath = $dir . '/' . $_FILES['file']['name']; 
		
		// check if file already exists
		if(file_exists($dir . '/' . $_FILES['file']['name'])) {
			$tmpStoreFile = $MainFolderName . '/tmp/' . $_FILES['file']['name']; 
			
			// make tmp folder if not exists 
			$tmpFolder = $MainFolderName . '/tmp';
			if (!is_dir($tmpFolder)) {
				mkdir($tmpFolder, 0755, true);
			}
			// upload file to tmp folder				
			move_uploaded_file($tmpFilePath, $tmpStoreFile);
			include('alerts/file_exists.php');
			exit;
			
		}
		
		
		// file to be upload not yet exists, upload file to its directory
		if(move_uploaded_file($tmpFilePath, $newFilePath)) {  
													
			include('alerts/success.php');			   
							
		}
		
	}	

	exit;
		
}// end isset($_FILES['file']['name']; 



/////////////////////////// END UPLOAD//////////////////////////////////////////////

if($_SERVER['REQUEST_METHOD'] == "POST") { 
exit; // prevent loading entire page in the echo
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Responsive Ajax Filemanagement</title>
<!-- LOAD FONT AWESOME ICONS AND JS FOR AJAX  -->

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/raf.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

	

</head>

<body>

<div class="container-fluid">

	<!--  DIV IN WHICH THE ECHOS COME-->
	<div class="row">
		<div class="col">
			<div class="echo"></div>
		</div>
	</div>
				
		   
	<!-- SPINNER DURING UPLOAD; CSS only -->
	
	<div class="uploadspinner" style="display: none;">
	<br />
	
		  <div class="sk-circle1 sk-circle"></div>
		  <div class="sk-circle2 sk-circle"></div>
		  <div class="sk-circle3 sk-circle"></div>
		  <div class="sk-circle4 sk-circle"></div>
		  <div class="sk-circle5 sk-circle"></div>
		  <div class="sk-circle6 sk-circle"></div>
		  <div class="sk-circle7 sk-circle"></div>
		  <div class="sk-circle8 sk-circle"></div>
		  <div class="sk-circle9 sk-circle"></div>
		  <div class="sk-circle10 sk-circle"></div>
		  <div class="sk-circle11 sk-circle"></div>
		  <div class="sk-circle12 sk-circle"></div>
		  
	</div>
   
	<br />
			
		
			
	
	
		<!-- BUTTONS: CREATE FOLDER, UPLOAD, NAVIGATE, SEARCH FIELD, INFO AND LOGOUT -->
	<div class="row row-buttons">
	
		<div class="col-4">		
							
			<!-- ****************************** ANCHOR FOR OPEN MODAL CREATE FOLDER ***************************-->
			<a class="btn  createfolder" href="#createfolder-modal">Create Dir</a>
			
			<!-- MODAL CREATE FOLDER -->
			<div id="createfolder-modal" class="modalDialog modalDialogCreate">
				<div>
					<a href="#close" title="Close" class="closemodal"><i class="fas fa-times"></i></a>
					<br />
					<h4>Create Directory</h4>
			
							 
						<form class="rafform"  action="" method="post">
							<input type="text" class="mkdir form-control" name="dirname"  placeholder="only a-z, A-Z, 0-9, -, _" />
						
							<input type="submit" class="submitmodal createfolder btn " name="mkdir" value="Create" />
						</form>
				</div>
			</div>
			
			 
			 
			<!-- ******************************************  END MODAL CREATE FOLDER ******************************************* -->	
				
			<!-- ****************************************** ANCHOR FOR OPEN MODAL UPLOAD FILES **********************************-->
			<a class="btn  uploadfiles" href="#uploadfiles-modal">Upload</a>
			
			<!-- MODAL UPLOAD FILES -->
			<div id="uploadfiles-modal" class="modalDialog modalDialogUpload">
				<div>
					<a href="#close" title="Close" class="closemodal"><i class="fas fa-times"></i></a>
					<br />
					<h4>Upload Files</h4>
					
					<!-- Info -->
					<?php 
					echo "<b>Allowed File Extensions:</b><br />";
						foreach($AllowedExts as $value){
							echo $value . ' ';
						}
						echo "<br /><b>Max Uploadsize: </b>" . sizeFormat($MaxUploadSize);
					?>
					
					<!-- DROPAREA -->
					<br /><br />
					<div id="drop_file_zone" ondrop="upload_file(event)" ondragover="return false">
					  <div id="drag_upload_file">				  													  				  				    
						<p>DROP FILE(S) HERE</p>
						<p>or</p>
						
						<p><input class="browse btn" type="button" id="browse" value="Browse" onclick="file_explorer();"></p>
						<input type="file" id="selectfile" name="upload" value="" multiple>
						
					  </div>
					</div>
					<!-- END DROPAREA -->
					
						
				</div>
			</div>
		</div> <!-- end col -->
		<!-- ******************************************* END MODAL UPLOAD FILES ****************************************** -->	
		<div class="col-4">
			<!-- SEARCH INPUT-->
			<div class="search-nav">
			<input type="text" class="search form-control" id="search" title = "Live Search Files"  placeholder='Search in dir' />
			
			<!-- NAVIGATE -->
			<a class="btn navigate" href="#navigate-modal">Navigate</a>
			
			<!-- Modal for NAVIGATE lower, part of div table-responsive -->				
			</div>
		</div> <!-- end col -->
		
		
		<!-- LOGOUT BUTTON -->
		<div class="col-4">						
			<a class="logout btn btn-danger float-right" title="Logout" href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
		</div> <!-- end col -->
		
		
	</div> <!-- End row-->
        
		
	<div class="dynamic-content">
	
		<!-- CHECKBOX ACTION BUTTONS AND MODALS-->
		<div class="row">

			<div class="col">

				<div class="cb-buttons hide">					
					<b class="cb-actions-text">Checkbox actions:</b>&nbsp;		
					<a href="#delete" class="cb_delete" title="Delete"><i class="fas fa-trash-alt"></i></a>
					
					<a href="#download" class="cb_down" title="Download"><i class="fas fa-download"></i></a>
					
					<a href="#cb_copy" class="cbcopy-anchor" title="Copy"><i class="fas fa-copy"></i></a>
					
						<!-- MODAL COPY FILE -->
						<div id="cb_copy" class="modalDialog">
							<div>
							<a href="#close" title="Close" class="closemodal"><i class="fas fa-times"></i></a>
							
							<br />
							<h4>Copy Files/Folders</h4>
							Click on one of the folders below to set the destination and press <b>COPY</b> button (Home is empty)<br /><br />
							
							<span class="li-viewfolders set-value" data-value=""><i class="fas fa-home"></i></span>	<!-- Home icon -->	
							<div class="tree">						
							<?php viewFolders($MainDir); ?> <!-- treeview -->

							</div>
							
							<h5>Copy selected files to:</h5>
								<form class="rafform" method="post" action="">
									
									<input type="text" class="set-input cbdestination form-control" name="copyfile-destination" value="" />
								
									<input type="submit" class="cb_copy_move submitmodal rafcopy copyfile btn" value="Copy" />
								</form> 
							</div>
						</div>
					
					<a href="#cb_move" class="cbmove-anchor" title="Move"><i class="fas fa-file-export"></i></a>
					
						<!-- MODAL MOVE FILE -->
						<div id="cb_move" class="modalDialog">
							<div>
							<a href="#close" title="Close" class="closemodal"><i class="fas fa-times"></i></a>
							
							<br />
							<h4>Move Files/Folders</h4>
							Click on one of the folders below to set the destination and press <b>MOVE</b> button (Home is empty)<br /><br />
							<span class="li-viewfolders set-value" data-value=""><i class="fas fa-home"></i></span>	<!-- Home icon -->	
							<div class="tree">						
							<?php viewFolders($MainDir); ?> <!-- treeview -->

							</div>
							
							<h5>Moveselected files to:</h5>
								<form class="rafform" method="post" action="">
									
									<input type="text" class="set-input new_destination form-control" name="cbmovefile-destination" value="" />
								
									<input type="submit" class="cb_copy_move submitmodal rafmove movefile btn" value="Move" />
								</form> 
							</div>
						</div>						
																															
				</div> <!-- end checkbox action buttons -->
			</div><!-- end col -->
		</div><!-- end row -->
	
				
		<div class="calculateStorage">
	   		
			<?php 
				
			 /* Output used storage */
											 
			if($ShowStorage === TRUE) { // show available storage; editable in settings
			 
			 
				if($LimitStorage === FALSE) { // editable in settings						
					echo 'Used<b> '.round($SumStorage/1048576, 2).' </b>MB of Unlimited';													
				}
				elseif(round($UsedStorage, 2) < 100) {
					echo 'Used<b> '.round($SumStorage/1048576, 2).' </b>MB of <b>'.round($LimitStorage/1048576, 2).'</b> MB&nbsp;&nbsp;<i class="fas fa-long-arrow-alt-right"></i>&nbsp;&nbsp;';
					echo '<b>'.round($UsedStorage, 2).' %</b><br />';
				}	
				else {
				// in css; max_exceed is red colored
					echo 'Used<b class="max_exceed"> '.round($SumStorage/1048576, 2).' </b>MB of <b>'.round($LimitStorage/1048576, 2).'</b> MB<br />';
					echo '<b class="max_exceed">'.round($UsedStorage, 2).' %.<br /> You have reached your available storage!</b>';
				
				
					}
				
			?>

			<!-- STORAGE PROGRESSBAR-->
			<div class="progress bg-success">
			  <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $UsedStorage; ?>%" aria-valuenow="<?php echo $UsedStorage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
			</div>
			
		<?php } // end $Show storage === TRUE ?>


		</div><!-- end calculateStorage -->
		
		<!-- HEADER -->
		<div class="header">

			<!-- BREADCRUMBS-->
			<div class="breadcrumb">
			<?php
						
			$crumbs = explode('/', $dir); 
			$path = '';
			
			foreach($crumbs as $crumb){
																	
				$path .= $crumb;					
				?>
					 					 
				<form class="rafform crumbform" method="post" action="">
					<input type="hidden" name="writedir" value="<?php echo $path; ?>" />
					<button type="submit" class="crumbanchor" name="submit_dir"><?php echo str_replace(array("$MainFolderName"),array('<i class="fas fa-home crumbhome"></i>'),$crumb) ?></button>												
				</form>
					 					 
				<?php	 
				$path .= '/'; // dir does not start with a /
					
			}
				
				
			?>
			</div> <!-- end breadcrumb -->
					
									
			<table>
				<tr>
					<td>
						<!-- CHECKALL CHECKBOX -->						
						<input type="checkbox" title="check All" id="checkAll" class="checkAll" value="checkAll">
						<label for="checkAll" class="checkAll"></label>							
					</td>	
						<?php 				
						// Count Folders & Files
						$numberTotal = count(scandir($dir)) -2;	// exclude "./" and "../"
						$numberFolders = count(glob($dir . '/*', GLOB_ONLYDIR));
						// don't count 'tmp' folder in Mainfolder
						if(file_exists($MainFolderName . '/tmp') && $dir ==  $MainFolderName) {
							$numberTotal = $numberTotal - 1;
							$numberFolders = $numberFolders - 1;
						}
						$numberFiles = $numberTotal - $numberFolders;
						?>
					<td class="td-general-info">							
							<?php echo 'Folders: <b>' . $numberFolders . '</b>'; ?>
					</td>	
					<td class="td-general-info">	
							<?php echo 'Files: <b>' . $numberFiles . '</b>'; ?>
					</td>			
					<td class="td-general-info">	
							<?php echo 'Size: <b>' . sizeFormat(recursive_directory_size($dir)) . '</b>'; ?>
					</td>	
					
				</tr> <!-- end general info -->
			</table>
				
			
							
			
		
		</div> <!-- end sticky header -->
		
		<div class="table-responsive">
		
			<table id="table-no-resize" class="raftable table">
			
				<thead>
				
				<tr class="first-row">
					<th class="checkbox header-item"></th>				
					<th class="name header-item"><a id="name" class="filter-link" href="#">Name<i class="fas fa-sort"></i></a></th>	
					<th class="extension header-item"><a id="extension" class="filter-link" href="#">Ext<i class="fas fa-sort"></i></a></th>						
					<th class="modified header-item"><a id="modified" class="filter-link filter-link-number" href="#">Modif<i class="fas fa-sort"></i></a></th>					
					<th class="size header-item"><a id="size" class="filter-link filter-link-number" href="#">Size<i class="fas fa-sort"></i></a></th>
					<th class="share header-item"><a id="share" class="filter-link" href="#">Share<i class="fas fa-sort"></i></a></th>
					<th class="view header-item"><a id="view" class="filter-link" href="#">View<i class="fas fa-sort"></i></a></th>							
					<th class="rename header-item">Ren</th>					
				</tr>
								
				</thead>
				
				<tbody class="table-content">
							   
				<?php
						
				/* 3rd ROW START RENDER THE FILES */
				foreach ($files as $file) {				
												
				$FileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				$ClassExtension = 'raf_'.$FileExtension;				
						
				?>
			   
				<tr class="table-row">
					
					<!-- CHECKBOX-->
					<td class="td-checkbox table-data">						
							<input type="checkbox" class="rafcheckbox checkSingle" id="<?php echo $dir.'/'.$file; ?>" value="<?php echo $dir.'/'.$file; ?>" />	
							<label for="<?php echo $dir.'/'.$file; ?>"></label>							
					</td>
				
					<!-- FILENAME-->		
					<td class="td-filename table-data">
						
						<?php
						if(is_dir($dir.'/'.$file)) {

						?>	
											
						<!--Navigate via anchors-->
					
						<form class="rafform" method="post" action="">
							<input type="hidden" name="writedir" value="<?php echo $dir.'/'.$file; ?>" />
							<button type="submit" class="diranchor tableanchor" name="submit_dir"><i class='fas fa-folder'></i><?php echo $file; ?></button>												
						</form>
																		
						<?php			
															     
						}
						else {			
						echo "<span class='raf_default $ClassExtension'>".' '.$file.'</span';		    			
						}
																	
						?>
					</td>
					
					<!-- FILE EXTENSION -->
					<td class="td-extension table-data">
						<?php 
						
						if(!is_dir($dir.'/'.$file)) {						
							 echo $FileExtension;						
						}
						
						?>
					</td>

					<!-- DATE -->			
					<td class="td-date">
						<!-- raw size for sorting only-->
						<span class="table-data hide"><?php echo filemtime($dir.'/'.$file); ?></span>
						<?php echo date("M d 'y H:i",filemtime($dir.'/'.$file)); ?>
					</td>
															
					<!-- SIZE-->	
					<td class="td-size">
						<!-- raw size for sorting only-->
						<span class="table-data hide"><?php echo filesize($dir . '/' . $file); ?></span>
						<?php						   
						if(!is_dir($dir.'/'.$file)){
							   echo sizeFormat(filesize($dir . '/' . $file));							   						   
						}
						else {
							// calculate size of folder
							echo sizeFormat(recursive_directory_size($dir . '/' . $file));							
						}
						?>
					</td>
					<!-- SHARE -->
					<td class="td-share table-data">
					
						<?php
						if(!is_dir($dir.'/'.$file)) {
						?>
						
						<a class="share" href="#<?php echo '/share-'.$file; ?>"><i class="fas fa-share-alt"></i></a>
									
						<!-- MODAL SHARE FILES -->
						<div id="<?php echo '/share-'.$file; ?>" class="modalDialog modalDialogShare">
						
							<div>
								<a href="#close" title="Close" class="closemodal"><i class="fas fa-times"></i></a>
								<br />
								<h4>Share</h4>
								Name: <b><?php echo basename($dir.'/'.$file); ?></b><br /><br />
								Link below to share (click to select):<br />
								<div class="select-all">								
									<span title="Select me by click"><b><?php echo preg_replace('#^' . preg_quote($_SERVER['DOCUMENT_ROOT']) . '[\\\\/]#', "{$_SERVER['HTTP_HOST']}/", realpath($dir.'/'.$file)) . "\n"; ?></b></span>
								</div>								
								<br />
							</div>	
						</div>
						<?php } ?> <!-- endif is dir -->
					</td>
					<!-- VIEW and EXTRACT -->		
					<td class="td-view table-data">
						<?php
						   
						
						// anchors -> eye icon
						if(!is_dir($dir.'/'.$file)){
												
						   // filter extensions for view icon
						   
							if ( in_array( $FileExtension, $ViewFiles) ) {
								
									// IMAGE FILES
									if(in_array($FileExtension,array('jpg', 'jpeg', 'png', 'gif', 'tif', 'tiff', 'bmp'))) {
									?>									
								
									<a class="view" href="#<?php echo 'image-' . $file; ?>"><i class="fas fa-eye"></i></a>
									
									<!-- MODAL IMAGE FILES -->
									<div id="<?php echo 'image-' . $file; ?>" class="modalDialog">
									
										<div>
											<a href="#close" title="Close" class="closemodal"><i class="fas fa-times"></i></a>
											<br />
											<h4><?php echo basename($dir.'/'.$file); ?></h4>
											<img src="<?php echo $dir.'/'.$file; ?>" title="<?php echo basename($dir.'/'.$file); ?>" style="width: 100%" />
													
											
											<br /><br />
										</div>	
									</div>
								
									<?php  
									}
									// MP3 FILES
									elseif(in_array($FileExtension,array('mp3'))) { 
									?>									
									
									<a class='view mp3' href="#<?php echo 'mp3-' . $file; ?>"><i class="fas fa-eye"></i></a>
									 
									<!-- MODAL MP3 -->						   
									<div id="<?php echo 'mp3-' . $file; ?>" class="modalDialog">
										<div>
											<a href="#close" title="Close" class="closemodal no-icon"><i class="fas fa-times"></i></a>
											<br />
											<h4><?php echo basename($dir . '/' . $file); ?></h4>
											<audio width="320" controls>
											    <source src="<?php echo $dir . "/" . $file; ?>" type="audio/ogg">
											    <source src="<?php echo $dir . "/" . $file; ?>" type="audio/mpeg">
											Your browser does not support the audio element.
											</audio>
										</div>									
									</div>
									<?php
									   
									}
									// MP4 FILES
									elseif(in_array($FileExtension,array('mp4'))) {
									?>									
									
									<a class='view mp4' href="#<?php echo 'mp4-' . $file; ?>"><i class="fas fa-eye"></i></a>
									 
									<!-- MODAL MP4 -->						   
									<div id="<?php echo 'mp4-' . $file; ?>" class="modalDialog">
										<div>
											<a href="#close" title="Close" class="closemodal no-icon"><i class="fas fa-times"></i></a>
											<br />
											<h4><?php echo basename($dir . '/' . $file); ?></h4>
											<video width="320" controls>
											  <source src="<?php echo $dir . "/" . $file; ?>" type="video/ogg">
											  <source src="<?php echo $dir . "/" . $file; ?>" type="video/mp4">
											Your browser does not support the audio element.
											</video>
										</div>									
									</div>
										
									<?php	
									}
										
									else {
									?>
									<!-- other files; open new window -->																			
									<a class="view" href="<?php echo  $dir . "/" . $file; ?> " target="_blank"><i class="fas fa-eye"></i></a>
									 <?php 
									}
								
							} // end Viewfiles 
							
							// EDIT FILES							
							elseif( in_array($FileExtension, $EditFiles) ) {
							?>
							
							<a class='view edit' href="#<?php echo 'edit-' . $file; ?>"><i class="fas fa-file-signature"></i></a>
									 
							<!-- MODAL EDIT -->						   
							<div id="<?php echo 'edit-' . $file; ?>" class="modalDialog">
								<div>
									<a href="#close" title="Close" class="closemodal no-icon"><i class="fas fa-times"></i></a>
									<br />
									<h4><?php echo basename($dir . '/' . $file); ?></h4>
									<form class="rafform" method="post" action="">
										<input type="hidden" name="editfile" value="<?php echo $dir . '/' . $file; ?>" />
										<textarea name="editcontent">
											<?php
													//echo file_get_contents($dir . '/' . $file); // get the contents, and echo it out.
													readfile($dir . '/' . $file);
											?> 
										</textarea>
										<br /><br />
										<input type="submit" class="submitmodal edit btn " value="Update" />
									</form>
								</div>										
							</div>
							
							<?php
							}
							
							// ZIP FILES -> extract icon in View column
							elseif(in_array($FileExtension,array('zip'))) {
							?>
							
							<a class="view" href="#<?php echo 'zip-' . $file; ?>"><i class="fas fa-file-archive"></i></a>
									
									<!-- MODAL ZIP FILES -->
									<div id="<?php echo 'zip-' . $file; ?>" class="modalDialog">
									
										<div>
											<a href="#close" title="Close" class="closemodal"><i class="fas fa-times"></i></a>
											<br />
											<h4>Content of <b><?php echo basename($dir . '/' . $file); ?></b>:</h4>
											<br />
											<?php
											    // show content of .zip file
												$za = new ZipArchive(); 

												$za->open($dir . '/' . $file); 
                                                echo '<div class="list-zipfiles">';
												for( $i = 0; $i < $za->numFiles; $i++ ){ 
													$stat = $za->statIndex( $i ); 
													echo '<ul><li class="zipfiles">' .  basename( $stat['name'] ) . '</li></ul>';
												}
												echo '</div>';												
											
											?>
											<br />
											Do you want to extract <b><?php echo basename($dir . '/' . $file); ?></b> to 
											<?php
											if(basename($dir) == $MainFolderName) {
												echo '<b>Home</b>?';
											}
											else {
												echo '<b>' . basename($dir) . '</b>?';
											}
											 ?>
											 <br /><br />
											<form class="rafform" method="post" action="">
												<input type="hidden" name="extractfile" value="<?php echo $dir.'/'.$file; ?>" />
												<input type="submit" class="submitmodal rafextract btn" name="extract" value="Yes" />												
											</form>
											<a href="#close" title="Close" class="closemodal btn btn-danger btn-cancel">Cancel</a>
												
											</form>
																	
											
											
										</div>	
									</div>	<!-- end Modal -->					
							
							<?php			        
							}
							
						} // endif is dir
						?>
						
					</td>
																															
					<!-- RENAME -->		
					<td class="td-rename table-data">					
						<a class="renamefile" href="#<?php echo 'rename-' . $file; ?>"><i class="fas fa-edit"></i></a>	

						<!-- MODAL RENAME -->	
						<div id="<?php echo 'rename-' . $file; ?>" class="modalDialog">
							<div>
								<a href="#close" title="Close" class="closemodal"><i class="fas fa-times"></i></a>
								<br />
								<h4>Rename File/Folder</h4>
								Current name: <b><?php echo basename($dir.'/'.$file); ?></b><br />
								<form class="rafform" method="post" action="">
									<input type="hidden" class="old_name" name="old_name" value="<?php echo $dir . '/' . $file; ?>" /> 										
									<input  type="text" class="new_name form-control" name="new_name" placeholder="only a-z, A-Z, 0-9, -, _" value="" />
									<input type="submit" class="submitmodal rename btn " value="Rename" />     					
								</form>
								
							</div>	
						</div>
						
					</td>
																		
				</tr>
																	   			  
				<?php
								
				} // endif foreach
				?>
				</tbody>
			</table><!-- end table -->
			

			<!-- MODAL NAVIGATE; dynamically reloaded, so must be part of div table-responsive-->
			<div id="navigate-modal" class="modalDialog">
				<div>
					<a href="#close" title="Close" class="closemodal"><i class="fas fa-times"></i></a>
					<br />
					<h4>Click on Directory to navigate</h4>
					<br />
					
					
					<form class="rafform navhome" method="post" action="">
						<input type="hidden" name="writedir" value="<?php echo $MainDir; ?>" />
						<button type="submit" class="diranchor" name="submit_dir"><i class="fas fa-home"></i></button>												
					</form>
					
					
					
					<div class="tree-navigation">						
					<?php navigateFolders($MainDir); ?> <!-- navigate -->
					</div>
				</div>
			</div>
			
			
			
	
		</div> <!-- end table responsive-->
	
    </div> <!-- end div dynamic-content --> 

	
    <script>
	
		/* AJAX for Upload */
		var fileobj;
	  
	    function upload_file(e) {
		    e.preventDefault();
			for (var i=0; i< e.dataTransfer.files.length;i++){ // multiple files uploading
			  fileobj = e.dataTransfer.files[i];
			  ajax_file_upload(fileobj);
			}
        }
	 
	    function file_explorer() {
			
			document.getElementById('selectfile').click();
		    document.getElementById('selectfile').onchange = function() {
				for (var i=0; i< this.files.length;i++){ // multiple files uploading
				  fileobj = document.getElementById('selectfile').files[i];
				  ajax_file_upload(fileobj);
				}
		    };
		
        }
			  
	
	    function ajax_file_upload(file_obj) {
			if(file_obj != undefined) {
				var form_data = new FormData();                  
				form_data.append('file', file_obj);
				$.ajax({
					
					type: 'POST',
					url: '',
					cache: false,
					contentType: false,
					processData: false,
					data: form_data,
					
					beforeSend: function () {
                        $(".uploadspinner").show();
						window.location.hash='close'; /* show spinner and disappear upload modal */
                    },
								
					success:function(data) {
						$.getScript( "https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js" ); // load core file for closing echo
						$('.echo').append(data);
						$('#selectfile').val('');
						  
						$('.dynamic-content').load('index.php .dynamic-content > *');
												  
						$('.uploadspinner').hide();						
						$('.echomessage').delay(5000).fadeOut(500);
												
					   
					}
																							
				});
			}
	    }
	
	
		
	
	
		/* AJAX for forms */
		$(document).on('submit' , ".rafform" , (function(e) {
			
			e.preventDefault();
			
			$.ajax({
				url: "",
				type: "post",
				data:  new FormData(this),
				cache: false,				
				contentType: false,
				processData: false,				
				beforeSend: function () {
   					window.location.hash='close'; /* show spinner and disappear upload modal */
                },
				success: function(data) {
										
					$.getScript( "https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js" );	// load jquery core file for closing echo	
									
					$('.echo').append(data);
					
					$('.dynamic-content').load('index.php .dynamic-content > *');
											
					$('.uploadspinner').hide();					
					$('.echomessage').delay(5000).fadeOut(500);
									  
				},
							
		   });
		   
		}));
		
		/* AJAX for download */		
		 $('.downloadfile, .downloadzip').click(function(){
		   $.ajax({
			 url: '',
			 type: 'post',			 
			 success: function(response){
			   // no response needed, .zip file already downloaded
				$('.dynamic-content').load('index.php .dynamic-content > *');
				$('.echomessage').delay(5000).fadeOut(500);				
			 }
		   });
		 });
		 
		 ///////////////////////////////////////////////////////
		
		//  AJAX for Checkbox download		
		$(document).on('click' , '.cb_down' , function() { 	
	
           var checkboxes_down = []; 
						
           $('.rafcheckbox').each(function() {   
				if(this.checked) {				
                     checkboxes_down.push($(this).val()); 
				     					 
                }  
           });  
           checkboxes_down = checkboxes_down.toString(); 
		   		   
           $.ajax({  
                url:"", 								
                method:"POST",  
				
                data:{ checkboxes_down:checkboxes_down },  
                success:function(response){ 														
					$.getScript( "https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js" );	// load jquery core file for closing echo
                    $('.echo').html(response); 	
					$('.row-buttons').removeClass('down-50');
					$('.dynamic-content').load('index.php .dynamic-content > *');
                    $('.echomessage').delay(5000).fadeOut(500);					
                } 
			
           });  
		});
		
		//////////////////////////////////////////////////////
		
		/////////////////////////////////////
		
		//  AJAX for Checkbox delete		
		$(document).on('click' , '.cb_delete' , function() { 		
           var checkboxes_delete = []; 
						
           $('.rafcheckbox').each(function() {   
				if(this.checked) {				
                     checkboxes_delete.push($(this).val()); 
				     					 
                }  
           });  
           checkboxes_delete = checkboxes_delete.toString(); 
		   		   
           $.ajax({  
                url:"",  
                method:"POST",  
                data:{ checkboxes_delete:checkboxes_delete },  
                success:function(data){  
					$.getScript( "https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js" );	// load jquery core file for closing echo
                    $('.echo').html(data);  
					$('.row-buttons').removeClass('down-50');
					$('.dynamic-content').load('index.php .dynamic-content > *');
					$('.echomessage').delay(5000).fadeOut(500);
                }  
           });  
		});
		
		
		
		// AJAX for Checkbox copy and move		
		$(document).on('click' , '.cb_copy_move' , function() { 		
            var checkboxes_value = []; // getting value of checkboxes
			var cbdestination = $(".cbdestination").val(); //getting value of input field; destination
			var buttonvalue = $(this).val(); //getting value of chosen button; copy or move 
            $('.rafcheckbox').each(function() {                 
				if(this.checked) {				
                     checkboxes_value.push($(this).val()); 
				     					 
                }  
            });  
            checkboxes_value = checkboxes_value.toString(); 
		   		   
            $.ajax({  
                url:"",  
                method:"POST",  
                data:{ 
						checkboxes_value:checkboxes_value , 
						cbdestination:cbdestination , 
						buttonvalue:buttonvalue						
					},  
                success:function(data){
					$.getScript( "https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js" );	// load jquery core file for closing echo					
                    $('.echo').html(data);  
					$('.row-buttons').removeClass('down-50');
					$('.dynamic-content').load('index.php .dynamic-content > *');
					$('.echomessage').delay(5000).fadeOut(500);
                }  
           });  
		});
		
		
		
		
		
		////////////////////////////////////
		
		// checkbox check all 
		$(document).on('change' , '.checkAll' , function() {			 
		  $('input:checkbox').not(this).prop('checked', this.checked);
		  $('.cb-buttons').toggleClass('hide', $('.rafcheckbox:checked').length < 1);
		  $('.row-buttons').toggleClass('down-50', $('.rafcheckbox:checked').length > 0);
		 });
		
		// show/hide action buttons for checkboxes
		$(document).on('change','.rafcheckbox',function() {
		  $('.cb-buttons').toggleClass('hide', $('.rafcheckbox:checked').length < 1);
		  $('.row-buttons').toggleClass('down-50', $('.rafcheckbox:checked').length > 0);
		  
		});	
		
		// close modal when submit
		$('.submitmodal').click(function() {		  
		  window.location.hash='close';
		});
						
		// grab value from foldertree and put in input when click on dir
		$(document).on('click','.set-value',function() {			
		   var s = this.dataset.value;						   
		   $(".set-input").val(s);
		});
		
		// close echo manually
		$(document).on('click','.closebtn',function() {		
			$(this).closest('.echomessage, .echomessage-exists, .echomessage-download').fadeOut(500);
		});
		
		// Live search files
		$("#search").on("keyup", function() {
			var value = $(this).val();
			if(value!='') {
			   $("table tbody tr").hide();
			}else{
			  $("table tbody tr").show();
		  }
		  $('table tbody tr td:contains("'+value+'")').parent('tr').show(); 
		});
		
		// sticky header
		$(window).scroll(function(){
		  var sticky = $('.sticky-header'),
			  scroll = $(window).scrollTop();

		  if (scroll >= 10) sticky.addClass('fixed');
		  else sticky.removeClass('fixed');
		});
		
		
		// table sort
		var properties = [
			'name',
			'extension',
			'modified',			
			'size',
			'share',
			'view'
			
		];

		$.each( properties, function( i, val ) {
			
			var orderClass = '';
			
			$(document).on('click','#' + val,function(e) {	
					
				e.preventDefault();
				$('.filter-link.filter-link-active').not(this).removeClass('filter-link-active').children('i').show();
				$(this).toggleClass('filter-link-active');
				$('.filter-link').removeClass('asc desc').children('i').show();

				if(orderClass == 'desc' || orderClass == '') {
					$(this).addClass('asc');
					$(this).children('i').hide();					
						orderClass = 'asc';
				} else {
					$(this).addClass('desc');
					$(this).children('i').hide();					
					orderClass = 'desc';
				}

				var parent = $(this).closest('.header-item');
					var index = $(".header-item").index(parent);
				var $table = $('.table-content');
				var rows = $table.find('.table-row').get();
				var isSelected = $(this).hasClass('filter-link-active');
				var isNumber = $(this).hasClass('filter-link-number');
					
				rows.sort(function(a, b){

					var x = $(a).find('.table-data').eq(index).text();
						var y = $(b).find('.table-data').eq(index).text();
						
					if(isNumber == true) {
								
						if(isSelected) {
							return x - y;
						} else {
							return y - x;
						}

					} else {
					
						if(isSelected) {		
							if(x < y) return -1;
							if(x > y) return 1;
							return 0;
						} else {
							if(x > y) return -1;
							if(x < y) return 1;
							return 0;
						}
					}
					});

				$.each(rows, function(index,row) {
					$table.append(row);
				});

				return false;
			});

		});
	
					
    </script>
		

</div> <!-- end container -->

</body>
</html>
