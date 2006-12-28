<?
#define('FILES_CHOWN', '');
#define('DIRS_CHOWN',  '');
define('FILES_CHGRP', 'www-japets');
define('DIRS_CHGRP',  'www-japets');
define('FILES_CHMOD', 0660);
define('DIRS_CHMOD',  0770);

define('MAX_WIDTH',   200);
define('MAX_HEIGHT',  150);

define('ROWS',        4);

// Google Analytics (http://www.google.com/analytics/) account ID:
//define('UACCT',      'UA-609379-1');

$GALLERY = $_GET['gallery'];

$GALLERY_ROOT = realpath(dirname(__FILE__).'/'.$GALLERY);

$WEB_ROOT = dirname($_SERVER['PHP_SELF']);
$WEB_GALLERY_ROOT = $WEB_ROOT . '/' . $GALLERY;
if (strlen($GALLERY_ROOT) <= strlen(dirname(__FILE__)) || !is_dir($GALLERY_ROOT) || !is_readable($GALLERY_ROOT)) {
	$GALLERY = false;
}

?>
