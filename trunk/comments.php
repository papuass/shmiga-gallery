<?
header('Content-Type: application/xml');
require 'init.php';

$IMAGE = $_GET['image'];
if (!is_file($GALLERY_ROOT . '/' . $IMAGE)) {
	die('<error>mf</error>');
}

$xmlFile = $GALLERY_ROOT . '/.data/' . $IMAGE . '.xml';

if ($_POST['add']) {
	$name = str_replace(']]>', ']] >', $_POST['name']);
	setCookie('name', $name, time()+60*60*24*30*500);
	$text = str_replace(']]>', ']] >', $_POST['text']);
	$text = preg_replace('/\s*\n\s*/', "\n", $text);
	$text = preg_replace('/\s\s+/', ' ', $text);
	$date = date('Y.m.d H:i:s');

	if (!file_exists($xmlFile)) {
		$dir = dirname($xmlFile);
		if (!file_exists($dir)) {
			mkdir($dir);
			@chmod($dir, DIRS_CHMOD);
			@chgrp($dir, DIRS_CHGRP);
		}

		$data = <<<EOXML
<?xml version="1.0" encoding="UTF-8"?>
<comments>
	<comment date="$date">
		<name><![CDATA[$name]]></name>
		<text><![CDATA[$text]]></text>
	</comment>
</comments>
EOXML;

		$fp = fopen($xmlFile, 'w');
		fwrite($fp, $data);
		fclose($fp);
	} else {

		class Parser {
			var $file;
			var $data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

			function Parser($file) {
				$this->file = $file;

				$parser = xml_parser_create();
				xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
				xml_set_object($parser, $this);
				xml_set_element_handler($parser, "startElement", "endElement");
				xml_set_character_data_handler($parser, "characterData");

				if (!($fp = fopen($this->file, "r"))) {
					die("could not open XML input");
				}

				while ($data = fread($fp, 4096)) {
					if (!xml_parse($parser, $data, feof($fp))) {
						die(sprintf("XML error: %s at line %d",
									xml_error_string(xml_get_error_code($xml_parser)),
									xml_get_current_line_number($xml_parser)));
					}
				}
				xml_parser_free($parser);
			}
			function startElement($parser, $ename, $attrs) {
				$this->data .= '<'.$ename;
				if ($ename == 'comment') {
					$this->data .= ' date="'.$attrs['date'].'"';
				}
				$this->data .= '>';
				if ($ename == 'name' || $ename == 'text') {
					$this->data .= '<![CDATA[';
				}
			}
			function endElement($parser, $ename) {
				global $name, $text, $date;

				if ($ename == 'name' || $ename == 'text') {
					$this->data .= ']]>';
				} elseif ($ename == 'comments') {// here we append new comment
					$this->data .= <<<EOD
	<comment date="$date">
		<name><![CDATA[$name]]></name>
		<text><![CDATA[$text]]></text>
	</comment>

EOD;
				}

				$this->data .= '</'.$ename.'>';
			}
			function characterData($parser, $cd) {
				$this->data .= $cd;
			}
		}

		$parser = new Parser($xmlFile);
		$fp = fopen($xmlFile, 'w');
		fwrite($fp, $parser->data);
		fclose($fp);
	}
	@chmod($xmlFile, FILES_CHMOD);
	@chgrp($xmlFile, FILES_CHGRP);
}

$cookieName = preg_replace('/[^\d\w]/', '_', $IMAGE);
setCookie($cookieName, time(), time()+60*60*24*30*500, $WEB_GALLERY_ROOT);

if (!is_file($xmlFile)) {
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<comments/>';
} else {
	readfile($xmlFile);
}

?>
