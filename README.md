# php-music-player
A web mp3 music player in html5 that will grab all mp3 files in a directory and create a webpage with audio players for every song and some global controls to play them all sequentially and skip between them.

To use simply place into a directory with mp3 files on your webserver.

Can now select loop settings for single song loop or while directory loop.

Can now click on the song name to skip to it during PLAY ALL sequence.
The index file will now recursively search for music in directories and try to copy itself. WARNING: May throw errors if permissions are insufficient, and may cause performance hit if page is loaded frequently

TODO: Add download button next to songs

