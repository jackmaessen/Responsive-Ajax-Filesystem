<?php 

/////////////////////////////////////////////* EDIT SETTINGS BELOW *//////////////////////////////////////
$USERNAME = 'user1'; // logging in: change 'user1' to your own needs
$PASSWORD = 'password1'; // logging in: change 'password1' to your own needs
$MainFolderName = 'uploads'; // set the name of the main folder where alle the files are stored e.g. 'uploads'
$ShowStorage = TRUE; // set to FALSE if you do not want to show available storage in text and progress
$LimitStorage = 20971520; // set the limit of storage you want for the users available; e.g 20971520 is 20MB. Set to FALSE if unlimited

$MaxUploadSize = 5242880; // set your max upload filesize; set is 5 MB

$AllowedExts = array( // set allowed extensions
"gif", 
"jpeg", 
"jpg", 
"png", 
"tif", 
"tiff", 
"bmp",
"ico", 
"zip", 
"rar", 
"js", 
"css", 
"txt", 
"less", 
"pdf", 
"mp3",
"mp4", 
"html", 
"doc", 
"docx", 
"xls", 
"xlsx",
"json"

); 
$ViewFiles =  array( // edit this array for the files you want to appear a view icon
'jpg', 
'jpeg', 
'png', 
'gif', 
'tif', 
'tiff', 
'bmp', 
'pdf', 
'mp3', 
'mp4'
); 

$EditFiles =  array( // edit this array for the files you want to appear a view icon
'txt',  
'js', 
'json', 
'html', 
'htm', 
'xml', 
'css', 
'scss' 
);

date_default_timezone_set('Europe/Amsterdam'); // set this to your own timezone
setlocale(LC_ALL,'en_US.UTF-8');
////////////////////////////////////////////* END EDIT *//////////////////////////////////////////////////

?>