#!/bin/sh
if [ "$1" != '--force' ]; then
	echo 'Warning, this will completely reset your local mkpc database. Continue? [Y/n]'
	read confirm
	if [ "$confirm" != 'Y' ] && [ "$confirm" != "y" ] && [ "$confirm" != "" ]; then
		exit 0
	fi
fi
mysql -h 127.0.0.1 -P 8306 -u root -proot mkpc < docker/mysql/setup.sql
rm -f docker/php/images/avatars/*
rm -f docker/php/images/uploads/map*
rm -f docker/php/images/uploads/course*
rm -f images/php/creation_icons/*
rm -f images/php/sprites/uploads/*
