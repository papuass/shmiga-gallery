<?
require_once('init.php');

// {{{ class Gallery {}
class Gallery {
// {{{ properties
	// fuck getters, fuck setters, PHP is not a real OOPL anyway!
	var $path;
	var $name;
	var $caption;
	var $images     = array();
	var $documents  = array();
	var $galleries  = array();
	var $vip;

	var $XMLPart;
	var $XMLItem;
	var $XMLObject;

// }}}
// {{{ Gallery($path, $vip)
	function Gallery($path, $vip) {
		$this->path = &$path;
		$this->name = basename($path).'/';

		$this->vip = &$vip;

		$this->parse();
	}

// }}}
// {{{ permitted()
	function permitted($vip) {
		if ($vip == '*') return true;
		$vip = explode(',', $vip);
		return in_array($this->vip, $vip);
	}

// }}}
// {{{ parse()
	function parse() {
		if (is_readable($this->path.'/data.xml') || is_readable($this->path.'/.data/data.xml')) {
			$this->XMLParse();
		} else {
			$this->DIRParse();
		}
	}

// }}}
// {{{ DIRParse()
	function DIRParse() {
		$dir = opendir($this->path);
		$hrens = array();
		while (false !== ($file = readdir($dir))) {
			if (Gallery::isImage($this->path.'/'.$file)) {
				$this->images[] = new Image($this->path, $file);
				$hrens[] = $file;
			} elseif (Gallery::isDocument($this->path.'/'.$file)) {
				$this->documents[] = new Document($file);
			} elseif (Gallery::isGallery($file)) {
				$gal = new SubGallery($file, $this->vip);
				$this->galleries[] = $gal;
			}
		}
		function cmp($a, $b) {
			return strcmp($a->name, $b->name);
		}
		usort($this->images, 'cmp');
		usort($this->documents, 'cmp');
		usort($this->galleries, 'cmp');
	}

// }}}
// {{{ XMLParse()
	function XMLParse() {
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($parser, $this);
		xml_set_element_handler($parser, "XMLStartElement", "XMLEndElement");
		xml_set_character_data_handler($parser, "XMLCharacterData");

		if (!($fp = fopen($this->path.'/.data/data.xml', "r"))) {
			if (!($fp = fopen($this->path.'/data.xml', "r"))) {
				die("Can not open data.xml");
			}
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

// }}}
// {{{ XMLStartElement()
	function XMLStartElement($parser, $name, $attrs) {
		if (!$this->XMLPart) {
			if (in_array($name, array('images', 'documents', 'caption', 'galleries'))) {
				$this->XMLPart = &$name;
			}
		} else {
			switch ($this->XMLPart) {
				case "images":
					if ($name == 'image' && $this->permitted($attrs['vip'])) {
						$this->XMLItem = &$name;
						$this->XMLObject = new Image($this->path, $attrs['name']);
					}
				break;
				case "documents":
					if ($name == 'document' && $this->permitted($attrs['vip'])) {
						$this->XMLItem = &$name;
						$this->XMLObject = new Document($attrs['name']);
						$this->XMLObject->caption = '';
					}
				break;
				case "galleries":
					if ($name == 'gallery' && $this->permitted($attrs['vip'])) {
						$this->XMLItem = &$name;
						$this->XMLObject = new SubGallery($attrs['name'], $this->vip);
						$this->XMLObject->caption = '';
					}
				break;
			}
		}
	}

// }}}
// {{{ XMLEndElement()
	function XMLEndElement($parser, $name) {
		if ($this->XMLPart == $name) {
			$this->XMLPart = null;
		} else {
			switch ($this->XMLPart) {
				case 'images':
					if ($name == 'image' && $this->XMLObject) {
						$this->images[] = $this->XMLObject;
						$this->XMLItem = null;
						$this->XMLObject = null;
					}
				break;
				case 'documents':
					if ($name == 'document' && $this->XMLObject) {
						$this->documents[] = $this->XMLObject;
						$this->XMLItem = null;
						$this->XMLObject = null;
					}
				break;
				case 'galleries':
					if ($name == 'gallery' && $this->XMLObject) {
						$this->galleries[] = $this->XMLObject;
						$this->XMLItem = null;
						$this->XMLObject = null;
					}
				break;
			}
		}
	}

// }}}
// {{{ XMLCharacterData()
	function XMLCharacterData($parser, $data) {
		if ($this->XMLPart == 'caption') {
			$this->caption = $data;
		} else {
			switch ($this->XMLItem) {
				case 'image':
					$this->XMLObject->caption .= $data;
				break;
				case 'document':
					$this->XMLObject->caption .= $data;
				break;
				case 'gallery':
					$this->XMLObject->caption .= $data;
				break;
			}
		}
	}

// }}}
// {{{ Gallery::isImage()
	function isImage($file) {
		if (!is_file($file)) return false;
		$info = pathInfo($file);
		return in_array(strToLower($info['extension']), Array('jpg', 'jpeg', 'jpe', 'png', 'gif' /* Can be unsupported for resizing */));
			//'bmp', 'dib' - less traffic, less problems, and i do not know how to rezise...
	}

// }}}
// {{{ Gallery::isDocument()
	function isDocument($file) {
		if (!is_file($file)) return false;
		$info = pathInfo($file);
		return in_array(strToLower($info['extension']), Array('mpg', 'avi'));
	}

// }}}
// {{{ Gallery::isGallery()
	function isGallery($file) {
		if (!is_dir($file))  return false;
		echo $file[0];
		if ($file[0] == '.') return false;
		return !in_array($f, Array('t'));
	}

// }}}
}

// }}}
// {{{ class Document {}
class Document {
// {{{ properties
	var $name;
	var $caption;

// }}}
// {{{ constructor
	function Document($name) {
		$this->name = &$name;
		$this->caption = $name;
	}

// }}}
}

// }}}
// {{{ class SubGallery {}
class SubGallery {
// {{{ properties
	var $name;
	var $caption;

// }}}
// {{{ constructor
	function SubGallery($name, $vip) {
		$this->name = &$name;
		$this->caption = $name;
		$this->vip = &$vip;
	}

// }}}
}

// }}}
// {{{ class Image {}
class Image {
// {{{ properties
	var $name;
	var $caption;
	var $path;

// }}}
// {{{ constructor
	function Image($path, $name) {
		$this->path = &$path;
		$this->name = &$name;
	}

// }}}
// {{{ hasThumbnail()
	function hasThumbnail($create) {
		if (!file_exists($this->path.'/t/'.$this->name)) {
			if ($create) {
				return $this->createThumbnail($file);
			} else {
				return false;
			}
    }
		return true;
	}

// }}}
// {{{ createThumbnail()
	function createThumbnail() {
			// create thumbnail directory
		if (!file_exists($this->path.'/t')) {
			if (!is_writeable($this->path)) {
				return false;
			}

			if (!mkdir($this->path.'/t')) {
				return false;
			}
			if (defined('DIRS_CHMOD')) {
				chmod($this->path.'/t', DIRS_CHMOD);
			}
			if (defined('DIRS_CHGRP')) {
				chgrp($this->path.'/t', DIRS_CHGRP);
			}
			if (defined('DIRS_CHOWN')) {
				chown($this->path.'/t', DIRS_CHOWN);
			}
		} elseif (!is_writeable($this->path.'/t')) {
			return false;
		}

			// source and target paths
		$source = $this->path.'/'.$this->name;
		$target = $this->path.'/t/'.$this->name;

			// width, height, image at all?
		$size = @getImageSize($source);
		if (!$size) {
			return false;
		}
		$scale = min(MAX_WIDTH / $size[0], MAX_HEIGHT / $size[1]);
		if ($scale >= 1) {
			return copy($source, $target);// copy same image
		}

			// image resource
		$in = '';
			// prefered output type
		$type = IMG_JPG;
			// try to read image
		$in = @imageCreateFromJPEG($source);
		if (!$in && ($in = @imageCreateFromPNG($source))) {
			$type = IMG_PNG;
		}
		if (!$in && ($in = @imageCreateFromGIF($source))) {
			$type = IMG_GIF;
		}
		if (!$in && ($in = @imageCreateFromWBMP($source))) {
			$type = IMG_WBMP;
		}
		if (!$in) {
			if (!($in = file_get_contents($source))) {
				return false;
			}
			$in = @imageCreateFromString($in);
		}
		if (!$in) {// can not read file, hmm actually should never be true
			return false;
		}

			// output type
		$types = imageTypes();
		if (!($type & $types)) {
			if (IMG_JPG & $types)       $type = IMG_JPG;
			else if (IMG_PNG & $types)  $type = IMG_PNG;
			else if (IMG_GIF & $types)  $type = IMG_GIF;
			else if (IMG_WBMP & $types) $type = IMG_WBMP;
			else return false;
		}

			// resize image - create thumbnail
		$w = $size[0] * $scale;
		$h = $size[1] * $scale;
		$out = ImageCreateTrueColor($w, $h);
		imageCopyResampled($out, $in, 0, 0, 0, 0, $w, $h, $size[0], $size[1]);

			// save thumbnail
		imageinterlace($out, 1);
		switch ($type) {
			case IMG_JPG:  imageJPEG($out, $target, 75); break;
			case IMG_PNG:  imagePNG($out, $target);      break;
			case IMG_GIF:  imageGIF($out, $target);      break;
			case IMG_WBMP: imageWBMP($out, $target);     break;
			default: return false;// never get here
		}

		imageDestroy($out);
		imageDestroy($in);

		if (defined('FILES_CHMOD')) {
			chmod($target, FILES_CHMOD);
		}
		if (defined('FILES_CHGRP')) {
			chgrp($target, FILES_CHGRP);
		}
		if (defined('FILES_CHOWN')) {
			chown($target, FILES_CHOWN);
		}
		return true;
	}

// }}}
}

// }}}
?>
