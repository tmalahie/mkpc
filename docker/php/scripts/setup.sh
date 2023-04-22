#!/bin/sh
if [ "$1" != '--force' ]; then
	echo 'Warning, this will completely reset your local mkpc database. Continue? [Y/n]'
	read confirm
	if [ "$confirm" != 'Y' ] && [ "$confirm" != "y" ] && [ "$confirm" != "" ]; then
		exit 0
	fi
fi
DIR=`dirname $(realpath $0)`
cd $DIR
php setupdb.php
rm -f /var/www/html/images/avatars/*
rm -f /var/www/html/images/uploads/map*
rm -f /var/www/html/images/uploads/course*
rm -f /var/www/html/images/creation_icons/*.png
rm -f /var/www/html/images/creation_icons/uploads/*
rm -f /var/www/html/images/sprites/uploads/*
chmod 777 /var/www/html/images/avatars
chmod 777 /var/www/html/images/uploads
chmod 777 /var/www/html/images/creation_icons
chmod 777 /var/www/html/images/creation_icons/uploads
chmod 777 /var/www/html/images/sprites/uploads
chmod 777 /var/www/html
