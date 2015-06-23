<!DOCTYPE html>
<html>
<head>
<script>
function playall() { //Damn nice closure!
	var CurrentSong = 0;
	var CurrentAudio;
	var Songs = document.getElementsByClassName('audio');
	var bPlayText = 'PLAY';
	var bPausText = 'PAUSE';
	var bDefaText = document.getElementById('play').innerHTML;
	var notPlayingOpacity = 0.5;
	for (var i = 0; i < Songs.length; i++) {
		Songs[i].pause();
		Songs[i].removeEventListener('ended', onEnded);
		Songs[i].parentNode.parentNode.style.opacity = notPlayingOpacity;
		Songs[i].style.display = 'none';
	}
	function updateCurrentSong() {
		console.log(CurrentSong + ' ' + CurrentAudio + ' ' + Songs.length);
		var label = document.getElementById('currentsong');
		//Make non playing transparent
		for (var i = 0; i < Songs.length; i++) {
			Songs[i].parentNode.parentNode.style.opacity = notPlayingOpacity;
			Songs[i].style.display = 'none';
		}
		//Except currently playing //WARN Its all gone to shit because of moving the audio up
		Songs[CurrentSong].parentNode.parentNode.removeAttribute('style');

		//Copy playing to the top
		label.innerHTML = Songs[CurrentSong].parentNode.parentNode.outerHTML;
		CurrentAudio = label.getElementsByTagName('audio')[0];
		CurrentAudio.removeAttribute('style');
		CurrentAudio.className = '';
		//label.innerHTML = '#' + (CurrentSong+1) + ' ' +  Songs[CurrentSong].parentNode.parentNode.
		//	getElementsByTagName('div')[0].getElementsByTagName('h2')[0].innerHTML;
	}
	function onEnded() {
		if (CurrentSong >= Songs.length) {
			CurrentSong = 0;
		} else {
			++CurrentSong;
		}
		CurrentAudio.removeEventListener('ended', onEnded);
		updateCurrentSong();
		play();
	}
	function play() {
		updateCurrentSong();
		if (CurrentAudio === undefined) return;
		CurrentAudio.currentTime = 0;
		CurrentAudio.play();
		CurrentAudio.addEventListener('ended', onEnded);
		document.getElementById('play').onclick = pause;
		document.getElementById('play').innerHTML = bPausText;
	}
	function pause() {
		if (CurrentAudio === undefined) return;
		CurrentAudio.pause();
		CurrentAudio.removeEventListener('ended', onEnded);
		document.getElementById('play').onclick = resume;
		document.getElementById('play').innerHTML = bPlayText;
	}
	function resume() {
		if (CurrentAudio === undefined) return; //Nasty safetys...
		CurrentAudio.play();
		CurrentAudio.addEventListener('ended', onEnded);
		document.getElementById('play').onclick = pause;
		document.getElementById('play').innerHTML = bPausText;
	}
	function stop() {
		for (var i = 0; i < Songs.length; i++) {
			Songs[i].pause();
			Songs[i].currentTime = 0;
			Songs[i].removeEventListener('ended', onEnded);
			Songs[i].parentNode.parentNode.removeAttribute('style');
			Songs[i].removeAttribute('style');
		}
		CurrentSong = 0;
		//updateCurrentSong();
		var label = document.getElementById('currentsong');
		label.innerHTML = '';
		CurrentAudio = undefined;
		document.getElementById('play').onclick = play;
		document.getElementById('play').innerHTML = bDefaText;
	}
	function next() {
		if (CurrentAudio === undefined) return; //Nasty safetys...
		pause();
		if (++CurrentSong >= Songs.length) {
			CurrentSong--;
		}
		play();
	}
	function previous() {
		if (CurrentAudio === undefined) return; //Nasty safetys...
		pause();
		if (--CurrentSong < 0 ) {
			CurrentSong++; //Not gonna let you!
		}
		play();
	}

	//Attach events to buttons
	document.getElementById('play').onclick = pause; //Override playall
	document.getElementById('play').innerHTML = bPausText;
	document.getElementById('next').onclick = next;
	document.getElementById('previous').onclick = previous;
	document.getElementById('stop').onclick = stop;
	//document.getElementById('currentsong').innerHTML = Songs[CurrentSong].parentNode.parentNode.outerHTML;
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
#controls audio #controls#currentsong {
	margin: auto;
	padding 0px;
}

.song {
	margin: auto;
	padding: 10px 0px 10px 0px;
}
.song audio, .song h2 {
	margin: auto;
}
.song h2 {
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
		<span id="currentsong"></span>
		<button type="button" id="play"><!--Set by js--></button>
		<button type="button" id="next">NEXT</button>
		<button type="button" id="previous">PREVIOUS</button>
		<button type="button" id="stop">STOP</button>
	</div>
</section>
<section id="songs">
<?php
$dir = scandir(__DIR__);
foreach ($dir as $file) {
	if (strpos($file, '.mp3') !== false) {
		echo '<article class="song">
			<div><h2>', substr($file, 0, strpos($file, '.mp3')), '</h2></div>
			<div><audio controls class="audio" preload="none" src="', basename($file), '">
			Not supported
			</audio></div></article>';
	}
}
?>
</section>
</body>
</html>

