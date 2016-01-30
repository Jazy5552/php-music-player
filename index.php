<?php
$dir = scandir(__DIR__);
$imgsHTML = '';
$songsHTML = '';
$i = 0;
foreach ($dir as $file) {
	if (strpos($file, '.mp3') !== false) {
		$songsHTML .= '<article class="song">
			<div><h2 id="' . $i++ . '">' . substr($file, 0, strpos($file, '.mp3')) . '</h2></div>
			<div><audio controls class="audio" preload="none" src="' . basename($file) . '">
			Not supported
			</audio></div></article>';
	} else if (strpos($file, '.jpg') !== false 
      || strpos($file, '.png') !== false) {
		#Use the all jpg/png as the album cover
		$imgsHTML .= '<img class="albumart" src="' . basename($file) . '"></img>';
  }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<script>	
function shuffle(a) { //Shuffles the array
	var i = a.length, x, j;
	while (--i) {
		j = Math.floor(Math.random() * (i+1));
		i;
		x = a[i];
		a[i] = a[j];
		a[j] = x;
	}
	console.log(a);
	//return a;
}
function playall() { //Damn nice closure!
	var CurrentSong = 0;
	var CurrentAudio = null;
	var Songs = document.getElementsByClassName('audio');
	Songs = Array.prototype.slice.call(Songs, 0);
	var OriginalSongs = Songs.slice(0); //clone
	var bPlayText = 'PLAY';
	var bPausText = 'PAUSE';
	var bDefaText = document.getElementById('play').innerHTML;
	var bLoopSingText = 'LOOP SINGLE';
	var bLoopAllText = 'LOOP ALL';
	var bLoopDefaText = document.getElementById('loop').innerHTML; 
	var bShuffleOnText = 'SHUFFLE ON';
	var bShuffleDefaText = document.getElementById('shuffle').innerHTML;
	var notPlayingOpacity = 0.5;
	var loop = false; //This is for all songs loop
	var shuffled = false; //Keep track of shuffle
	var oTitle = document.title;
	for (var i = 0; i < Songs.length; i++) {
		Songs[i].pause();
		Songs[i].parentNode.parentNode.style.opacity = notPlayingOpacity;
		Songs[i].style.display = 'none';
		Songs[i].parentNode.parentNode.getElementsByTagName('h2')[0].onclick = function(){onClick(this.id);}; //Allow to click to jump to song
	}
	function updateCurrentSong() {
		console.log(CurrentSong + ' ' + CurrentAudio + ' ' + Songs.length + ' ' + Songs[CurrentSong].src);
		//Make non playing transparent
		for (var i = 0; i < Songs.length; i++) {
			Songs[i].parentNode.parentNode.style.opacity = notPlayingOpacity;
			Songs[i].style.display = 'none';
		}
		//Except currently playing //WARN Its all gone to shit because of moving the audio up
		Songs[CurrentSong].parentNode.parentNode.removeAttribute('style');
		var cslabel = Songs[CurrentSong].parentNode.parentNode.getElementsByTagName('h2')[0].innerHTML;
		document.title = '[' + cslabel + ']';
		//Only leave current text visible

		//Copy playing to the top //NOW CHANGE SRC
		//label.innerHTML = Songs[CurrentSong].parentNode.parentNode.outerHTML;
		CurrentAudio = document.getElementById('currentsong');
		CurrentAudio.parentNode.getElementsByTagName('h2')[0].innerHTML = cslabel;
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
			shufflebutton.innerHTML = bShuffleOnText;
		} else {
			Songs = OriginalSongs.slice(0);
			shuffled = false;
			shufflebutton.innerHTML = bShuffleDefaText;
			next();
		}
		pause();
		CurrentSong = 0;
		play();
	}
	function onClick(id) {
		if (CurrentAudio === null) return; //Hmmmm not sure if i should leave this
		CurrentSong = Number(id);
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
		if (CurrentAudio === null) return;
		CurrentAudio.pause();
		CurrentAudio.removeEventListener('ended', onEnded);
		document.getElementById('play').onclick = resume;
		document.getElementById('play').innerHTML = bPlayText;
	}
	function resume() {
		if (CurrentAudio === null) return; //Nasty safetys...
		CurrentAudio.play();
		CurrentAudio.addEventListener('ended', onEnded);
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
			Songs[i].removeAttribute('style');
		}
		document.title = oTitle;
		CurrentSong = 0;
		CurrentAudio.parentNode.getElementsByTagName('h2')[0].innerHTML = '';
		//updateCurrentSong();
		//var label = document.getElementById('currentsong');
		//label.innerHTML = '';
		CurrentAudio.removeAttribute('style');
		CurrentAudio.loop = false;
		CurrentAudio = null;
		loop = false;
		document.getElementById('play').onclick = play;
		document.getElementById('play').innerHTML = bDefaText;
		document.getElementById('loop').innerHTML = bLoopDefaText; 
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

	//Attach events to buttons
	document.getElementById('play').onclick = pause; //Override playall
	document.getElementById('play').innerHTML = bPausText;
	document.getElementById('next').onclick = next;
	document.getElementById('previous').onclick = previous;
	document.getElementById('stop').onclick = stop;
	document.getElementById('loop').onclick = loopToggle;
	document.getElementById('shuffle').onclick = shuffleToggle;
	play();
}
function scrollAlbumArt() {
	var DELAY = 20;
	var imgs = document.getElementsByClassName('albumart');
  var artsHolder = document.getElementById('arts');
  var backgroundHolder = document.getElementsByTagName('header')[0];
	if (imgs === undefined) return; //No images were found
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
      backgroundHolder.style.backgroundSize = '30%';
      backgroundHolder.style.backgroundRepeat = 'round';
      backgroundHolder.style.backgroundPosition = 'top right';
    } else {
      imgs[current].style.opacity = '1';
    }
		//Change to the next image after delay
		setTimeout(scrollNext, DELAY*1000);
	}
  scrollNext();
	//setTimeout(scrollNext, DELAY*1000);
}

window.onload = function() {
	document.getElementById('play').innerHTML = 'PLAY ALL';
	document.getElementById('play').onclick = playall;
	scrollAlbumArt();
}
</script>
<style>
header {
	font-size: 3em;
	text-shadow: 1px 1px gray;
  overflow-x: auto;
  text-shadow: -1px 0 white, 0 1px white, 1px 0 white, 0 -1px white;
  color: black;
}
#controllabel {
	width: 190px;
	background-color: white;
	font-size: 14px;
	margin-top: -16px;
}
#controls {
	margin: 10px 0px 10px 0px;
	padding: 5px 5px 5px 5px;
	display: inline-block;
	border: 3px gray solid;
	width: auto;
}
#controls button {
  margin: 3px 0px;
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
}
img.albumart {
	/*width: 300px;
	Maintain original size*/
  position: absolute;
	right: 0;
	margin: 100px;
	border-style: double;
	border-width: 20px;
	transition: all 5s ease-in-out;
	opacity: 0;
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
}

</style>
<title><?php echo basename(__DIR__) ?></title>
<link rel="shortcut icon" href="jazyserver.com/favicons/favicon.ico" />
<link rel="icon" type="image/png" href="jazyserver.com/favicons/favicon-96x96.png" />
<link rel="icon" type="image/png" href="jazyserver.com/favicons/favicon-32x32.png" />
<meta name="author" content="Jazy Llerena" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>
<header><?php echo basename(__DIR__) ?></header>
<section id="arts">
<?php echo $imgsHTML; ?>
</section>
<section id="controls">
	<div id='controllabel'>Controls for playing all the songs</div>
	<div>
		<span><h2 id="cslabel"></h2><audio id="currentsong" preload="auto" controls></audio></span>
		<button type="button" id="play"><!--Set by js--></button>
		<button type="button" id="next">NEXT</button>
		<button type="button" id="previous">PREVIOUS</button>
		<button type="button" id="stop">STOP</button>
		<button type="button" id="loop">LOOP OFF</button>
		<button type="button" id="shuffle">SHUFFLE</button>
	</div>
</section>
<section id="songs">
<?php echo $songsHTML; ?>
</section>
</body>
</html>

