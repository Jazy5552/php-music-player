#!/bin/bash

find /var/www/test/downloads -name "index.php" -delete
cp index.php /var/www/test/downloads
#Now load the downloads/index.php file for it to fill the subdirs

