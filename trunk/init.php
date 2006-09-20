<?
define('FILES_CHGRP', 'www-japets');
define('DIRS_CHGRP', 'www-japets');
define('FILES_CHMOD', 0660);
define('DIRS_CHMOD',  0770);

define('ROWS',        4);

// comment this line, if no google analytics
define('UACCT',      'UA-609379-1');

$GALLERY = $_GET['gallery'];

$GALLERY_ROOT = realpath(dirname(__FILE__).'/'.$GALLERY);

$WEB_ROOT = dirname($_SERVER['PHP_SELF']);//XXX
$WEB_GALLERY_ROOT = $WEB_ROOT . '/' . $GALLERY;
if (strlen($GALLERY_ROOT) <= strlen(dirname(__FILE__)) || !is_dir($GALLERY_ROOT) || !is_readable($GALLERY_ROOT)) {
	$GALLERY = false;
}
?>