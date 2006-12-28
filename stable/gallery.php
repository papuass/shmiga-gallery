<?
require_once('init.php');

define('MAX_WIDTH',   200);
define('MAX_HEIGHT',  150);

// {{{ class Element {}
class Element {
// {{{ properties
	var $caption;

// }}}
// {{{ setCaption()
	function setCaption($data) {
		$this->caption = $data;
	}

// }}}
// {{{ getCaption()
	function getCaption() {
		return $this->caption;
	}

// }}}
}

// }}}
// {{{ class Gallery {}
class Gallery extends Element {
// {{{ properties
	var $path;
	var $name;
	var $images;
	var $documents;
	var $galleries;
	var $vip;

	var $XMLPart;
	var $XMLItem;
	var $XMLObject;

// }}}
// {{{ constructor
	function Gallery($path, $vip) {
		$this->path = &$path;
		$this->name = basename($path).'/';

		$this->images = array();
		$this->documents = array();
		$this->galleries = array();

		$this->vip = &$vip;

		$this->parse();
	}

// }}}
// {{{ getName()
	function getName() {
		return $this->name;
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
			} elseif (Gallery::isGallery($this->path.'/'.$file)) {
				$gal = new SubGallery($this->path.'/'.$file.'/', $this->vip);
				$gal->setCaption($gal->getName());
				$this->galleries[] = $gal;
			}
		}
		function cmp($a, $b) {
			return strcmp($a->getName(), $b->getName());
		}
		usort($this->images, "cmp");
//array_multisort($hrens, SORT_ASC, SORT_STRING, $this->images);
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
				die("Can not open XML input file");
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
					}
				break;
				case "galleries":
					if ($name == 'gallery' && $this->permitted($attrs['vip'])) {
						$this->XMLItem = &$name;
						$this->XMLObject = new SubGallery($attrs['name'], $this->vip);
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
			$this->setCaption($data);
		} else {
			switch ($this->XMLItem) {
				case 'image':
					$this->XMLObject->setCaption($data);
				break;
				case 'document':
					$this->XMLObject->setCaption($data);
				break;
				case 'gallery':
					$this->XMLObject->setCaption($data);
				break;
			}
		}
	}

// }}}
// {{{ isImage()
	function isImage($file) {
		if (!is_file($file)) return false;
		$info = pathInfo($file);
		return in_array(strToLower($info['extension']), Array('jpg', 'jpeg', 'jpe', 'png', 'gif' /* Can be unsupported for resizing */));//'bmp', 'dib' - less trafic, less problems, and i do not know how to rezise...
	}

// }}}
// {{{ isDocument()
	function isDocument($file) {
		if (!is_file($file)) return false;
		$info = pathInfo($file);
		return in_array(strToLower($info['extension']), Array('mpg'));
	}

// }}}
// {{{ isGallery()
	function isGallery($file) {
		if (!is_dir($file)) return false;
		$f = basename($file);
		return !in_array($f, Array('.', '..', 't'));
	}

// }}}
}

// }}}
// {{{ class Document {}
class Document extends Element {
// {{{ properties
	var $name;

// }}}
// {{{ constructor
	function Document($name) {
		$this->name = $name;
		$this->caption = $name;
	}

// }}}
// {{{ getName()
	function getName() {
		return $this->name;
	}

// }}}
}

// }}}
// {{{ class SubGallery {}
class SubGallery extends Element {
// {{{ properties
	var $name;

// }}}
// {{{ constructor
	function SubGallery($name, $vip) {
		$this->name = $name;
		$this->vip = &$vip;
	}

// }}}
// {{{ getName()
	function getName() {
		return $this->name;
	}

// }}}
}

// }}}
// {{{ class Image {}
class Image extends Document {
// {{{ properties
	var $path;

// }}}
// {{{ constructor
	function Image($path, $name) {
		$this->path = $path;
		$this->name = $name;
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
			if (!is_writeable($this->path)) return false;

			mkdir($this->path.'/t');
			chmod($this->path.'/t', DIRS_CHMOD);
			chgrp($this->path.'/t', DIRS_CHGRP);
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
			if (IMG_PNG & $types)       $type = IMG_PNG;
			else if (IMG_JPG & $types)  $type = IMG_JPG;
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
			case IMG_JPG:  imageJPEG($out, $target, 65); break;
			case IMG_PNG:  imagePNG($out, $target);      break;
			case IMG_GIF:  imageGIF($out, $target);      break;
			case IMG_WBMP: imageWBMP($out, $target);     break;
			default: return false;// never get here
		}

		imageDestroy($out);
		imageDestroy($in);

		chmod($target, FILES_CHMOD);
		chgrp($target, FILES_CHGRP);
		return true;
	}

// }}}
}

// }}}
?>
