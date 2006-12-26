<?
header("Content-Type: text/javascript");

include 'init.php';
?>
// -----------------------------------------------------------------------------------
//
//	Lightbox v2.02
//	by Lokesh Dhakar - http://www.huddletogether.com
//	3/31/06
//
//	For more information on this script, visit:
//	http://huddletogether.com/projects/lightbox2/
//
//	Licensed under the Creative Commons Attribution 2.5 License - http://creativecommons.org/licenses/by/2.5/
//	
//	Credit also due to those who have helped, inspired, and made their code available to the public.
//	Including: Scott Upton(uptonic.com), Peter-Paul Koch(quirksmode.org), Thomas Fuchs(mir.aculo.us), and others.
//
//
// -----------------------------------------------------------------------------------
/*

	Table of Contents
	-----------------
	Configuration
	Global Variables

	Extending Built-in Objects	
	- Object.extend(Element)
	- Array.prototype.empty()

	Lightbox Class Declaration
	- initialize()
	- start()
	- changeImage()
	- resizeImageContainer()
	- showImage()
	- updateDetails()
	- updateNav()
	- enableKeyboardNav()
	- disableKeyboardNav()
	- keyboardAction()
	- preloadNeighborImages()
	- end()
	
	Miscellaneous Functions
	- getPageScroll()
	- getPageSize()
	- showSelectBoxes()
	- hideSelectBoxes()
	- pause()
	- initLightbox()
	
	Function Calls
	- addLoadEvent(initLightbox)
	
*/
// -----------------------------------------------------------------------------------

//
//	Configuration
//
var fileLoadingImage = "<?=$WEB_ROOT?>/loading.gif";		
var fileBottomNavCloseImage = "<?=$WEB_ROOT?>/closelabel.gif";

var resizeSpeed = 8;	// controls the speed of the image resizing (1=slowest and 10=fastest)

var borderSize = 10;	//if you adjust the padding in the CSS, you will need to update this variable

var gPageHeight = 0;

// -----------------------------------------------------------------------------------

//
//	Global Variables
//
var imageArray = new Array;
var activeImage;

if(resizeSpeed > 10){ resizeSpeed = 10;}
if(resizeSpeed < 1){ resizeSpeed = 1;}
resizeDuration = (11 - resizeSpeed) * 0.15;

// -----------------------------------------------------------------------------------

//
//	Additional methods for Element added by SU, Couloir
//	- further additions by Lokesh Dhakar (huddletogether.com)
//
Object.extend(Element, {
	getWidth: function(element) {
	   	element = $(element);
	   	return element.offsetWidth; 
	},
	setWidth: function(element,w) {
	   	element = $(element);
    	element.style.width = w +"px";
	},
	setHeight: function(element,h) {
   		element = $(element);
    	element.style.height = h +"px";
	},
	setTop: function(element,t) {
	   	element = $(element);
    	element.style.top = t +"px";
	},
	setSrc: function(element,src) {
    	element = $(element);
    	element.src = src; 
	},
	setHref: function(element,href) {
    	element = $(element);
    	element.href = href; 
	},
	setInnerHTML: function(element,content) {
		element = $(element);
		element.innerHTML = content;
	}
});

// -----------------------------------------------------------------------------------

Array.prototype.empty = function () {
	for(i = 0; i <= this.length; i++){
		this.shift();
	}
}

// -----------------------------------------------------------------------------------

//
//	Lightbox Class Declaration
//	- initialize()
//	- start()
//	- changeImage()
//	- resizeImageContainer()
//	- showImage()
//	- updateDetails()
//	- updateNav()
//	- enableKeyboardNav()
//	- disableKeyboardNav()
//	- keyboardNavAction()
//	- preloadNeighborImages()
//	- end()
//
//	Structuring of code inspired by Scott Upton (http://www.uptonic.com/)
//
var Lightbox = Class.create();

Lightbox.prototype = {
	
	// initialize()
	// Constructor runs on completion of the DOM loading. Loops through anchor tags looking for 
	// 'lightbox' references and applies onclick events to appropriate links. The 2nd section of
	// the function inserts html at the bottom of the page which is used to display the shadow 
	// overlay and the image container.
	//
	initialize: function() {	
		if (!document.getElementsByTagName){ return; }
		var anchors = document.getElementsByTagName('a');

		// loop through all anchor tags
		for (var i=0; i<anchors.length; i++){
			var anchor = anchors[i];
			
			var relAttribute = String(anchor.getAttribute('rel'));
			
			// use the string.match() method to catch 'lightbox' references in the rel attribute
			if (anchor.getAttribute('href') && (relAttribute.toLowerCase().match('lightbox'))){
				anchor.onclick = function () {myLightbox.start(this); return false;}
			}
		}

		// The rest of this code inserts html at the bottom of the page that looks similar to this:
		//
		//	<div id="overlay"></div>
		//	<div id="lightbox">
		//		<div id="outerImageContainer">
		//			<div id="imageContainer">
		//				<img id="lightboxImage">
		//				<div style="" id="hoverNav">
		//					<a href="#" id="prevLink"></a>
		//					<a href="#" id="nextLink"></a>
		//				</div>
		//				<div id="loading">
		//					<a href="#" id="loadingLink">
		//						<img src="<?=$WEB_ROOT?>/loading.gif">
		//					</a>
		//				</div>
		//			</div>
		//		</div>
		//		<div id="imageDataContainer">
		//			<div id="imageData">
		//				<div id="imageDetails">
		//					<span id="caption"></span>
		//					<span id="numberDisplay"></span>
		//				</div>
		//				<div id="bottomNav">
		//					<a href="#" id="bottomNavClose">
		//						<img src="<?=$WEB_ROOT?>/close.gif">
		//					</a>
		//				</div>
		//			</div>
		//		</div>
		//	</div>


		var objBody = document.getElementsByTagName("body").item(0);
		
		var objOverlay = document.createElement("div");
		objOverlay.setAttribute('id','overlay');
		objOverlay.style.display = 'none';
		objOverlay.onclick = function() { myLightbox.end(); return false; }
		objBody.appendChild(objOverlay);
		
		var objLightbox = document.createElement("div");
		objLightbox.setAttribute('id','lightbox');
		objLightbox.style.display = 'none';
		objBody.appendChild(objLightbox);
	
		var objOuterImageContainer = document.createElement("div");
		objOuterImageContainer.setAttribute('id','outerImageContainer');
		objLightbox.appendChild(objOuterImageContainer);

		var objImageContainer = document.createElement("div");
		objImageContainer.setAttribute('id','imageContainer');
		objOuterImageContainer.appendChild(objImageContainer);
	
		var objLightboxImage = document.createElement("img");
		objLightboxImage.setAttribute('id','lightboxImage');
		objImageContainer.appendChild(objLightboxImage);
	
		var objHoverNav = document.createElement("div");
		objHoverNav.setAttribute('id','hoverNav');
		objImageContainer.appendChild(objHoverNav);
	
		var objPrevLink = document.createElement("a");
		objPrevLink.setAttribute('id','prevLink');
		objPrevLink.setAttribute('href','#');
		objHoverNav.appendChild(objPrevLink);
		
		var objNextLink = document.createElement("a");
		objNextLink.setAttribute('id','nextLink');
		objNextLink.setAttribute('href','#');
		objHoverNav.appendChild(objNextLink);
	
		var objLoading = document.createElement("div");
		objLoading.setAttribute('id','loading');
		objImageContainer.appendChild(objLoading);
	
		var objLoadingLink = document.createElement("a");
		objLoadingLink.setAttribute('id','loadingLink');
		objLoadingLink.setAttribute('href','#');
		objLoadingLink.onclick = function() { myLightbox.end(); return false; }
		objLoading.appendChild(objLoadingLink);
	
		var objLoadingImage = document.createElement("img");
		objLoadingImage.setAttribute('src', fileLoadingImage);
		objLoadingLink.appendChild(objLoadingImage);

		var objImageDataContainer = document.createElement("div");
		objImageDataContainer.setAttribute('id','imageDataContainer');
		objImageDataContainer.className = 'clearfix';
		objLightbox.appendChild(objImageDataContainer);

		var objImageData = document.createElement("div");
		objImageData.setAttribute('id','imageData');
		objImageDataContainer.appendChild(objImageData);
	
		var objImageDetails = document.createElement("div");
		objImageDetails.setAttribute('id','imageDetails');
		objImageData.appendChild(objImageDetails);
	
		var objCaption = document.createElement("span");
		objCaption.setAttribute('id','caption');
		objImageDetails.appendChild(objCaption);
	
		var objNumberDisplay = document.createElement("span");
		objNumberDisplay.setAttribute('id','numberDisplay');
		objImageDetails.appendChild(objNumberDisplay);
		
		var objBottomNav = document.createElement("div");
		objBottomNav.setAttribute('id','bottomNav');
		objImageData.appendChild(objBottomNav);
	
		var objBottomNavCloseLink = document.createElement("a");
		objBottomNavCloseLink.setAttribute('id','bottomNavClose');
		objBottomNavCloseLink.setAttribute('href','#');
		objBottomNavCloseLink.onclick = function() { myLightbox.end(); return false; }
		objBottomNav.appendChild(objBottomNavCloseLink);
	
		var objBottomNavCloseImage = document.createElement("img");
		objBottomNavCloseImage.setAttribute('src', fileBottomNavCloseImage);
		objBottomNavCloseLink.appendChild(objBottomNavCloseImage);

			// !!!comments!!!
		var objImageCommentsContainer = document.createElement("div");
		objImageCommentsContainer.setAttribute("id", "imageCommentsContainer");
		objLightbox.appendChild(objImageCommentsContainer);

		var objImageCommentsLoading = document.createElement("div");
		objImageCommentsLoading.setAttribute("id", "imageCommentsLoading");
		objImageCommentsContainer.appendChild(objImageCommentsLoading);

		var objImageCommentsNone = document.createElement("div");
		objImageCommentsNone.setAttribute("id", "imageCommentsNone");
		objImageCommentsNone.appendChild(document.createTextNode("Nav komentāru"));
		objImageCommentsContainer.appendChild(objImageCommentsNone);

		var objImageCommentsList = document.createElement("ul");
		objImageCommentsList.setAttribute("id", "imageCommentsList");
		objImageCommentsContainer.appendChild(objImageCommentsList);

		var objImageCommentsForm = document.createElement("div");
		objImageCommentsForm.setAttribute("id", "imageCommentsForm");
		objImageCommentsContainer.appendChild(objImageCommentsForm);

		var objImageCommentsNameLabel = document.createElement("label");
		objImageCommentsNameLabel.setAttribute("id", "imageCommentsNameLabel");
		objImageCommentsNameLabel.setAttribute("for", "imageCommentsName");
		objImageCommentsNameLabel.appendChild(document.createTextNode("Vārds:"));
		objImageCommentsForm.appendChild(objImageCommentsNameLabel);

		var objImageCommentsName = document.createElement("input");
		objImageCommentsName.setAttribute("id", "imageCommentsName");
		objImageCommentsName.setAttribute("type", "text");
<?
		if (isset($_COOKIE['name'])) {
			echo '		objImageCommentsName.value = "', str_replace('"', '\\"', $_COOKIE['name']), '";';
		}
?>
		objImageCommentsName.onkeydown = function(ev) {
			if (ev == null) { // ie
				event.cancelBubble = true;
		  } else {
				ev.stopPropagation();
			}
		}; 
		var objImageCommentsNameWrapper = document.createElement("p");
		objImageCommentsNameWrapper.appendChild(objImageCommentsNameLabel);
		objImageCommentsNameWrapper.appendChild(objImageCommentsName);
		objImageCommentsForm.appendChild(objImageCommentsNameWrapper);

		var objImageCommentsTextLabel = document.createElement("label");
		objImageCommentsTextLabel.setAttribute("id", "imageCommentsTextLabel");
		objImageCommentsTextLabel.setAttribute("for", "imageCommentsText");
		objImageCommentsTextLabel.appendChild(document.createTextNode("Komentārs:"));

		var objImageCommentsText = document.createElement("textarea");
		objImageCommentsText.setAttribute("id", "imageCommentsText");
		objImageCommentsText.onkeydown = function(ev) {
			if (ev == null) { // ie
				event.cancelBubble = true;
		  } else {
				ev.stopPropagation();
			}
		};

		var objImageCommentsTextWrapper = document.createElement("p");
		objImageCommentsTextWrapper.appendChild(objImageCommentsTextLabel);
		objImageCommentsTextWrapper.appendChild(objImageCommentsText);
		objImageCommentsForm.appendChild(objImageCommentsTextWrapper);

		var objImageCommentsSubmit = document.createElement("input");
		objImageCommentsSubmit.setAttribute("id", "imageCommentsSubmit");
		objImageCommentsSubmit.setAttribute("type", "button");
		objImageCommentsSubmit.setAttribute("value", "Pievienot");
		objImageCommentsSubmit.onclick = function() { myLightbox.submitComment();  }

		var objImageCommentsSubmitWrapper = document.createElement("p");
		objImageCommentsSubmitWrapper.appendChild(objImageCommentsSubmit);
		objImageCommentsForm.appendChild(objImageCommentsSubmitWrapper);


//<div id="imageCommentsContainer">
//  <div id="imageCommentsLoading"></div>
//  <div id="imageCommentsNone">Nav komentāru</div>
//  <ul id="imageCommentsList">
////    <li>
////		  <div class="lohs"><span class="name">Papuass</span><span class="date">2006.06.06 06:06:06</div>
////			<div class="text"><p>Nu ja tā paskatās, es neko nerubī :)</p><p>Patiesībā jau varētu cenas dēļ to pašu 350D paņemt.</p></div>
////		</li>
//	</ul>
//  <div id="imageCommentsForm">
//	<label id="imageCommentsNameLabel" for="imageCommentsName">Vārds:</label><input type="text" id="imageCommentsName"/>
//		<label id="imageCommentsTextLabel" for="imageCommentsText">Komentārs:</label><textarea id="imageCommentsText"></textarea>
//		<input type="button" id="imageCommentsSubmit" value="Pievienot"/>
//  </div>
//</div>

		var pageSizeArray = getPageSize();
		gPageHeight = pageSizeArray[1];
	},

	//
	//	start()
	//	Display overlay and lightbox. If image is part of a set, add siblings to imageArray.
	//
	start: function(imageLink) {	

		hideSelectBoxes();

		// stretch overlay to fill page and fade in
		var arrayPageSize = getPageSize();
		Element.setHeight('overlay', arrayPageSize[1]);
		new Effect.Appear('overlay', { duration: 0.2, from: 0.0, to: 0.8 });

		imageArray = [];
		imageNum = 0;		

		var imageRel = imageLink.getAttribute('rel');

		// if image is NOT part of a set..
		if (imageRel == 'lightbox') {
			// add single image to imageArray
			imageArray.push(imageLink);
		} else {
		// if image is part of a set..

			if (!document.getElementsByTagName){ return; }
			var anchors = document.getElementsByTagName('a');

			// loop through anchors, find other images in set, and add them to imageArray
			for (var i=0; i<anchors.length; i++){
				var anchor = anchors[i];
				if (anchor.href && anchor.getAttribute('rel') == imageRel){
					imageArray.push(anchor);
				}
			}
			while (imageArray[imageNum].href != imageLink.href) imageNum++;
		}

		// calculate top offset for the lightbox and display 
		var arrayPageSize = getPageSize();
		var arrayPageScroll = getPageScroll();
		var lightboxTop = arrayPageScroll[1] + (arrayPageSize[3] / 15);

		Element.setTop('lightbox', lightboxTop);
		Element.show('lightbox');
		
		this.changeImage(imageNum);
	},

	//
	//  resizeOverlay()
	//	resize overlay 
	//

	resizeOverlay: function() {
		var lightboxHeight = parseInt(getStyle($('lightbox'),'height'));
		if (getStyle($('lightbox'),'height') == 'auto') { 
			// die IE!!
			// dirty hack - we get the position of submit button
			lightboxHeight = findPos($('imageCommentsSubmit'))[1];
		}
		// make sure it fills all screen
		var pageSizeArray = getPageSize();
		if (lightboxHeight < pageSizeArray[3]) {
			lightboxHeight = pageSizeArray[3];
		} else {
			lightboxHeight += parseInt(getStyle($('lightbox'),'top'));
			lightboxHeight += 50;
		}
		if (lightboxHeight < gPageHeight) {
			lightboxHeight = gPageHeight;
		}

		$('overlay').style.height = lightboxHeight + 'px';
	},

	//
	//  resizeComments()
	//	resize comments to image size 
	//

	resizeComments: function() {
		var imageWidth = getStyle($('outerImageContainer'),'width');

		$('imageCommentsList').style.width = imageWidth;
		$('imageCommentsForm').style.width = imageWidth;
	},

	//
	//	changeImage()
	//	Hide most elements and preload image in preparation for resizing image container.
	//
	changeImage: function(imageNum) {	
		
		activeImage = imageNum;	// update global var

		// hide elements during transition
		Element.show('loading');
		Element.hide('lightboxImage');
		Element.hide('hoverNav');
		Element.hide('prevLink');
		Element.hide('nextLink');
		Element.hide('imageDataContainer');
		Element.hide('numberDisplay');

		Element.show('imageCommentsLoading');
		Element.hide('imageCommentsList');
		Element.hide('imageCommentsNone');

		imgPreloader = new Image();

		// once image is preloaded, resize image container
		imgPreloader.onload=function(){
			Element.setSrc('lightboxImage', imageArray[activeImage].href);
			myLightbox.resizeImageContainer(imgPreloader.width, imgPreloader.height);
		}
		imgPreloader.src = imageArray[activeImage].href;

		var xml = myLightbox.getImageCommentsXML(imageArray[activeImage]);
		myLightbox.showComments(xml);
	},

	showComments: function(xml) {
		if (xml == null) {
			Element.hide('imageCommentsLoading');
			Element.show('imageCommentsNone');
		} else {
			Element.hide('imageCommentsLoading');

			var list = $('imageCommentsList');
			while (list.lastChild) list.removeChild(list.lastChild);
			Element.show('imageCommentsList');

			for (var i=0; i<xml.documentElement.childNodes.length; i++) {
				var comment = xml.documentElement.childNodes[i];
				if (comment.nodeName != 'comment') continue;

				var name = 'anonymous';
				var text = '';
				for (var j=0; j<comment.childNodes.length; j++) {
					if (comment.childNodes[j].nodeName == 'name') {
						name = comment.childNodes[j].firstChild.nodeValue;
					} else if (comment.childNodes[j].nodeName == 'text') {
						text = comment.childNodes[j].firstChild.nodeValue;
					}
				}
				var date = comment.getAttribute("date");

				var li = document.createElement("li");
				list.appendChild(li);
				var div = document.createElement("div");
				div.className = "lohs";
				var span = document.createElement("span");
				span.className = "name";
				span.appendChild(document.createTextNode(name));
				div.appendChild(span);
				span = document.createElement("span");
				span.className = "date";
				span.appendChild(document.createTextNode(date));
				div.appendChild(span);
				li.appendChild(div);

				div = document.createElement("div");
				div.className = "text";
				var lines = text.split("\n");
				for (var k=0; k<lines.length; k++) {
					var p = document.createElement("p");
					p.appendChild(document.createTextNode(lines[k]));
					div.appendChild(p);
				}
				li.appendChild(div);
			}
		}
	},

	getImageCommentsXML: function(image, post) {
		var xml = image.commentsXML;
		var xmlDate = image.commentsDate;
		var timeout = (new Date()).valueOf() - 600000; // 10 min?

		if (!xml || xmlDate < timeout || post) {
			image.commentsXML = null;
			image.commentsDate = null;

			var imageName = image.href.substring(image.href.lastIndexOf("/") + 1, image.href.length);
			var req = Ajax.getTransport();

			if (post) {
				var name = $('imageCommentsName');
				var text = $('imageCommentsText');

				req.open('POST', "<?=$WEB_ROOT?>/comments.php?gallery=<?=$GALLERY?>&image=" + imageName, false);
				req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				req.send('add=1&name=' + encodeURIComponent(name.value) + '&text=' + encodeURIComponent(text.value));

				text.value = '';
			} else {
				req.open('GET', "<?=$WEB_ROOT?>/comments.php?gallery=<?=$GALLERY?>&image=" + imageName, false);
				req.send(null);
			}

			if (req.status == 200 && req.responseXML != null) {
				image.commentsXML = req.responseXML;
				image.commentsDate = (new Date()).valueOf();
			}
		}

		return image.commentsXML;
	},

	submitComment: function() {
		$('imageCommentsNameLabel').style.color='#111111';
		$('imageCommentsName').style.border='1px solid #111111';
		$('imageCommentsTextLabel').style.color='#111111';
		$('imageCommentsText').style.border='1px solid #111111';

		if ($F('imageCommentsName').match(/^\s*$/)) {
			$('imageCommentsNameLabel').style.color='red';
			$('imageCommentsName').style.border='1px solid red';
			Field.focus('imageCommentsName');
			return;
		} else if ($F('imageCommentsText').match(/^\s*$/)) {
			$('imageCommentsTextLabel').style.color='red';
			$('imageCommentsText').style.border='1px solid red';
			Field.focus('imageCommentsText');
			return;
		}
		var xml = myLightbox.getImageCommentsXML(imageArray[activeImage], true);
		myLightbox.showComments(xml);
		this.resizeOverlay();
	},

	//
	//	resizeImageContainer()
	//
	resizeImageContainer: function( imgWidth, imgHeight) {

		// get current height and width
		this.wCur = Element.getWidth('outerImageContainer');
		this.hCur = Element.getHeight('outerImageContainer');

		// scalars based on change from old to new
		this.xScale = ((imgWidth  + (borderSize * 2)) / this.wCur) * 100;
		this.yScale = ((imgHeight  + (borderSize * 2)) / this.hCur) * 100;

		// calculate size difference between new and old image, and resize if necessary
		wDiff = (this.wCur - borderSize * 2) - imgWidth;
		hDiff = (this.hCur - borderSize * 2) - imgHeight;

		if(!( hDiff == 0)){ new Effect.Scale('outerImageContainer', this.yScale, {scaleX: false, duration: resizeDuration, queue: 'front'}); }
		if(!( wDiff == 0)){ new Effect.Scale('outerImageContainer', this.xScale, {scaleY: false, delay: resizeDuration, duration: resizeDuration}); }

		// if new and old image are same size and no scaling transition is necessary, 
		// do a quick pause to prevent image flicker.
		if((hDiff == 0) && (wDiff == 0)){
			if (navigator.appVersion.indexOf("MSIE")!=-1){ pause(250); } else { pause(100);} 
		}

		Element.setHeight('prevLink', imgHeight);
		Element.setHeight('nextLink', imgHeight);
		Element.setWidth( 'imageDataContainer', imgWidth + (borderSize * 2));

		this.showImage();
	},
	
	//
	//	showImage()
	//	Display image and begin preloading neighbors.
	//
	showImage: function(){
		Element.hide('loading');
		new Effect.Appear('lightboxImage', { duration: 0.5, queue: 'end', afterFinish: function(){	myLightbox.updateDetails(); } });
		this.preloadNeighborImages();
	},

	//
	//	updateDetails()
	//	Display caption, image number, and bottom nav.
	//
	updateDetails: function() {
	
		Element.show('caption');
		Element.setInnerHTML( 'caption', imageArray[activeImage].title);
		
		// if image is part of set display 'Image x of x' 
		if(imageArray.length > 1){
			Element.show('numberDisplay');
			Element.setInnerHTML( 'numberDisplay', "Image " + eval(activeImage + 1) + " of " + imageArray.length);
		}

		new Effect.Parallel(
			[ new Effect.SlideDown( 'imageDataContainer', { sync: true, duration: resizeDuration + 0.25, from: 0.0, to: 1.0 }), 
			  new Effect.Appear('imageDataContainer', { sync: true, duration: 1.0 }) ], 
			{ duration: 0.65, afterFinish: function() { myLightbox.updateNav();} } 
		);
		this.resizeOverlay();
		this.resizeComments();
	},

	//
	//	updateNav()
	//	Display appropriate previous and next hover navigation.
	//
	updateNav: function() {

		Element.show('hoverNav');				

		// if not first image in set, display prev image button
		if(activeImage != 0){
			Element.show('prevLink');
			document.getElementById('prevLink').onclick = function() {
				myLightbox.changeImage(activeImage - 1); return false;
			}
		}

		// if not last image in set, display next image button
		if(activeImage != (imageArray.length - 1)){
			Element.show('nextLink');
			document.getElementById('nextLink').onclick = function() {
				myLightbox.changeImage(activeImage + 1); return false;
			}
		}
		
		this.enableKeyboardNav();
	},

	//
	//	enableKeyboardNav()
	//
	enableKeyboardNav: function() {
		document.onkeydown = this.keyboardAction; 
	},

	//
	//	disableKeyboardNav()
	//
	disableKeyboardNav: function() {
		document.onkeydown = '';
	},

	//
	//	keyboardAction()
	//
	keyboardAction: function(e) {
		if (e == null) { // ie
			keycode = event.keyCode;
		} else { // mozilla
			keycode = e.which;
		}

		key = String.fromCharCode(keycode).toLowerCase();

		if(keycode == 37){	// display previous image
			if(activeImage != 0){
				myLightbox.disableKeyboardNav();
				myLightbox.changeImage(activeImage - 1);
			}
		} else if(keycode == 39){	// display next image
			if(activeImage != (imageArray.length - 1)){
				myLightbox.disableKeyboardNav();
				myLightbox.changeImage(activeImage + 1);
			}
		}


	},

	//
	//	preloadNeighborImages()
	//	Preload previous and next images.
	//
	preloadNeighborImages: function(){

		if((imageArray.length - 1) > activeImage){
			preloadNextImage = new Image();
			preloadNextImage.src = imageArray[activeImage + 1].href;
		}
		if(activeImage > 0){
			preloadPrevImage = new Image();
			preloadPrevImage.src = imageArray[activeImage - 1].href;
		}
	
	},

	//
	//	end()
	//
	end: function() {
		this.disableKeyboardNav();
		Element.hide('lightbox');
		new Effect.Fade('overlay', { duration: 0.2});

		Element.scrollTo(imageArray[activeImage]);
		imageArray[activeImage].focus();
		showSelectBoxes();
	}
}

// -----------------------------------------------------------------------------------

//
// getPageScroll()
// Returns array with x,y page scroll values.
// Core code from - quirksmode.org
//
function getPageScroll(){

	var yScroll;

	if (self.pageYOffset) {
		yScroll = self.pageYOffset;
	} else if (document.documentElement && document.documentElement.scrollTop){	 // Explorer 6 Strict
		yScroll = document.documentElement.scrollTop;
	} else if (document.body) {// all other Explorers
		yScroll = document.body.scrollTop;
	}

	arrayPageScroll = new Array('',yScroll) 
	return arrayPageScroll;
}

// -----------------------------------------------------------------------------------

//
// getPageSize()
// Returns array with page width, height and window width, height
// Core code from - quirksmode.org
// Edit for Firefox by pHaez
//
function getPageSize(){
	
	var xScroll, yScroll;
	
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	
	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}	
	
	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else { 
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth){	
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}

	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight) 
	return arrayPageSize;
}

// -----------------------------------------------------------------------------------


function showSelectBoxes(){
	selects = document.getElementsByTagName("select");
	for (i = 0; i != selects.length; i++) {
		selects[i].style.visibility = "visible";
	}
}

// ---------------------------------------------------

function hideSelectBoxes(){
	selects = document.getElementsByTagName("select");
	for (i = 0; i != selects.length; i++) {
		selects[i].style.visibility = "hidden";
	}
}

// ---------------------------------------------------

//
// pause(numberMillis)
// Pauses code execution for specified time. Uses busy code, not good.
// Code from http://www.faqts.com/knowledge_base/view.phtml/aid/1602
//
function pause(numberMillis) {
	var now = new Date();
	var exitTime = now.getTime() + numberMillis;
	while (true) {
		now = new Date();
		if (now.getTime() > exitTime)
			return;
	}
}

// ---------------------------------------------------

function getStyle(oElm, strCssRule){
    var strValue = "";
    if (document.defaultView && document.defaultView.getComputedStyle){
        strValue = document.defaultView.getComputedStyle(oElm, "").getPropertyValue(strCssRule);
    } else if(oElm.currentStyle){
        strCssRule = strCssRule.replace(/\-(\w)/g, function (strMatch, p1){
            return p1.toUpperCase();
        });
        strValue = oElm.currentStyle[strCssRule];
    }
    return strValue;
}

// ---------------------------------------------------

function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		curleft = obj.offsetLeft
		curtop = obj.offsetTop
		while (obj = obj.offsetParent) {
			curleft += obj.offsetLeft
			curtop += obj.offsetTop
		}
	}
	return [curleft,curtop];
}

// ---------------------------------------------------

function initGallery() {
	myLightbox = new Lightbox();

	var is_ie = navigator.userAgent.toLowerCase().indexOf("msie") > -1;
	var a = document.getElementsByTagName("a");
	for (var i=0; i<a.length; i++) {
		if (a[i].className == "addr") {
			a[i].href = "mailto:";
			a[i].href += a[i].childNodes[0].firstChild.nodeValue;
			a[i].href += "@";
			a[i].href += a[i].childNodes[2].firstChild.nodeValue;
		}
	}
}
Event.observe(window, 'load', initGallery, false);