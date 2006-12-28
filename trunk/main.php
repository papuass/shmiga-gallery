<?php
require 'gallery.php';
require 'init.php';

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"';
echo ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
echo '<html>';
echo '<head>';
echo ' <title>Gallery</title>';
echo ' <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
echo ' <style type="text/css" media="screen">@import "', $WEB_ROOT, '/lightbox.css.php?gallery=', $GALLERY, '";</style>';
echo ' <script type="text/javascript" src="', $WEB_ROOT, '/spica.js"></script>';
echo ' <script type="text/javascript" src="', $WEB_ROOT, '/lightbox_plus.js.php?gallery=', $GALLERY, '"></script>';
echo '</head>';
echo '<body>';

if ($GALLERY === false) {
	if ($_GET['list']) {
		include 'list.php';
		echo '<ol style="text-align:left;">';
		foreach ($galleries as $value) {
			echo '<li><a href="', $value, '">', $value, '</a></li>';
		}
		echo '</ol>';

	} else {
			echo '<p>Woops, gallery not found!</p>';
	}
} else {
	$gallery = new Gallery($GALLERY_ROOT, $_GET['vip']);

	if ($gallery->caption) {
			echo '<h1>', $gallery->caption, '</h1>';
	}
	echo '<table class="images">';

	for ($i=0, $toi=count($gallery->images); $i<$toi; $i++) {
		$img = $gallery->images[$i];

		if ($i % ROWS == 0) {
			echo "<tr>\n";
		}

		$commentClass = '';
		$cookieName = preg_replace('/[^\d\w]/', '_', $img->name);
		if (filemtime($GALLERY_ROOT . '/.data/' . $img->name . '.xml') > $_COOKIE[$cookieName]) {
			$commentClass = ' class="newcomments"';
		}
		echo "\t<td", $commentClass, '><a href="', $WEB_GALLERY_ROOT, '/', $img->name, '" rel="lightbox[g]">';

		if ($img->hasThumbnail(true)) {
			echo '<img src="', $WEB_GALLERY_ROOT, '/t/', $img->name, '" alt="', $img->name, '" />';
		} else {
			echo '<img src="', $WEB_GALLERY_ROOT, '/', $img->name, '" alt="', $img->name, '" width="', MAX_WIDTH, '" height="', MAX_HEIGHT, '" />';
		}

		echo '</a>';
		if ($img->caption) {
			echo '<div>', $img->caption, '</div>';
		}
		echo "</td>\n";

		if (($i+1) % ROWS == 0) {
			echo "</tr>\n";
				// force flush
			ob_flush();
			flush();
		}
	}
	if ($i % ROWS != 0) {
		for ($i=ROWS-($i%ROWS); $i>0; $i--) {
			echo '<td>&nbsp;</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
	if (count($gallery->documents) > 0) {
		echo '<div id="files">Citi faili</div><ul id="other">';
		for ($i=0, $toi=count($gallery->documents); $i<$toi; $i++) {
			$doc = $gallery->documents[$i];
			echo '<li><a href="', $doc->name, '">', $doc->caption, '</a></li>';
		}
		echo '</ul>';
	}
	if (count($gallery->galleries) > 0) {
		echo '<div id="galleries">Citas galerijas</div><ul id="galls">';
		for ($i=0, $toi=count($gallery->galleries); $i<$toi; $i++) {
			$gal = $gallery->galleries[$i];
			echo '<li><a href="', $gal->name, '">', $gal->caption, '</a></li>';
		}
		echo '</ul>';
	}
}
echo '<div id="footer">&copy; ', spambot('japets', 'miga.lv'), ', ', spambot('papuass', 'enkurs.org'), '<br>Shmiga productions 2003 - '.date(Y).'</div>';

if (defined('UACCT')) {
	echo '<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">';
	echo '</script>';
	echo '<script type="text/javascript">';
	echo '_uacct = "', UACCT, '";';
	echo 'urchinTracker();';
	echo '</script>';
}

echo '</body>';
echo '</html>';

// {{{ spambot()
function spambot() {
	if (func_num_args() == 1) {
		$email = func_get_arg(0);
		$temp = explode('@', $email);
		if (count($temp) != 2) {
			return $email;
		}
		$name = $temp[0];
		$domain = $temp[1];
	} elseif (func_num_args() == 2) {
		$name = func_get_arg(0);
		$domain = func_get_arg(1);
	} else {
		return;// eh, pietruukst exception :)
	}

	return '<a class="addr" href="#"><span class="addr_name">'
	.$name
	.'</span><span class="addr_suns"> et </span><span class="addr_domain">'
	.$domain
	.'</span></a>';
}

// }}}
?>
