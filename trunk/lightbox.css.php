<?
header('Content-Type: text/css');
require 'init.php';
?>
#lightbox {
	background-color: #eee;
	padding: 10px;
	border-bottom: 1px solid #666;
	border-right: 1px solid #666;
}
#overlay {
	background-image: url(<?=$WEB_ROOT?>/overlay.png);
}
#lightboxCaption {
	color: #333;
	background-color: #eee;
	font-size: 90%;
	text-align: center;
	border-bottom: 1px solid #666;
	border-right: 1px solid #666;
}
#lightboxIndicator {
	border: 1px solid #fff;
}
#lightboxOverallView {
	background-image: url(<?=$WEB_ROOT?>/overlay.png);
}
* html #lightboxOverallView,
* html #overlay {
	background-color: #000;
	background-image: url(<?=$WEB_ROOT?>/blank.gif);
	filter: Alpha(opacity=50);
}
