<?php
//TODO Get rid of these stupid fucking strtolowers!
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['down'])) {
  //Client wants to download a file lets give it to em
	$file = $_GET['down'];

	//Ensure the file is in a sub dir of this file (Security)
	$curDir = __DIR__ . '/';
	$fullPath = realpath($curDir . $file);
	if (strpos($fullPath, $curDir) !== 0) {
		die('Unauthorized');
	}
	$file = $fullPath;

	//Only allow mp3/mp4 and images
	if ((strpos(strtolower($file), '.mp') === false
		&& strpos(strtolower($file), 'jpg') === false
		&& strpos(strtolower($file), 'png') === false)
    || file_exists($file) === false) {
    //Not an mp3/4? fk off
    die('Unauthorized');
  }

  header('Content-Type: application/octet-stream');
  header('Content-Transfer-Encoding: Binary');
	header('Content-Disposition: attachment; filename="' .basename($file). '"');
	header('Content-Length: ' . filesize($file));
  readfile($file); // do the double-download-dance (dirty but worky)
  die();
}
//WARNING Change this depending on what you want the file to do
$INFECTDEPTH = 3; //Depth at which this index file will clone itself into directories
//Set INFECTDEPTH to 0 to prevent this file from cloning itself
//NOTE: Each iteration will clone it self deeper when accessed. This is just
//to dampen the general server load. aka: shitty coding

//TODO Add more customization
//DONT EDIT BELOW THIS
$superrecursive = isset($_GET['recursive']); #If recursive then form a huge music list
$_dir = './';
$_filename = basename(__FILE__); #Name of the php file to be ignored
$_imgsHTML = '';
$_imgFilesHTML = '';
$_songsHTML = '';
$_directoriesHTML = '';
$_favicon = '';
$_i = 0; #Used as the ID for the songs
$_deep = 10; #Limit recursions

function CreateHTMLCode($odir, $filename, $superrecursive,
	&$imgsHTML, &$imgFilesHTML, &$songsHTML, &$directoriesHTML, &$favicon, &$i, $deep) {
	if ($deep < 1) { //Recursion control
		return;
	}
	$deep--;

	$dir = scandir($odir);
	foreach ($dir as $file) {
		$file = $odir . $file;
		//Get the filesize
		$fs = human_filesize(filesize($file));
		if (strpos(strtolower($file), '.mp3') !== false) {
			$songsHTML .= '
			<article class="song">
				<div class="tooltip">
					<i class="download-button hide fa fa-arrow-circle-o-down fa-2x"></i>
					<span class="tooltiptext">' . $fs . '</span>
					<h2 id="' . $i++ . '">'
					. substr(basename($file), 0, strpos(basename(strtolower($file)), '.mp3')) #Change to basename and use listed items
					. '</h2>
				</div>
				<div>
					<audio controls id="' . $file . '"
          class="audio" preload="none" src="'
					. $file . '">
					Not Supported
					</audio>
				</div>
			</article>';
		} else if (strpos(strtolower($file), '.jpg') !== false
				|| strpos(strtolower($file), '.jpeg') !== false
				|| strpos(strtolower($file), '.png') !== false) {
			#Use the all jpg/png as the album cover
			$imgsHTML .= '<img class="albumart"
				data-src="' . $file . '"></img>';
			#Create a files html in case audio mode is turned off
			$imgFilesHTML .= '<article class="file image">
			<div class="tooltip"><a class="defaultCursor link" href="' . $file . '" id="' . $file . '"><i class="fa fa-file-o" aria-hidden="true"></i>
      ' . substr($file, 2) . '</a><span class="tooltiptext">' . $fs . '</span></div>
			</article>';
			#Use the first one as the favicon
			if ($favicon === '') {
				$favicon = '<link rel="icon" href="' . $file . '" />';
			}
		} else if (is_dir($file) && basename($file) !== '.') {
			if ($superrecursive) {
				if (basename($file) !== '..') { //Dont show ../ directories
					$songsHTML .= '<div class="box"><div class="boxlabel">'
						. basename($file) . '</div>';
					//Run this function into the directory
					CreateHTMLCode($file . '/', $filename, $superrecursive,
						$imgsHTML, $imgFilesHTML, $songsHTML, $directoriesHTML, $favicon, $i, $deep);
					$songsHTML .= '</div>';
					//TODO Remove empty box divs
				}
			} else {
				$directoriesHTML .= '<article class="dir">
				<div class=""><a class="defaultCursor link" href="' . $file . '" id="' . $file . '"><i class="fa fa-folder-open" aria-hidden="true"></i>
        ' . substr($file, 2) . '</a></div>
				</article>';
			}
		} else if (basename($file) !== '.'
			&& basename($file) !== '..'
			&& $superrecursive !== true //Dont show files/dirs when in recursive mode
			&& basename($file) !== $filename) {
			//Display file name
			$directoriesHTML .= '<article class="file">
			<div class="tooltip"><a class="defaultCursor link" href="' . $file . '" id="' . $file . '"><i class="fa fa-file-o" aria-hidden="true"></i>
      ' . substr($file, 2) . '</a><span class="tooltiptext">' . $fs . '</span></div>
			</article>';
		}
	}
	if ($favicon === '') {
		//Use MY server wide favicons, feel free to change to yours
		$favicon = '
	<link rel="shortcut icon" href="https://jazyserver.com/favicons/favicon.ico" />
	<link rel="icon" type="image/png" href="https://jazyserver.com/favicons/favicon-96x96.png" />
	<link rel="icon" type="image/png" href="https://jazyserver.com/favicons/favicon-32x32.png" />
		';
	}
}
//Will be adding index.php files RECURSIVELY WARNING
function SearchForPotentialAlbums($dirname, $x) {
$filename = basename(__FILE__);
  if ($x < 1) {
    //Stop recursion
    return;
  }
  $d = scandir($dirname);
  foreach ($d as $file) {
		if (is_dir($dirname . '/' . $file)
			&& $file !== '.'
			&& $file !== '..') {
				if (!file_exists($dirname . '/' . $file . '/' . $filename)
          && is_writable($dirname . '/' . $file) ){
					//&& HasSongs($dirname . '/' . $file)) {
        copy('./' . $filename, $dirname . '/' . $file . '/' . $filename);
      }
      SearchForPotentialAlbums($dirname . '/' . $file, $x - 1);
    }
  }
}
function HasSongs($dirname) {
  $d = scandir($dirname);
  foreach ($d as $file) {
    if (strpos(strtolower($file), '.mp') !== false) {
      return true;
    }
  }
  return false;
}
//Thanks to php.net for this nice snippet :D
function human_filesize($bytes, $decimals = 2) {
  $sz = ' KMGTP';
  $factor = floor((strlen($bytes) - 1) / 3); //Clever shit right here
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$sz[$factor] . 'B';
}

if ($superrecursive) {
	//echo '<h3>Super recursion enabled!</h3>';
}

CreateHTMLCode($_dir, $_filename, $superrecursive,
	$_imgsHTML, $_imgFilesHTML, $_songsHTML, $_directoriesHTML, $_favicon, $_i, $_deep);

//WARNING FUCKING SAVAGE AHEAD
SearchForPotentialAlbums(__DIR__, $INFECTDEPTH);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<?php echo $_favicon ?>
<!--
<link rel="shortcut icon" href="https://jazyserver.com/favicons/favicon.ico" />
<link rel="icon" type="image/png" href="https://jazyserver.com/favicons/favicon-96x96.png" />
<link rel="icon" type="image/png" href="https://jazyserver.com/favicons/favicon-32x32.png" />
-->
<meta name="author" content="Jazy Llerena" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<!-- CDN Link with some cool free icons! -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
<script>
function shuffle(a) {
	//Shuffles the array (So basic...)
	var i = a.length, x, j;
	while (--i) {
		j = Math.floor(Math.random() * (i+1));
		i;
		x = a[i];
		a[i] = a[j];
		a[j] = x;
	}
	//console.log(a);
	//return a;
}
function playall() { //Damn nice closure!
	//Setting things up
	var CurrentSong = 0;
	var CurrentAudio = document.getElementById('currentsong');
	var CurrentLabel = document.getElementById('cslabel');
	var LoadingSpinner = document.getElementById('controls').
		getElementsByClassName('fa-spinner')[0];
	var Songs = document.getElementsByClassName('audio');
	Songs = Array.prototype.slice.call(Songs, 0);
	var OriginalSongs = Songs.slice(0); //clone
	var bPlayText = '<i class="fa fa-play" aria-hidden="true"></i>'; //PLAY
	var bPausText = '<i class="fa fa-pause" aria-hidden="true"></i>'; //PAUSE
	var bDefaText = document.getElementById('play').innerHTML;
	var bLoopSingText = 'LOOP SINGLE';
	var bLoopAllText = 'LOOP ALL';
	var bLoopDefaText = document.getElementById('loop').innerHTML;
	var bShuffleDefaText = document.getElementById('shuffle').innerHTML;
	var notPlayingOpacity = 0.5;
	var loop = false; //This is for all songs loop
	var shuffled = false; //Keep track of shuffle
	var oTitle = document.title;
	//Pause and opaque all the songs
	for (var i = 0; i < Songs.length; i++) {
		Songs[i].pause();
		Songs[i].parentNode.parentNode.style.opacity = notPlayingOpacity;
		Songs[i].style.display = 'none';
		Songs[i].parentNode.parentNode.getElementsByTagName('h2')[0].
      classList.add('defaultCursor');
		Songs[i].parentNode.parentNode.getElementsByTagName('h2')[0].onclick
      = function(){onClick(this.id);}; //Allow to click to jump to song
	}
	//Make download buttons visible
	var dButtons = document.getElementsByClassName('download-button');
	for (var i=0; i<dButtons.length; i++) {
		dButtons[i].classList.toggle('hide');
	}

	//Done setting things up
	function updateCurrentSong() {
		console.log(CurrentSong + ' ' + CurrentAudio + ' ' + Songs.length + ' ' + Songs[CurrentSong].src);
		//Make non playing transparent
		for (var i = 0; i < Songs.length; i++) {
			Songs[i].parentNode.parentNode.style.opacity = notPlayingOpacity;
			Songs[i].style.display = 'none';
		}
		//Except currently playing //WARN Its all gone to shit because of moving the audio up, you fuck //OK It no longer moves anything up
		//Songs[CurrentSong].parentNode.parentNode.removeAttribute('style');
		Songs[CurrentSong].parentNode.parentNode.style.opacity = '';
		var cslabel = Songs[CurrentSong].parentNode.parentNode.getElementsByTagName('h2')[0].innerHTML;
		document.title = '[' + cslabel + ']';
		//Find album name if in super recursive mode
		var p = Songs[CurrentSong].parentNode.parentNode.parentNode.getElementsByClassName('boxlabel')[0];
		if (p) {
			cslabel = '<div class="cslabel2">'
				+ p.innerHTML
				+ '</div>'
				+ cslabel;
		}

		CurrentLabel.style.display = 'block';
		CurrentLabel.innerHTML = cslabel;
		CurrentAudio.style.display = 'block';
		CurrentAudio.src = Songs[CurrentSong].src;
		CurrentAudio.load();
		//label.innerHTML = '#' + (CurrentSong+1) + ' ' +  Songs[CurrentSong].parentNode.parentNode.
		//	getElementsByTagName('div')[0].getElementsByTagName('h2')[0].innerHTML;
	}
	function shuffleToggle() { //Just shuffle the songs array
		if (CurrentAudio === null) return;
		var shufflebutton = document.getElementById('shuffle');
		if (!shuffled) {
			shuffle(Songs);
			shuffled = true;
			shufflebutton.classList.add('activated');
		} else {
			Songs = OriginalSongs.slice(0);
			shuffled = false;
			shufflebutton.classList.remove('activated');
			next();
		}
		pause();
		CurrentSong = 0;
		play();
	}
	function onClick(id) {
		if (CurrentAudio === null) return; //Hmmmm not sure if i should leave this
		if (shuffled) {
			//Find the song they clicked on
			for (var i=0; i<Songs.length; i++) {
				if (
					Songs[i].parentNode.parentNode.getElementsByTagName('h2')[0].id
					=== id) {
						CurrentSong = i;
					}
			}
		} else {
			CurrentSong = Number(id);
		}
		pause();
		play();
	}
	function onEnded() {
		if (CurrentSong + 1 >= Songs.length && loop) {
			next(); // I assure this function accomplished more in the glory days!
		} else if (CurrentSong + 1 < Songs.length) {
			next();
		}
	}
	function play() {
		updateCurrentSong();
		if (CurrentAudio === null) return;
		//CurrentAudio.currentTime = 0; //Errors for firefox/safari
		CurrentAudio.play();
		CurrentAudio.addEventListener('ended', onEnded);
		document.getElementById('play').onclick = pause;
		document.getElementById('play').innerHTML = bPausText;
	}
	function pause() {
		console.log('Paused');
		CurrentAudio.pause();
		CurrentAudio.removeEventListener('ended', onEnded);
	}
	function pauseUI() {
		console.log('Paused UI');
		if (CurrentAudio === null) return;
		document.getElementById('play').onclick = resume;
		document.getElementById('play').innerHTML = bPlayText;
	}
	function resume() {
		console.log('Resumed');
		CurrentAudio.play();
		CurrentAudio.addEventListener('ended', onEnded);
	}
	function resumeUI() {
		console.log('Resumed UI');
		if (CurrentAudio === null) return;
		document.getElementById('play').onclick = pause;
		document.getElementById('play').innerHTML = bPausText;
	}
	function stop() { //Reset everything!
		if (CurrentAudio === null) return;
		pause();
		for (var i = 0; i < Songs.length; i++) {
			Songs[i].pause();
			Songs[i].currentTime = 0;
			Songs[i].removeEventListener('ended', onEnded);
			Songs[i].parentNode.parentNode.removeAttribute('style');
      //Remove to jump click
			Songs[i].parentNode.parentNode.getElementsByTagName('h2')[0].
				onclick = '';
      Songs[i].parentNode.parentNode.getElementsByTagName('h2')[0].
        classList.remove('defaultCursor');
			Songs[i].removeAttribute('style');
		}
		var dButtons = document.getElementsByClassName('download-button');
		for (var i=0; i<dButtons.length; i++) {
			dButtons[i].classList.toggle('hide');
		}
		document.title = oTitle;
		CurrentSong = 0;
		CurrentLabel.style.display = '';
		CurrentLabel.innerHTML = '';
		CurrentAudio.parentNode.getElementsByTagName('h2')[0].innerHTML = '';
		CurrentAudio.removeAttribute('style');
		CurrentAudio.loop = false;
		//Remove all event listeners by cloning the element
		var tmp = CurrentAudio.cloneNode(true);
		CurrentAudio.parentNode.replaceChild(tmp, CurrentAudio);
		CurrentAudio = null; //Why you do dis?
		loop = false;
		songLoaded();
		document.getElementById('play').onclick = playall;
		document.getElementById('play').innerHTML = bDefaText;
		document.getElementById('loop').innerHTML = bLoopDefaText;
		document.getElementById('shuffle').innerHTML = bShuffleDefaText;
		document.getElementById('shuffle').classList.remove('activated');
	}
	function next() {
		if (CurrentAudio === null) return; //Nasty safetys...
		pause();
		if (++CurrentSong >= Songs.length) {
			CurrentSong = 0;
		}
		CurrentAudio.removeEventListener('ended', onEnded);
		play();
	}
	function previous() {
		if (CurrentAudio === null) return; //Nasty safetys...
		pause();
		if (--CurrentSong < 0 ) {
			CurrentSong = Songs.length - 1;
		}
		CurrentAudio.removeEventListener('ended', onEnded);
		play();
	}
	function loopToggle() { //This is terrible to understand...sry
	if (CurrentAudio === null) return;
		loopbutton = document.getElementById('loop');
		if (loop) { //Disable all loops
			loop = false;
			loopbutton.innerHTML = bLoopDefaText;
		} else if (!loop && !CurrentAudio.loop) { //Enable single song loop (FIRST)
			CurrentAudio.loop = true;
			loopbutton.innerHTML = bLoopSingText;
		} else { //Enable all songs loop (SECOND)
			loop = true;
			CurrentAudio.loop = false;
			loopbutton.innerHTML = bLoopAllText;
		}
	}

	//Remove loading spinner
	function songLoaded() {
			LoadingSpinner.classList.add('hide');
	}
	function songLoading() {
			LoadingSpinner.classList.remove('hide');
	}

	//Attach events to buttons
	document.getElementById('play').onclick = pause; //Override playall
	document.getElementById('play').innerHTML = bPausText;
	document.getElementById('next').onclick = next;
	document.getElementById('previous').onclick = previous;
	document.getElementById('stop').onclick = stop;
	document.getElementById('loop').onclick = loopToggle;
	document.getElementById('shuffle').onclick = shuffleToggle;
	CurrentAudio.addEventListener('pause', pauseUI);
	CurrentAudio.addEventListener('play', resumeUI);
	//CurrentAudio.addEventListener('canplay', songLoaded);
	CurrentAudio.addEventListener('loadeddata', songLoaded);
	CurrentAudio.addEventListener('loadstart', songLoading);
	play();
}

function scrollAlbumArt() {
	//NO FUCKING BRAKES
	var DELAY = 20;
	var imgs = document.getElementsByClassName('albumart');
  var artsHolder = document.getElementById('arts');
  var backgroundHolder = document.getElementsByTagName('header')[0];
	if (imgs === undefined || imgs.length < 1) return; //No images were found
  //DONT Display the first one
	/*
  imgs[0].style.opacity = '1';
	if (imgs.length === 1) {
		//Only 1 was found just display it
		return;
  }
  */
	var current = 0;
	function scrollNext() {
		//Hide the currnet image and display the next while moving the iterator (current)
		//Check if device width too small
    if (getComputedStyle(artsHolder).display === 'none') {
      //Do nothing
    } else {
      backgroundHolder.style.backgroundImage = '';
			imgs[current].style.opacity = '0';
		}
    current++;
		if (current >= imgs.length) current = 0;
		//Display next image
    if (getComputedStyle(artsHolder).display === 'none') {
      backgroundHolder.style.backgroundImage = 'url(' + imgs[current].src + ')';
      backgroundHolder.style.backgroundSize = 'contain';
      backgroundHolder.style.backgroundRepeat = 'no-repeat';
      backgroundHolder.style.backgroundPosition = 'top left';
    } else {
      imgs[current].style.opacity = '1';
    }
		//Change to the next image after delay
		setTimeout(scrollNext, DELAY*1000);
	}
  scrollNext();
	//setTimeout(scrollNext, DELAY*1000);
}
function attachDirs() {
	//And files
  var dirs = document.querySelectorAll('.file, .dir');
  for (var i=0; i<dirs.length; i++) {
    var dir = dirs[i].getElementsByTagName('h2')[0];
		//console.log(dir.getAttribute('id'));
    dir.addEventListener('click', function() {
      var loc = window.location.href.split('/');
      loc.pop();
			var newloc = loc.join('/') + '/' + this.getAttribute('id');
			//console.log(newloc);
			window.location = newloc;
    });
  }
}
function attachDownloads() {
  var dButtons = document.getElementsByClassName('download-button');
  for (var i=0; i<dButtons.length; i++) {
    dButtons[i].addEventListener('click', function() {
      //Download the song
      var audio = this.parentNode.parentNode.getElementsByTagName('audio')[0];
			download(audio.id);
    });
  }
}

function downImage(e) {
	//Check all images for the visible one
	console.log('Down image');
	var imgs = document.getElementsByClassName('albumart');
	for (var i=0; i<imgs.length; i++) {
		if (imgs[i].style.opacity === '1') {
			download(imgs[i].getAttribute('data-src'));
			return;
		}
	}
}

function download(file) {
	var loc = window.location.href;
	if (loc.indexOf('?') < 0) {
		//Some ghetto GET request...
		loc += '?down=' + file;
	} else {
		//Using ?recursive maybe
		loc += '&down=' + file;
	}

	window.location = loc; //rip
}

function headerClicked(e) {
	if (e.ctrlKey) {
		toggleRecursiveMode();
	}
}

function toggleRecursiveMode() {
	if (window.location.href.indexOf('?recursive') === -1) {
		//Enable
		window.location.href = window.location.href + '?recursive';
	} else {
		//Disable WARN: What if something is after ?recursive!
		var href = window.location.href;
		window.location.href = href.substring(0, href.indexOf('?recursive'));
	}
}

function toggleBox(e) {
	//Uses element.classList which doesn't work on IE10
	//Fuck IE
	var boxlabel = e.target;
	var box = boxlabel.parentElement;
	if (boxlabel.classList.contains('closedboxlabel')) {
		boxlabel.classList.remove('closedboxlabel');
		box.classList.remove('closedbox');
		//boxlabel.classList.add('openboxlabel');
		//box.classList.add('openbox');
		//Open it
		box.childNodes.forEach(function(c) {
			if (c !== boxlabel && c.nodeType == Node.ELEMENT_NODE)
				c.style.display = '';
		});
	} else {
		boxlabel.classList.add('closedboxlabel');
		box.classList.add('closedbox');
		//boxlabel.classList.remove('openboxlabel');
		//box.classList.remove('openbox');
		//Close it
		box.childNodes.forEach(function(c) {
			if (c !== boxlabel && c.nodeType == Node.ELEMENT_NODE)
				c.style.display = 'none';
		});
	}
}

var timer;
var dur = 3000;
function headerTouched(e) { // ( ͡° ͜ʖ ͡°)
	if (e.type === 'touchstart') {
		timer = setTimeout(toggleRecursiveMode, dur);
	} else if (e.type === 'touchmove' || e.type === 'touchend') {
		if (timer)
			clearTimeout(timer);
	}
}

function disableAudioMode() {
	document.getElementById('arts').style.display = 'none';
	document.getElementById('controls').style.display = 'none';
}

function lazyLoadImages() {
	var imgs = document.getElementsByClassName('albumart');
	Array.prototype.forEach.call(imgs, function(img) {
		lazyLoadImg(img);
	});
}
function lazyLoadImg(img) {
	img.setAttribute('src', img.getAttribute('data-src'));
}

function enableControls() {
	var controls = document.getElementById('controls')
	if (controls === null) return; //Controls not present
	var buts = controls.getElementsByTagName('button');
	Array.prototype.forEach.call(buts, function(but) {
		but.disabled = false;
	});
}

window.onload = function() {
	var pa = document.getElementById('play');
	if (pa !== null) {
		pa.onclick = playall;
	}

	var head = document.getElementsByTagName('header')[0];
	if (head !== null) {
		head.onclick = headerClicked;
		head.addEventListener('touchstart', headerTouched);
		head.addEventListener('touchmove', headerTouched);
		head.addEventListener('touchend', headerTouched);
	}

	var imgs = document.getElementsByClassName('albumart');
	Array.prototype.forEach.call(imgs, function(img) {
		img.addEventListener('click', downImage);
	});

	scrollAlbumArt();

	var boxlabels = document.getElementsByClassName('boxlabel');
	for (var i=0; i<boxlabels.length; i++) {
		boxlabels[i].addEventListener('click', toggleBox);
	}

	//attachDirs(); //No longer needed as the file/dir links are actual links now
	attachDownloads();
	enableControls();
	lazyLoadImages();
}
</script>
<style>
body {
}
header {
	font-size: 3em;
	/*text-shadow: 1px 1px gray;*/
  overflow-x: auto;
	/*text-shadow: -1px 0 white, 0 1px white, 1px 0 white, 0 -1px white;*/
	color: black;
	cursor: default;
	user-select: none;
}
.hide {
	display: none;
}
.fa-arrow-circle-o-down {
  position: relative;
  top: 0.15em; /*The shit i put up with...*/
	-webkit-cursor: pointer;
	-moz-cursor: pointer;
	-ms-cursor: pointer;
	cursor: pointer;
  transition: all 1s;
}
.fa-arrow-circle-o-down:hover {
  color: #181; /*Nice color for download?*/
}
#cslabel {
	display: none;
}
/*#controllabel {
	display: inline-block;
	background-color: white;
	font-size: 0.9em;
	position: absolute;
	top: -1.2em;
}*/
#controls {
	margin: 10px 0px 10px 0px;
	padding: 5px 5px 5px 5px;
	display: inline-block;
	border: 3px gray double;
	width: auto;
	position: relative;
}
#controls button {
	margin: 3px 0px;
	height: 2.2em;
	width: 3em;
}
#controls #loop {
	width: 7em;
}
#controls .fa-spinner {
	position: absolute;
	top: 6px;
	left: 3px;
}
#currentsong {
	display: none;
	margin: 5px 0px 5px 0px;
	padding: 0px;
}
@media all and (max-width: 760px) {
  #arts {
    display: none;
	}
	.mobilecenter {
		display: inline-block;
		position: relative;
    left: 50%;
		transform: translateX(-50%);
		width: 100%;
	}
	.wrapper {
		width: 100%;
	}
}
img.albumart {
	/*width: 300px;
	Maintain original size*/
  max-width: 400px;
  position: absolute;
	right: 0;
	margin: 100px;
	border-style: double;
	border-width: 20px;
	transition: all 5s ease-in-out;
	opacity: 0;
	z-index: -1;
}
.wrapper {
	display: inline-block;
}
.shiftup {
	margin-top: -1.5em;
}
.defaultCursor {
	-webkit-cursor: pointer;
	-moz-cursor: pointer;
	-ms-cursor: pointer;
	cursor: pointer;
	-webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
.file a:hover {
	background-color: #AAF;
}
.file {
	margin: auto;
	padding: 2px 0px 2px 0px;
}
.file a {
  margin: 0px;
  margin-top: 2px;
  margin-bottom: 2px;
	display: inline-block;
	font-size: 1.2em;
	/*text-shadow: 1px 0px white, -1px 0px white, 0px 1px white, 0px -1px white;*/
  padding: 2px 2px 2px 2px;
  border: solid 2px blue;
	transition: all 1s;
	user-select: none;
}

.box {
	border-style: inset;
	border-width: 3px;
	border-color: lightblue;
	padding: 5px;
	margin: 10px 0px 10px 0px;
	position: relative;
}

.boxlabel {
	cursor: default;
	user-select: none;
	font-size: 1em;
	font-style: oblique;
	color: darkcyan;
	display: inline-block;
	background-color: lightyellow;
	position: absolute;
	top: -0.6em;
}

.closedboxlabel {
	color: darkred;
}
.closedbox {
	border-style: outset;
}

.cslabel2 {
	font-size: 0.7em;
	font-style: normal;
	font-weight: normal;
	text-align: center;
}

.dir a:hover {
  background-color: #AAA;
}
.dir {
  margin: auto;
	padding: 2px 0px 2px 0px;
}
.dir a {
  margin: 0px;
  margin-top: 2px;
  margin-bottom: 2px;
	display: inline-block;
	font-size: 1.2em;
	/*text-shadow: 1px 0px white, -1px 0px white, 0px 1px white, 0px -1px white;*/
  padding: 2px 2px 2px 2px;
  border: solid 2px black;
	transition: all 1s;
	/*user-select: none;*/
}

a.link {
	text-decoration: none;
	color: inherit;
	font-weight: bold;
}

#shuffle.activated {
  background-color: lightgreen;
}

.song {
	margin: auto;
	padding: 10px 0px 10px 0px;
}
.song audio, .song h2 {
	margin: auto;
}
.song h2 {
	display: inline-block;
	font-size: 1.2em;
	/*text-shadow: 1px 0px white, -1px 0px white, 0px 1px white, 0px -1px white;*/
  transition: all 1s;
}
.song h2.defaultCursor:hover {
  color: #55F; /*Pretty little color*/
}
.tooltip {
	display: inline-block;
	position: relative;
}

/* Tooltip text */
.tooltip .tooltiptext {
	/*visibility: hidden;*/
	display: none;
	width: 6em;
	background-color: black;
	color: #fff;
	text-align: center;
	padding: 5px 0;
	border-radius: 6px;

	/* Position the tooltip text - see examples below! */
	position: absolute;
	top: 0.4em;
	right: -7em;
	z-index: 1;
}

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
	/*visibility: visible;*/
	display: inline;
}

/* Little arrow to the left of the tooltip */
.tooltip .tooltiptext::after {
	content: " ";
	position: absolute;
	top: 50%;
	right: 100%; /* To the left of the tooltip */
	margin-top: -5px;
	border-width: 5px;
	border-style: solid;
	border-color: transparent black transparent transparent;
}

</style>
<title><?php echo basename(__DIR__) ?></title>
</head>
<body>
<header><?php echo basename(__DIR__) ?></header>

<?php
if ($_songsHTML !== '') {
#Enable audio mode if songs are available
?>
<section id="arts">
<?php echo $_imgsHTML; ?>
</section>
<section id="controls" class="mobilecenter">
	<!--<div id='controllabel'>Controls for playing all the songs</div>-->
	<div>
		<span><h2 id="cslabel"></h2><audio id="currentsong" preload="auto" controls></audio></span>
		<button class="control" disabled type="button" id="previous"><!--PREVIOUS--><i class="fa fa-step-backward" aria-hidden="true"></i></button>
		<button class="control" disabled type="button" id="play"><i class="fa fa-play" aria-hidden="true"></i></button>
		<button class="control" disabled type="button" id="next"><!--NEXT--><i class="fa fa-step-forward" aria-hidden="true"></i></button>
		<button class="control" disabled type="button" id="stop"><!--STOP--><i class="fa fa-stop" aria-hidden="true"></i></button>
		<button class="control" disabled type="button" id="shuffle"><!--SHUFFLE--><i class="fa fa-random" aria-hidden="true"></i></button>
		<button class="control" disabled type="button" id="loop">LOOP OFF</button>
		<i class="control fa fa-lg fa-spinner fa-spin hide" aria-hidden="true"></i>
	</div>
</section>
<?php
}
?>

<section id="songs" class="mobilecenter">
<div class="wrapper">
<?php echo $_songsHTML; ?>
</div>
</section>
<section id="dirs">
<div class="wrapper">
<?php
echo $_directoriesHTML;
if ($_songsHTML === '') {
	#No songs so disable audio mode and show images as files
	echo $_imgFilesHTML;
}
?>
</div>
</section>
</body>
</html>

