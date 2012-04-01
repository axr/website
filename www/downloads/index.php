<?php
/*
CREATE TABLE  `axr`.`downloads` (
`id` INT NOT NULL ,
`filename` VARCHAR( 40 ) NOT NULL ,
`ip` VARCHAR( 15 ) NOT NULL,
`time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
PRIMARY KEY (  `id` )
) ENGINE = MYISAM
*/
define('filesfolder', '../../downloads');

function sanity($filename) {
	return !preg_match('%(^|/)[.]+/%', $filename);
}

$file = $_GET['file'];
if(!empty($file) && $file != 'index.php') {

	if (sanity($file) && file_exists(filesfolder.$file)) { //add propper check for misusage
		//add mysql connect here
		mysql_query('INSERT INTO downloads (filename,ip) VALUES ("'.mysql_real_escape_string($file).'","'.mysql_real_escape_string($_SERVER["REMOTE_ADDR"]).'")');
		header('Content-Length: '.filesize(filesfolder.$file)); 
		header('Content-Type: application/octet-stream'); 
		readfile(filesfolder.$file);
	} else {
		echo '404';
	}
} else { //very simple listing
	$files = array_filter(glob(filesfolder.'*'),function($var){
		return !is_dir($var);
	});
	foreach($files as $file) {
		$filename = substr($file,3);
		echo "<a href='downloads/$filename'>$filename</a><br/>";
	}
}