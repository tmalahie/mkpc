#!/bin/sh
if [ "$1" != '--force' ]; then
	echo 'Warning, this will completely reset your local mkpc database. Continue? [Y/n]'
	read confirm
	if [ "$confirm" != 'Y' ] && [ "$confirm" != "y" ] && [ "$confirm" != "" ]; then
		exit 0
	fi
fi
mysql -h 127.0.0.1 -P 8306 -u root -proot mkpc < docker/mysql/setup.sql
rm -f images/avatars/*
rm -f images/uploads/*
rm -f images/creation_icons/*
rm -f images/sprites/uploads/*
