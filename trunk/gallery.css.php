<?
header("Content-Type: text/css");
require 'init.php';
?>

/* Lightbox styles */
#lightbox{
	position: absolute;
	top: 40px;
	left: 0;
	width: 100%;
	z-index: 100;
	text-align: center;
	line-height: 0;
	}

#lightbox a img{ border: none; }

#outerImageContainer{
	position: relative;
	background-color: #fff;
	width: 250px;
	height: 250px;
	margin: 0 auto;
	}

#imageContainer{
	padding: 10px;
	}

#loading{
	position: absolute;
	top: 40%;
	left: 0%;
	height: 25%;
	width: 100%;
	text-align: center;
	line-height: 0;
	}
#hoverNav{
	position: absolute;
	top: 0;
	left: 0;
	height: 100%;
	width: 100%;
	z-index: 10;
	}
#imageContainer>#hoverNav{ left: 0;}
#hoverNav a{ outline: none;}

#prevLink, #nextLink{
	width: 49%;
	height: 100%;
	background: transparent url(<?=$WEB_ROOT?>/blank.gif) no-repeat; /* Trick IE into showing hover */
	display: block;
	}
#prevLink { left: 0; float: left;}
#nextLink { right: 0; float: right;}
#prevLink:hover, #prevLink:visited:hover { background: url(<?=$WEB_ROOT?>/prevlabel.gif) left 15% no-repeat; }
#nextLink:hover, #nextLink:visited:hover { background: url(<?=$WEB_ROOT?>/nextlabel.gif) right 15% no-repeat; }


#imageDataContainer{
	font: 10px Verdana, Helvetica, sans-serif;
	background-color: #fff;
	margin: 0 auto;
	line-height: 1.4em;
	}

#imageData{
	padding:0 10px;
	}
#imageData #imageDetails{ width: 70%; float: left; text-align: left; }	
#imageData #caption{ font-weight: bold;	}
#imageData #numberDisplay{ display: block; clear: left; padding-bottom: 1.0em;	}			
#imageData #bottomNavClose{ width: 66px; float: right;  padding-bottom: 0.7em;	}	
		
#overlay{
	position: absolute;
	top: 0;
	left: 0;
	z-index: 90;
	width: 100%;
	height: 500px;
	background-color: #000;
	filter:alpha(opacity=60);
	-moz-opacity: 0.6;
	opacity: 0.6;
	}

.clearfix:after {
	content: "."; 
	display: block; 
	height: 0; 
	clear: both; 
	visibility: hidden;
	}

* html>body .clearfix {
	display: inline-block; 
	width: 100%;
	}

* html .clearfix {
	/* Hides from IE-mac \*/
	height: 1%;
	/* End hide from IE-mac */
	}	
	
/* end Lightbox styles */

/* Main page */

body {
	background-color: #fff;
	margin: 0;
	text-align: center;
	}

body, td, p { font-family: Verdana, Helvetica, Arial, sans-serif; font-size: 12px; }
a { text-decoration: none; }

h1 {
	font-size: 32px;
	font-weight: normal;
	margin: 15px 0;
	padding: 5px;
	background: #e6db8e;
	color: #fff;
	font-variant: small-caps;
	}

#footer {
	font-weight: normal;
	margin: 15px 0;
	padding: 5px;
	background: #e6db8e;
	color: #A25A1A;
	font-variant: small-caps;
}

.addr {
 color: #A25A1A;
 text-decoration: none;
}
.addr_suns {
 display: none;
}
.addr_domain {
 padding-left: 14px;
 background: url(kuce.gif) no-repeat 0 1px;
}

table {
	border: 8px solid #fff8de;
	margin: 15px auto;
	}

table.images img { 
	border: 4px solid #e6db8e; 
	padding: 2px;
	}

td {
	vertical-align: top;
	padding: 1px 2px 9px 2px;
	}

td:hover > div {
	font-weight: bold;
	background: #e6db8e;
	}

td div {
	padding: 3px;
	text-align: center;
	font-variant: small-caps;
	width: 206px;
	background: #fff8de; 
	margin: 2px 0;
	}

#files, #galleries {
	border-bottom: 4px solid #fff8de;
	margin: 15px 0 0 15px;
	font: 20px Verdana;
	width: 250px;
	font-variant: small-caps;
	letter-spacing: 0.2em; 
	}

#files:first-letter, #galleries:first-letter {color: #e6db8e;}

ul {
	margin: 0 25px;
	padding: 0;
	list-style-type: square;
	}

#other li, #galls li {
 margin: 0 25px;
 color: #e6db8e;
}

ul a, #other li:hover, #galls li:hover { color: #a25a1a;}
ul a:hover {
	letter-spacing: 0.2em;
	font-weight: bold;
	}

/* New comments */
td.newcomments {/* td */}
td.newcomments a {/* link */}
td.newcomments a img {/* image */}
td.newcomments div {
	/* caption div */
	color: #A25A1A;
	}

/* comments view */
#imageCommentsContainer {/* div */}
#imageCommentsLoading {/* div */}
#imageCommentsNone {/* div contains text "Nav komentāru" */}
#imageCommentsList {
	/* ul */
	list-style: none;
	margin: 5px auto;
	}
#imageCommentsList li {
	/* comment element */
	margin: 5px 0;
	padding: 0 0 10px 0;
	background-color: #ffffff;
	text-align: left;
	-moz-border-radius: 0.6em;
	-webkit-border-radius: 0.6em;
	border-radius: 0.6em;
	}
#imageCommentsList li div.lohs {
	/* contains name and date spans */
	background-color: #333333;
	color: #fff;
	padding: 10px 0;
	border: 2px solid #fff;
	-moz-border-radius: 0.6em;
	-webkit-border-radius: 0.6em;
	border-radius: 0.6em;
	}
#imageCommentsList li div.lohs span.name {
	/* name */
	margin: 5px;
	}
#imageCommentsList li div.lohs span.date {
	/* date */
	font-size: 0.8em;
	}
#imageCommentsList li div.text {
	/* text */
	line-height: normal;
	}
#imageCommentsList li div.text p {
	/* text */
	margin: 3px;
	}

#imageCommentsForm {
	/* div */
	margin: 0 auto;
	background-color: #ffffff;
	padding: 0 0 10px 0;
	-moz-border-radius: 0.6em;
	-webkit-border-radius: 0.6em;
	border-radius: 0.6em;
	}
#imageCommentsForm p {
	margin: 0;
	padding: 9px 0 0 0;
}
#imageCommentsNameLabel, #imageCommentsTextLabel {
	/* label */
	color: #333333;
	display:block;
	float:left;
	padding: 5px 0 0 0;
	width:20%;
	text-align: right;
	line-height: normal;
	}
#imageCommentsName, #imageCommentsText {
	border: 1px solid #333333;
	width: 70%;
	display:inline;
	}
#imageCommentsText {
	height: 50px;
	}
#imageCommentsSubmit {
	/* input submit */
	color: #ffffff;
	background-color: #333333;
	border: none;
	}