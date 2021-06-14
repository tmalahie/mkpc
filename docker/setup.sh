#!/bin/sh
if [ "$1" != '--force' ]; then
	echo 'Warning, this will completely reset your local mkpc database. Continue? [Y/n]'
	read confirm
	if [ "$confirm" != 'Y' ] && [ "$confirm" != "y" ] && [ "$confirm" != "" ]; then
		exit 0
	fi
fi
mysql -h 127.0.0.1 -P 8306 -u root -proot mkpc < docker/php/scripts/setup.sql
rm -f docker/php/images/avatars/*
rm -f docker/php/images/uploads/map*
rm -f docker/php/images/uploads/course*
rm -f docker/php/images/creation_icons/*
rm -f docker/php/images/sprites/uploads/*
chmod 777 docker/php/images/avatars
chmod 777 docker/php/images/uploads
chmod 777 docker/php/images/creation_icons
chmod 777 docker/php/images/sprites/uploads
chmod 777 .
