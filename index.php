<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<script>
function playall() { //Damn nice closure!
	var CurrentSong = 0;
	var CurrentAudio = null;
	var Songs = document.getElementsByClassName('audio');
	var bPlayText = 'PLAY';
	var bPausText = 'PAUSE';
	var bDefaText = document.getElementById('play').innerHTML;
	var bLoopSingText = 'LOOP SINGLE';
	var bLoopAllText = 'LOOP ALL';
	var bLoopDefaText = document.getElementById('loop').innerHTML; 
	var notPlayingOpacity = 0.5;
	var loop = false; //This is for all songs loop
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
		//Only leave current text visible

		//Copy playing to the top //NOW CHANGE SRC
		//label.innerHTML = Songs[CurrentSong].parentNode.parentNode.outerHTML;
		CurrentAudio = document.getElementById('currentsong');
		CurrentAudio.style.display = 'block';
		CurrentAudio.src = Songs[CurrentSong].src;
		CurrentAudio.load();
		//label.innerHTML = '#' + (CurrentSong+1) + ' ' +  Songs[CurrentSong].parentNode.parentNode.
		//	getElementsByTagName('div')[0].getElementsByTagName('h2')[0].innerHTML;
	}
	function onClick(id) {
		if (CurrentAudio === null) return; //Hmmmm not sure if i should leave this TODO
		CurrentSong = id;
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
	function stop() {
		pause();
		for (var i = 0; i < Songs.length; i++) {
			Songs[i].pause();
			Songs[i].currentTime = 0;
			Songs[i].removeEventListener('ended', onEnded);
			Songs[i].parentNode.parentNode.removeAttribute('style');
			Songs[i].removeAttribute('style');
		}
		CurrentSong = 0;
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
	play();
}

window.onload = function() {
	document.getElementById('play').innerHTML = 'PLAY ALL';
	document.getElementById('play').onclick = playall;
}
</script>
<style>
header {
	font-size: 3em;
	text-shadow: 1px 1px gray;
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
#currentsong {
	display: none;
	margin: 5px 0px 5px 0px;
	padding: 0px;
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
</head>
<body>
<header><?php echo basename(__DIR__) ?></header>
<section id="controls">
	<div id='controllabel'>Controls for playing all the songs</div>
	<div>
		<span><audio id="currentsong" preload="auto" controls></audio></span>
		<button type="button" id="play"><!--Set by js--></button>
		<button type="button" id="next">NEXT</button>
		<button type="button" id="previous">PREVIOUS</button>
		<button type="button" id="stop">STOP</button>
		<button type="button" id="loop">LOOP</button>
	</div>
</section>
<section id="songs">
<?php
$dir = scandir(__DIR__);
$i = 0;
foreach ($dir as $file) {
	if (strpos($file, '.mp3') !== false) {
		echo '<article class="song">
			<div><h2 id="', $i++, '">', substr($file, 0, strpos($file, '.mp3')), '</h2></div>
			<div><audio controls class="audio" preload="none" src="', basename($file), '">
			Not supported
			</audio></div></article>';
	}
}
?>
</section>
</body>
</html>

